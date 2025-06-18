<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Bestelling;
use Illuminate\Support\Str;
use Mollie\Laravel\Facades\Mollie;

class WinkelwagenController extends Controller
{
    public function toevoegen(Request $request, Product $product)
    {
        // Haal de winkelwagen op uit de sessie, of maak een lege winkelwagen als deze niet bestaat
        $cart = session()->get('cart', []);
        
        // Controleer of het product al in de winkelwagen zit
        if (isset($cart[$product->id])) {
            // Als het product al in de winkelwagen zit, verhoog de hoeveelheid met 1
            $cart[$product->id]['aantal']++;
        } else {
            // Als het product nog niet in de winkelwagen zit, voeg het toe met een hoeveelheid van 1
            $cart[$product->id] = [
                'naam' => $product->naam,
                'prijs' => $product->prijs,
                'aantal' => 1,
                'foto' => $product->foto,
            ];
        }
        
        // Sla de bijgewerkte winkelwagen op in de sessie
        session()->put('cart', $cart);
        
        // Geef een terugmelding dat het product is toegevoegd aan de winkelwagen
        return back()->with('toegevoegd', $product->naam);
    }     

    public function index()
    {
        $cart = session()->get('cart', []);
        return view('winkelwagen.index', compact('cart'));
    }

    public function verwijderen($id)
    {
        $cart = session()->get('cart', []);
        unset($cart[$id]);
        session()->put('cart', $cart);

        return back()->with('success', 'Product verwijderd uit winkelwagen.');
    }

    public function updateAantal(Request $request, $id)
    {
        $request->validate([
            'aantal' => 'required|integer|min:1'
        ]);
    
        $cart = session()->get('cart', []);
        if (isset($cart[$id])) {
            $cart[$id]['aantal'] = $request->aantal;
            session()->put('cart', $cart);
        }
    
        return redirect()->back();
    }

    public function toonContactForm()
    {
        $cart = session()->get('cart', []);
        return view('winkelwagen.contact', compact('cart'));
    }
    
    public function contactOpslaan(Request $request)
    {
        $data = $request->validate([
            'naam' => 'required|string|max:255',
            'email' => 'required|email',
            'adres' => 'required|string',
            'postcode' => 'required|string',
            'plaats' => 'required|string',
        ]);

        session(['checkout.gegevens' => $data]);

        return redirect()->route('winkelwagen.betaling');
    }

    public function toonBetaling()
    {
        $cart = session()->get('cart', []);
        $gegevens = session('checkout.gegevens');

        return view('winkelwagen.betaling', compact('cart', 'gegevens'));
    }

    public function afronden(Request $request)
    {
        $request->validate([
            'betaalmethode' => 'required|string|in:ideal,paypal,creditcard',
        ]);

        $klant = session('checkout.gegevens');
        $winkelwagen = session('cart', []);
        $productenTotaal = collect($winkelwagen)->sum(fn($item) => $item['prijs'] * $item['aantal']);
        $verzendkosten = $productenTotaal >= 75 ? 0 : 4.90;
        $totaalprijs = $productenTotaal;

        // Unieke ID voor bestelling
        $bestellingUuid = (string) Str::uuid();

        // Sla volledige info tijdelijk in sessie
        session()->put("temp_bestellingen.$bestellingUuid", [
            'klant' => $klant,
            'cart' => $winkelwagen,
            'verzendkosten' => $verzendkosten,
            'totaalprijs' => $totaalprijs,
            'betaalmethode' => $request->betaalmethode,
        ]);

        // Mollie betaling met alleen UUID in metadata
        $payment = Mollie::api()->payments->create([
            'amount' => [
                'currency' => 'EUR',
                // 'value' => number_format($totaalprijs, 2, '.', ''),
                'value' => '0.01',
            ],
            'description' => 'Bestelling bij Deluxe Nailshop',
            'redirectUrl' => route('mollie.callback', ['bestelling' => $bestellingUuid]),
            'metadata' => [
                'bestelling_id' => $bestellingUuid,
            ],
        ]);

        // Bewaar Mollie payment ID + UUID in sessie (optioneel)
        session()->put('mollie_payment_id', $payment->id);

        return redirect($payment->getCheckoutUrl());
    }

