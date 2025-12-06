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
            FIELD(status, 'open', 'onderweg', 'opgehaald', 'afgerond')
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
        $rules = [
            'naam'  => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ];

        $levermethode = $bestelling->levermethode ?? 'verzenden';

        if ($levermethode === 'verzenden') {
            $rules['adres']    = 'required|string|max:255';
            $rules['postcode'] = 'required|string|max:20';
            $rules['plaats']   = 'required|string|max:100';
        }

        $validated = $request->validate($rules);

        // Als ophalen: adresvelden leegmaken in DB (netjes)
        if ($levermethode === 'ophalen') {
            $validated['adres'] = null;
            $validated['postcode'] = null;
            $validated['plaats'] = null;
        }

        $bestelling->update($validated);

        return redirect()->back()->with('success', 'Verzendgegevens succesvol opgeslagen');
    }

    public function updateStatus(Request $request, Bestelling $bestelling)
    {
        $validated = $request->validate([
            'status' => ['required', \Illuminate\Validation\Rule::in([
                'open', 'onderweg', 'opgehaald', 'afgerond'
            ])],
        ]);

        $bestelling->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Status succesvol bijgewerkt');
    }
    
    public function updateTrackTrace(Request $request, Bestelling $bestelling)
    {
        $request->validate([
            'track_trace' => 'nullable|string|max:255',
        ]);

        $update = ['track_trace' => $request->track_trace];

        // Alleen auto-naar onderweg als hij nog 'nieuw' is
        if (($bestelling->status ?? 'open') === 'open') {
            $update['status'] = 'onderweg';
        }

        $bestelling->update($update);

        return back()->with('success', 'Track & Trace code succesvol toegevoegd');
    }

    public function downloadFactuur(Bestelling $bestelling)
    {
        // Bedrijfsgegevens – pas aan naar je eigen data / Instellingen-model
        $bedrijf = [
            'naam'     => 'Deluxe Nail Shop',
            'adres'    => 'Lentemorgen 5 (Kamer 5.36)',
            'postcode' => '6903 CT',
            'plaats'   => 'Zevenaar',
            'kvk'      => '84373466',
            'btw'      => 'NL003954592B82',
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
