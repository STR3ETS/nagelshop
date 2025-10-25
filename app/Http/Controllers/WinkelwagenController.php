<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Bestelling;
use Illuminate\Support\Str;
use Mollie\Laravel\Facades\Mollie;
use App\Models\Kortingscode;
use Illuminate\Validation\Rule;

class WinkelwagenController extends Controller
{
    public function toevoegen(Request $request, Product $product)
    {
        $cart = session()->get('cart', []);
        
        if (isset($cart[$product->id])) {
            $cart[$product->id]['aantal']++;
        } else {
            $cart[$product->id] = [
                'naam'  => $product->naam,
                'prijs' => $product->prijs,
                'aantal'=> 1,
                'foto'  => $product->foto,
            ];
        }
        
        session()->put('cart', $cart);
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
        // Basisvalidatie (NL-telefoon tolerant)
        $data = $request->validate([
            'naam'     => ['required','string','max:120'],
            'email'    => ['required','email'],
            'telefoon' => ['required','string','min:6','max:20'],
            'adres'    => ['required','string','max:200'],
            'postcode' => ['required','string','max:20'],
            'plaats'   => ['required','string','max:120'],
        ]);

        session(['checkout.gegevens' => $data]);

        return redirect()->route('winkelwagen.betaling');
    }

    public function kortingscodeToepassen(Request $request)
    {
        $data = $request->validate([
            'code' => ['required','string','max:50','regex:/^[A-Za-z0-9\-_]+$/'],
        ], [
            'code.regex' => 'Gebruik alleen letters, cijfers, streepjes (-) of underscores (_).',
        ]);

        $code = mb_strtoupper(trim($data['code']));
        $k = Kortingscode::where('code', $code)->first();

        if (!$k) {
            return back()->withErrors(['code' => 'Ongeldige kortingscode.'])->withInput();
        }
        if ($k->isExpired()) {
            return back()->withErrors(['code' => 'Deze kortingscode is verlopen.'])->withInput();
        }

        session(['checkout.kortingscode' => [
            'code'  => $k->code,
            'type'  => $k->type,   // 'percent' | 'amount'
            'value' => (float) $k->value,
        ]]);

        return back()->with('success', 'Kortingscode toegepast.');
    }

    public function kortingscodeVerwijderen()
    {
        session()->forget('checkout.kortingscode');
        return back()->with('success', 'Kortingscode verwijderd.');
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

        // Gegevens uit sessie
        $klant       = session('checkout.gegevens');
        $winkelwagen = session('cart', []);
        if (!$klant || empty($winkelwagen)) {
            return redirect()->route('winkelwagen.index')->with('error', 'Je sessie is verlopen of je winkelwagen is leeg.');
        }

        // 1) Totaal van producten
        $productenTotaal = collect($winkelwagen)->sum(fn($item) => $item['prijs'] * $item['aantal']);

        // 2) Kortingscode toepassen (indien aanwezig)
        $sessieCode  = session('checkout.kortingscode'); // ['code','type','value']
        $kortingBedrag = 0.0;
        $actieveKortingscode = null;

        if ($sessieCode && !empty($sessieCode['code'])) {
            // Hercontroleer de code (bestaat & niet verlopen)
            $k = \App\Models\Kortingscode::where('code', mb_strtoupper($sessieCode['code']))->first();
            if ($k && !$k->isExpired()) {
                $actieveKortingscode = [
                    'code'  => $k->code,
                    'type'  => $k->type,            // 'percent' | 'amount'
                    'value' => (float) $k->value,
                ];
                $kortingBedrag = $k->type === 'percent'
                    ? ($k->value / 100) * $productenTotaal
                    : (float) $k->value;

                // Korting nooit groter dan producttotaal
                $kortingBedrag = min($kortingBedrag, $productenTotaal);
            } else {
                // Ongeldig/Verlopen -> verwijder uit sessie
                session()->forget('checkout.kortingscode');
            }
        }

        $totaalNaKorting = max(0, $productenTotaal - $kortingBedrag);

        // 3) Verzendkosten bepalen
        $gratisVerzendingDrempel = 75.00;
        $verzendkosten = 0.00;
        $adresOk = !empty($klant['adres'] ?? null) && !empty($klant['postcode'] ?? null) && !empty($klant['plaats'] ?? null);

        if ($adresOk) {
            $pc = strtoupper(trim($klant['postcode']));
            // NL: 1234 AB  (4 cijfers + 2 letters, spatie optioneel)
            $isNL = (bool) preg_match('/^\d{4}\s?[A-Z]{2}$/', $pc);
            // BE: 1234 (exact 4 cijfers)
            $isBE = !$isNL && (bool) preg_match('/^\d{4}$/', $pc);

            $tarief = $isBE ? 9.50 : 5.95; // default naar NL-tarief als onherkenbaar
            $verzendkosten = $totaalNaKorting >= $gratisVerzendingDrempel ? 0.00 : $tarief;
        } else {
            // Als geen adres is ingevuld, rekenen we nog geen verzendkosten mee
            $verzendkosten = 0.00;
        }

        // 4) Eindtotaal
        $totaalprijs = round($totaalNaKorting + $verzendkosten, 2);

        // 5) Unieke ID voor de bestelling (nog niet in DB, we doen dat pas in callback)
        $bestellingUuid = (string) \Illuminate\Support\Str::uuid();

        // 6) Sla alles tijdelijk in de sessie op (wordt in callback definitief opgeslagen)
        session()->put("temp_bestellingen.$bestellingUuid", [
            'klant'          => $klant,
            'cart'           => $winkelwagen,
            'kortingscode'   => $actieveKortingscode, // kan null zijn
            'korting_bedrag' => $kortingBedrag,
            'verzendkosten'  => $verzendkosten,
            'totaalprijs'    => $totaalprijs,
            'betaalmethode'  => $request->betaalmethode,
        ]);

        // 7) Mollie betaling aanmaken
        $payment = \Mollie\Laravel\Facades\Mollie::api()->payments->create([
            'amount' => [
                'currency' => 'EUR',
                'value'    => number_format($totaalprijs, 2, '.', ''), // ← zet hier '0.01' om te testen
                // 'value' => '0.01',
            ],
            'description' => 'Bestelling bij Deluxe Nailshop',
            'redirectUrl' => route('mollie.callback', ['bestelling' => $bestellingUuid]),
            'metadata'    => [
                'bestelling_id' => $bestellingUuid,
            ],
            // Optioneel: webhookUrl als je server-side statusupdates wilt
            // 'webhookUrl' => route('mollie.webhook'),
        ]);

        // 8) Bewaar Mollie payment ID in sessie voor callback
        session()->put('mollie_payment_id', $payment->id);

        // 9) Naar Mollie checkout
        return redirect($payment->getCheckoutUrl());
    }

