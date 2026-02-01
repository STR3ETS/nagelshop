<?php

namespace App\Http\Controllers;

use App\Models\Factuur;
use App\Models\FactuurRegel;
use App\Services\InvoiceNumberService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;

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

            // verzendkosten
            'verzendkosten_incl' => ['nullable', 'numeric', 'min:0'],

            // ✅ korting
            'korting_type' => ['nullable', 'string', Rule::in(['none', 'percent', 'amount'])],
            'korting_waarde' => ['nullable', 'numeric', 'min:0'],

            'regels' => ['required', 'array', 'min:1'],
            'regels.*.product_id' => ['nullable', 'integer'],
            'regels.*.artikel' => ['required', 'string', 'max:255'],
            'regels.*.aantal' => ['required', 'integer', 'min:1'],
            'regels.*.prijs_incl' => ['required', 'numeric', 'min:0'],
        ]);

        $btwPercentage = (int) $data['btw_percentage'];
        $verzendkostenIncl = round((float) ($data['verzendkosten_incl'] ?? 0), 2);

        $kortingType = (string) ($data['korting_type'] ?? 'none');
        $kortingWaarde = round((float) ($data['korting_waarde'] ?? 0), 2);

        // percent extra begrenzen
        if ($kortingType === 'percent') {
            $kortingWaarde = max(0, min(100, $kortingWaarde));
        } elseif ($kortingType === 'amount') {
            $kortingWaarde = max(0, $kortingWaarde);
        } else {
            $kortingType = 'none';
            $kortingWaarde = 0;
        }

        $factuur = new Factuur();
        $factuur->factuurnummer = $numbers->next('INV', 6);
        $factuur->datum = $data['datum'];

        $factuur->naam = $data['naam'];
        $factuur->email = $data['email'] ?? null;
        $factuur->adres = $data['adres'] ?? null;
        $factuur->postcode = $data['postcode'] ?? null;
        $factuur->plaats = $data['plaats'] ?? null;
        $factuur->btw_percentage = $btwPercentage;

        $factuur->verzendkosten_incl = $verzendkostenIncl;

        // korting velden alvast opslaan
        $factuur->korting_type = $kortingType;
        $factuur->korting_waarde = $kortingWaarde;
        $factuur->korting_bedrag = 0;

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

        // Totaal incl vóór korting = producten + verzending
        $totaalVoorKorting = round($totaalInclProducten + $verzendkostenIncl, 2);

        // Korting bedrag berekenen (cap op totaal)
        $kortingBedrag = 0.0;
        if ($kortingType === 'percent') {
            $kortingBedrag = round($totaalVoorKorting * ($kortingWaarde / 100), 2);
        } elseif ($kortingType === 'amount') {
            $kortingBedrag = round($kortingWaarde, 2);
        }
        $kortingBedrag = max(0, min($kortingBedrag, $totaalVoorKorting));

        // Eindtotaal incl
        $totaalIncl = round($totaalVoorKorting - $kortingBedrag, 2);

        // BTW berekenen over totaal incl na korting
        $subtotaalEx = round($totaalIncl / (1 + $btwPercentage / 100), 2);
        $btwBedrag   = round($totaalIncl - $subtotaalEx, 2);

        $factuur->update([
            'subtotaal_ex'       => $subtotaalEx,
            'btw_bedrag'         => $btwBedrag,
            'totaal_incl'        => $totaalIncl,
            'verzendkosten_incl' => $verzendkostenIncl,

            'korting_type'       => $kortingType,
            'korting_waarde'     => $kortingWaarde,
            'korting_bedrag'     => $kortingBedrag,
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
            'kortingBedrag' => $factuur->korting_bedrag,
            'verzendKosten' => $factuur->verzendkosten_incl,
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
