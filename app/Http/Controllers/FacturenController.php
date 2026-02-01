<?php

namespace App\Http\Controllers;

use App\Models\Factuur;
use App\Models\FactuurRegel;
use App\Services\InvoiceNumberService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class FacturenController extends Controller
{
    public function index()
    {
        $facturen = Factuur::orderBy('id', 'desc')->get();
        return view('beheer.facturen.index', compact('facturen'));
    }

    public function create()
    {
        $producten = \App\Models\Product::select('id', 'naam', 'prijs')->orderBy('naam')->get();
        return view('beheer.facturen.create', compact('producten'));
    }

    public function store(Request $request, InvoiceNumberService $numbers)
    {
        $data = $request->validate([
            'datum' => ['required', 'date'],
            'naam' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'adres' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:20'],
            'plaats' => ['nullable', 'string', 'max:100'],
            'btw_percentage' => ['required', 'integer', 'min:0', 'max:100'],

            // ✅ NIEUW
            'verzendkosten_incl' => ['nullable', 'numeric', 'min:0'],

            'regels' => ['required', 'array', 'min:1'],
            'regels.*.product_id' => ['nullable', 'integer'],
            'regels.*.artikel' => ['required', 'string', 'max:255'],
            'regels.*.aantal' => ['required', 'integer', 'min:1'],
            'regels.*.prijs_incl' => ['required', 'numeric', 'min:0'],
        ]);

        $btwPercentage = (int) $data['btw_percentage'];
        $verzendkostenIncl = round((float) ($data['verzendkosten_incl'] ?? 0), 2);

        $factuur = new Factuur();
        $factuur->factuurnummer = $numbers->next('INV', 6);
        $factuur->datum = $data['datum'];

        $factuur->naam = $data['naam'];
        $factuur->email = $data['email'] ?? null;
        $factuur->adres = $data['adres'] ?? null;
        $factuur->postcode = $data['postcode'] ?? null;
        $factuur->plaats = $data['plaats'] ?? null;
        $factuur->btw_percentage = $btwPercentage;

        // ✅ NIEUW
        $factuur->verzendkosten_incl = $verzendkostenIncl;

        $factuur->save();

        $totaalInclProducten = 0;

        foreach ($data['regels'] as $regel) {
            $regelTotaal = round(((float) $regel['prijs_incl']) * (int) $regel['aantal'], 2);
            $totaalInclProducten += $regelTotaal;

            FactuurRegel::create([
                'factuur_id' => $factuur->id,
                'product_id' => $regel['product_id'] ?? null,
                'artikel' => $regel['artikel'],
                'aantal' => (int) $regel['aantal'],
                'prijs_incl' => round((float) $regel['prijs_incl'], 2),
                'totaal_incl' => $regelTotaal,
            ]);
        }

        // Eindtotaal incl = producten + verzending
        $totaalIncl = round($totaalInclProducten + $verzendkostenIncl, 2);

        // BTW berekenen over totaal incl
        $subtotaalEx = round($totaalIncl / (1 + $btwPercentage / 100), 2);
        $btwBedrag   = round($totaalIncl - $subtotaalEx, 2);

        $factuur->update([
            'subtotaal_ex'       => $subtotaalEx,
            'btw_bedrag'         => $btwBedrag,
            'totaal_incl'        => $totaalIncl,
            'verzendkosten_incl' => $verzendkostenIncl,
        ]);

        return redirect()->route('facturen.factuur.download', $factuur);
    }

    public function pdf(Factuur $factuur)
    {
        $factuur->load('regels');

        $bedrijf = [
            'naam'     => 'Deluxe Nail Shop',
            'adres'    => 'Lentemorgen 5 (Kamer 5.36)',
            'postcode' => '6903 CT',
            'plaats'   => 'Zevenaar',
            'kvk'      => '84373466',
            'btw'      => 'NL003954592B82',
            'email'    => 'info@deluxenailshop.nl',
        ];

        $pdf = Pdf::loadView('beheer.facturen.factuur', [
            'factuur'       => $factuur,
            'bedrijf'       => $bedrijf,
            'btwPercentage' => $factuur->btw_percentage,
            'subtotaalEx'   => $factuur->subtotaal_ex,
            'btwBedrag'     => $factuur->btw_bedrag,
            'totaalIncl'    => $factuur->totaal_incl,
            'factuurnummer' => $factuur->factuurnummer,
        ]);

        return $pdf->download('factuur-' . $factuur->factuurnummer . '.pdf');
    }

    public function destroy(Factuur $factuur)
    {
        $factuur->load('regels');
        $factuur->regels()->delete();
        $factuur->delete();

        return redirect()->route('beheer.facturen')->with('success', 'Factuur verwijderd.');
    }
}