    public function mollieCallback()
    {
        $paymentId = session('mollie_payment_id');
        $payment   = Mollie::api()->payments->get($paymentId);

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

        // Bestelling opslaan (→ inclusief telefoon + juiste betaalmethode)
        $bestelling = Bestelling::create([
            'transactie_id' => $payment->id,
            'naam'          => $data['klant']['naam'],
            'email'         => $data['klant']['email'],
            'telefoon'      => $data['klant']['telefoon'], // ← nieuw
            'adres'         => $data['klant']['adres'],
            'postcode'      => $data['klant']['postcode'],
            'plaats'        => $data['klant']['plaats'],
            'betaalmethode' => $data['betaalmethode'],      // ← uit sessie i.p.v. hardcoded
            'totaalprijs'   => $data['totaalprijs'],
        ]);

        // Koppel producten en verlaag voorraad
        $product_data = [];
        foreach ($data['cart'] as $id => $item) {
            $product_data[$id] = ['aantal' => $item['aantal']];

            $product = Product::find($id);
            if ($product) {
                $product->voorraad = max(0, $product->voorraad - $item['aantal']);
                $product->save();
            }
        }

        $bestelling->producten()->sync($product_data);

        // Ruim sessie op
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
        $bestelling = Bestelling::findOrFail($id);
    
        $gegevens = [
            'naam'     => $bestelling->naam,
            'email'    => $bestelling->email,
            'telefoon' => $bestelling->telefoon, // ← tonen op bedanktpagina
            'adres'    => $bestelling->adres,
            'postcode' => $bestelling->postcode,
            'plaats'   => $bestelling->plaats,
        ];
    
        $bestellingProducten = $bestelling->producten; 
    
        $gratisVerzendingDrempel = 75;
        $totaalIncl = $bestellingProducten->sum(fn($item) => $item->prijs * $item->pivot->aantal);
        $btw = $totaalIncl * 0.21;
        $verzendkosten = $totaalIncl >= $gratisVerzendingDrempel ? 0 : 4.90;
        $totaalMetVerzending = $totaalIncl + $verzendkosten;
    
        return view('winkelwagen.bedankt', compact(
            'bestelling', 'gegevens', 'bestellingProducten',
            'totaalIncl', 'btw', 'verzendkosten', 'totaalMetVerzending'
        ));
    } 
}