    public function mollieCallback()
    {
        $paymentId = session('mollie_payment_id');
        $payment = Mollie::api()->payments->get($paymentId);

        if (!$payment->isPaid()) {
            return redirect()->route('winkelwagen.index')->with('error', 'Betaling is niet gelukt.');
        }

        $bestellingId = request('bestelling');
        $data = session("temp_bestellingen.$bestellingId");
        if (!$data) {
            return redirect()->route('winkelwagen.index')->with('error', 'Geen betalingsgegevens gevonden.');
        }

        $bestaan = Bestelling::where('transactie_id', $payment->id)->first();
        if ($bestaan) {
            return redirect()->route('winkelwagen.bedankt', ['id' => $bestaan->id]);
        }

        // Bestelling opslaan
        $bestelling = Bestelling::create([
            'transactie_id' => $payment->id,
            'naam' => $data['klant']['naam'],
            'email' => $data['klant']['email'],
            'adres' => $data['klant']['adres'],
            'postcode' => $data['klant']['postcode'],
            'plaats' => $data['klant']['plaats'],
            'betaalmethode' => 'ideal',
            'totaalprijs' => $data['totaalprijs'],
        ]);

        // Koppel producten aan bestelling én verminder voorraad
        $product_data = [];
        foreach ($data['cart'] as $id => $item) {
            $product_data[$id] = ['aantal' => $item['aantal']];

            // 📉 Verminder voorraad van het product
            $product = Product::find($id);
            if ($product) {
                $product->voorraad = max(0, $product->voorraad - $item['aantal']);
                $product->save();
            }
        }

        $bestelling->producten()->sync($product_data);

        // 🔚 Ruim alles op
        session()->forget([
            'cart',
            'checkout.gegevens',
            'mollie_payment_id',
            "temp_bestellingen.$bestellingId"
        ]);

        return redirect()->route('winkelwagen.bedankt', ['id' => $bestelling->id]);
    }

    public function mollieWebhook(Request $request)
    {
        $payment = Mollie::api()->payments->get($request->id);
        $bestellingId = $payment->metadata->bestelling_id;

        $bestelling = Bestelling::find($bestellingId);
        if (!$bestelling) {
            return response()->json(['error' => 'Bestelling niet gevonden'], 404);
        }

        if ($payment->isPaid() && !$payment->hasRefunds()) {
            $bestelling->status = 'betaald';
        } elseif ($payment->isFailed() || $payment->isExpired()) {
            $bestelling->status = 'mislukt';
        }

        $bestelling->save();

        return response()->json(['status' => 'ok']);
    }

    public function bedankt($id)
    {
        // Haal de bestelling op via het ID
        $bestelling = Bestelling::findOrFail($id);
    
        // Haal klantgegevens en winkelwagen uit de sessie
        $gegevens = [
            'naam' => $bestelling->naam,
            'email' => $bestelling->email,
            'adres' => $bestelling->adres,
            'postcode' => $bestelling->postcode,
            'plaats' => $bestelling->plaats,
        ];
    
        // Haal de producten van de bestelling uit de tussenliggende tabel
        $bestellingProducten = $bestelling->producten; // Zorg ervoor dat de relatie is gedefinieerd in de Bestelling model
    
        // Bereken het totaal met verzendkosten
        $gratisVerzendingDrempel = 75;
        $totaalIncl = $bestellingProducten->sum(fn($item) => $item->prijs * $item->pivot->aantal);
        $btw = $totaalIncl * 0.21;
        $verzendkosten = $totaalIncl >= $gratisVerzendingDrempel ? 0 : 4.90;
        $totaalMetVerzending = $totaalIncl + $verzendkosten;
    
        return view('winkelwagen.bedankt', compact('bestelling', 'gegevens', 'bestellingProducten', 'totaalIncl', 'btw', 'verzendkosten', 'totaalMetVerzending'));
    } 
}
