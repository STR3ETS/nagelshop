<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Bestelling;
use Illuminate\Support\Str;

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
    
        // Haal klantgegevens en winkelwagen uit de sessie
        $klant = session('checkout.gegevens');
        $winkelwagen = session('cart', []);
    
        // Bereken het totaal van de bestelling
        $totaalprijs = collect($winkelwagen)->sum(fn($item) => $item['prijs'] * $item['aantal']);
    
        // Maak een nieuwe bestelling aan
        $bestelling = Bestelling::create([
            'transactie_id' => strtoupper(Str::random(12)),
            'naam' => $klant['naam'],
            'email' => $klant['email'],
            'adres' => $klant['adres'],
            'postcode' => $klant['postcode'],
            'plaats' => $klant['plaats'],
            'betaalmethode' => $request->betaalmethode,
            'totaalprijs' => $totaalprijs,
        ]);
    
        // Voeg producten toe aan de tussenliggende tabel
        $product_data = [];
        foreach ($winkelwagen as $item) {
            $product_data[$item['id']] = ['aantal' => $item['aantal']];
        }
    
        // Koppel producten aan de bestelling
        $bestelling->producten()->sync($product_data);
    
        // Leeg de winkelwagen en checkoutgegevens
        session()->forget(['cart', 'checkout.gegevens']);
    
        return redirect()->route('bestelling.bedankt', ['id' => $bestelling->id])->with('success', 'Bedankt voor je bestelling!');
    }      

    public function bedankt($id)
    {
        // Haal de bestelling op via het ID
        $bestelling = Bestelling::findOrFail($id);
    
        // Haal klantgegevens en winkelwagen uit de sessie
        $gegevens = session('checkout.gegevens');
        $cart = session('cart', []);
    
        // Als klantgegevens niet bestaan in de sessie, geef een error of redirect
        if (!$gegevens) {
            return redirect()->route('winkelwagen.contact')->with('error', 'Klantgegevens niet gevonden.');
        }
    
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
