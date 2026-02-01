<?php

namespace App\Http\Controllers;

use App\Models\Factuur;
use App\Models\FactuurRegel;
use App\Models\Bestelling;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

    private function makeFactuurnummerFromBestellingId(int $id): string
    {
        return 'INV-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'datum' => ['required', 'date'],

            'naam' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefoon' => ['nullable', 'string', 'max:20'],

            'adres' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:20'],
            'plaats' => ['nullable', 'string', 'max:100'],

            'btw_percentage' => ['required', 'integer', 'min:0', 'max:100'],

            // verzendkosten
            'verzendkosten_incl' => ['nullable', 'numeric', 'min:0'],

            // korting
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

        // levermethode voor de "bestelling" die we aanmaken (simpel afgeleid)
        $levermethode = (!empty($data['adres']) || !empty($data['postcode']) || !empty($data['plaats']))
            ? 'verzenden'
            : 'ophalen';

        // Alles in 1 transactie: bestelling -> factuur -> regels -> totalen
        $factuur = DB::transaction(function () use ($data, $btwPercentage, $verzendkostenIncl, $kortingType, $kortingWaarde, $levermethode) {

            // 1) Maak een bestelling aan zodat we het ID kunnen gebruiken als teller
            $bestelling = Bestelling::create([
                'transactie_id' => null,
                'naam'          => $data['naam'],
                'email'         => $data['email'] ?? null,
                'telefoon'      => $data['telefoon'] ?? '-', // safe als kolom niet nullable is
                'levermethode'  => $levermethode,

                'adres'         => $levermethode === 'verzenden' ? ($data['adres'] ?? null) : null,
                'postcode'      => $levermethode === 'verzenden' ? ($data['postcode'] ?? null) : null,
                'plaats'        => $levermethode === 'verzenden' ? ($data['plaats'] ?? null) : null,

                'betaalmethode' => 'handmatig',
                'totaalprijs'   => 0,          // vullen we straks
                'status'        => 'afgerond',  // handmatig aangemaakte factuur = afgerond
            ]);

            $factuurnummer = $this->makeFactuurnummerFromBestellingId((int) $bestelling->id);

            // 2) Sla factuurnummer ook op op bestelling (als kolom bestaat)
            if (Schema::hasColumn('bestellingen', 'factuurnummer')) {
                $bestelling->factuurnummer = $factuurnummer;
                $bestelling->save();
            }

            // 3) Maak factuur aan en koppel aan bestelling_id
            $factuur = new Factuur();
            $factuur->bestelling_id = $bestelling->id;
            $factuur->factuurnummer = $factuurnummer;

            $factuur->datum = $data['datum'];

            $factuur->naam = $data['naam'];
            $factuur->email = $data['email'] ?? null;
            $factuur->adres = $data['adres'] ?? null;
            $factuur->postcode = $data['postcode'] ?? null;
            $factuur->plaats = $data['plaats'] ?? null;

            $factuur->btw_percentage = $btwPercentage;

            $factuur->verzendkosten_incl = $verzendkostenIncl;

            $factuur->korting_type = $kortingType;
            $factuur->korting_waarde = $kortingWaarde;
            $factuur->korting_bedrag = 0;

            $factuur->save();

            // 4) Regels + producten totaal
            $totaalInclProducten = 0;
            $product_data = []; // voor bestelling_product pivot

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

                // Koppel ook aan bestelling (alleen als product_id bestaat)
                if (!empty($regel['product_id'])) {
                    $pid = (int) $regel['product_id'];
                    $qty = (int) $regel['aantal'];

                    // Als hetzelfde product meerdere regels heeft: optellen
                    if (!isset($product_data[$pid])) {
                        $product_data[$pid] = ['aantal' => 0];
                    }
                    $product_data[$pid]['aantal'] += $qty;
                }
            }

            // Sync producten op bestelling (optioneel maar handig)
            if (!empty($product_data)) {
                $bestelling->producten()->sync($product_data);

                // Als je óók voorraad wilt verlagen bij handmatige facturen,
                // kun je dit hier doen (nu bewust niet automatisch).
            }

            // 5) Totaal incl vóór korting = producten + verzending
            $totaalVoorKorting = round($totaalInclProducten + $verzendkostenIncl, 2);

            // 6) Korting bedrag berekenen (cap op totaal)
            $kortingBedrag = 0.0;
            if ($kortingType === 'percent') {
                $kortingBedrag = round($totaalVoorKorting * ($kortingWaarde / 100), 2);
            } elseif ($kortingType === 'amount') {
                $kortingBedrag = round($kortingWaarde, 2);
            }
            $kortingBedrag = max(0, min($kortingBedrag, $totaalVoorKorting));

            // 7) Eindtotaal incl
            $totaalIncl = round($totaalVoorKorting - $kortingBedrag, 2);

            // 8) BTW over totaal incl na korting
            $subtotaalEx = round($totaalIncl / (1 + $btwPercentage / 100), 2);
            $btwBedrag   = round($totaalIncl - $subtotaalEx, 2);

            // 9) Update factuur totalen
            $factuur->update([
                'subtotaal_ex'       => $subtotaalEx,
                'btw_bedrag'         => $btwBedrag,
                'totaal_incl'        => $totaalIncl,
                'verzendkosten_incl' => $verzendkostenIncl,

                'korting_type'       => $kortingType,
                'korting_waarde'     => $kortingWaarde,
                'korting_bedrag'     => $kortingBedrag,
            ]);

            // 10) Update bestelling totaalprijs (zodat bestellingen + facturen gelijk lopen)
            $bestelling->update([
                'totaalprijs' => $totaalIncl,
            ]);

            return $factuur;
        });

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

            // ✅ Deze twee zijn nu 1-op-1 de DB velden
            'kortingBedrag' => (float) ($factuur->korting_bedrag ?? 0),
            'verzendKosten' => (float) ($factuur->verzendkosten_incl ?? 0),
        ]);

        return $pdf->download('factuur-' . $factuur->factuurnummer . '.pdf');
    }

    public function destroy(Factuur $factuur)
    {
        $factuur->load('regels');

        // verwijder regels + factuur
        $factuur->regels()->delete();

        // als deze factuur een “manual bestelling” heeft, ruim die ook op
        if (!empty($factuur->bestelling_id)) {
            $bestelling = Bestelling::find($factuur->bestelling_id);
            if ($bestelling && empty($bestelling->transactie_id) && ($bestelling->betaalmethode ?? '') === 'handmatig') {
                $bestelling->producten()->detach();
                $bestelling->delete();
            }
        }

        $factuur->delete();

        return redirect()->route('beheer.facturen')->with('success', 'Factuur verwijderd.');
    }
}