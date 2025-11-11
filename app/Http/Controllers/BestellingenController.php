<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bestelling;
use Barryvdh\DomPDF\Facade\Pdf;

class BestellingenController extends Controller
{
    public function index()
    {
        $bestellingen = Bestelling::orderByRaw("
            FIELD(status, 'open', 'onderweg', 'afgeleverd')
        ")->orderBy('created_at', 'desc')->get();

        return view('beheer.bestellingen.index', compact('bestellingen'));
    }

    public function inzien(Bestelling $bestelling)
    {
        $bestelling->load('producten');

        return view('beheer.bestellingen.inzien', compact('bestelling'));
    }

    public function updateVerzendgegevens(Request $request, Bestelling $bestelling)
    {
        $validated = $request->validate([
            'naam' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'adres' => 'required|string|max:255',
            'postcode' => 'required|string|max:20',
            'plaats' => 'required|string|max:100',
        ]);

        $bestelling->update($validated);

        return redirect()->back()->with('success', 'Verzendgegevens succesvol opgeslagen');
    }
    
    public function updateTrackTrace(Request $request, Bestelling $bestelling)
    {
        $request->validate([
            'track_trace' => 'nullable|string|max:255',
        ]);

        $bestelling->update([
            'track_trace' => $request->track_trace,
            'status' => 'onderweg',
        ]);

        return back()->with('success', 'Track & Trace code succesvol toegevoegd');
    }

    public function downloadFactuur(Bestelling $bestelling)
    {
        // Bedrijfsgegevens – pas aan naar je eigen data / Instellingen-model
        $bedrijf = [
            'naam'     => 'Deluxe Nail Shop',
            'adres'    => 'Voorbeeldstraat 1',
            'postcode' => '1234 AB',
            'plaats'   => 'Arnhem',
            'kvk'      => '12345678',
            'btw'      => 'NL001234567B01',
            'iban'     => 'NL00BANK0123456789',
            'email'    => 'info@deluxenailshop.nl',
        ];

        $btwPercentage = 21;

        // Uitgaande van totaalprijs incl. btw
        $totaalIncl  = $bestelling->totaalprijs;
        $subtotaalEx = round($totaalIncl / (1 + $btwPercentage / 100), 2);
        $btwBedrag   = round($totaalIncl - $subtotaalEx, 2);

        $factuurnummer = $bestelling->factuurnummer
            ?? 'INV-' . str_pad($bestelling->id, 6, '0', STR_PAD_LEFT);

        $pdf = Pdf::loadView('beheer.bestellingen.factuur', [
            'bestelling'    => $bestelling,
            'bedrijf'       => $bedrijf,
            'btwPercentage' => $btwPercentage,
            'subtotaalEx'   => $subtotaalEx,
            'btwBedrag'     => $btwBedrag,
            'totaalIncl'    => $totaalIncl,
            'factuurnummer' => $factuurnummer,
        ]);

        return $pdf->download('factuur-' . $factuurnummer . '.pdf');
    }
}
