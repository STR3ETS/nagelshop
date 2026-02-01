<?php

namespace App\Http\Controllers;

use App\Models\Bestelling;
use App\Models\Factuur;
use App\Models\FactuurRegel;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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

    private function bestellingIdFromFactuurnummer(?string $factuurnummer): ?int
    {
        if (!$factuurnummer) return null;

        // verwacht: INV-000009
        if (preg_match('/^INV-(\d{6})$/', $factuurnummer, $m)) {
            return (int) ltrim($m[1], '0'); // "000009" -> 9
        }

        return null;
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

            // verzendkosten (incl)
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

        if ($kortingType === 'percent') {
            $kortingWaarde = max(0, min(100, $kortingWaarde));
        } elseif ($kortingType === 'amount') {
            $kortingWaarde = max(0, $kortingWaarde);
        } else {
            $kortingType = 'none';
            $kortingWaarde = 0;
        }

        // “levermethode” afleiden: als adres ingevuld => verzenden, anders ophalen
        $levermethode = (!empty($data['adres']) || !empty($data['postcode']) || !empty($data['plaats']))
            ? 'verzenden'
            : 'ophalen';

        $factuur = DB::transaction(function () use ($data, $btwPercentage, $verzendkostenIncl, $kortingType, $kortingWaarde, $levermethode) {

            // 1) Bestelling aanmaken (ID is onze teller)
            $bestelling = new Bestelling();
            $bestelling->transactie_id = 'HANDMATIG-' . (string) Str::uuid(); // ✅ NOT NULL fix
            $bestelling->naam = $data['naam'];
            $bestelling->email = $data['email'] ?? null;

            if (Schema::hasColumn('bestellingen', 'telefoon')) {
                $bestelling->telefoon = $data['telefoon'] ?? '-';
            }

            $bestelling->levermethode = $levermethode;
            $bestelling->adres = $levermethode === 'verzenden' ? ($data['adres'] ?? null) : null;
            $bestelling->postcode = $levermethode === 'verzenden' ? ($data['postcode'] ?? null) : null;
            $bestelling->plaats = $levermethode === 'verzenden' ? ($data['plaats'] ?? null) : null;

            $bestelling->betaalmethode = 'handmatig';
            $bestelling->totaalprijs = 0;
            $bestelling->status = 'afgerond';
            $bestelling->save();

            // 2) Factuurnummer = op basis van bestellingen.id
            $factuurnummer = $this->makeFactuurnummerFromBestellingId((int) $bestelling->id);

            if (Schema::hasColumn('bestellingen', 'factuurnummer')) {
                $bestelling->factuurnummer = $factuurnummer;
                $bestelling->save();
            }

            // 3) Factuur record maken (GEEN bestelling_id kolom nodig)
            $factuur = new Factuur();
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

            // 4) Regels opslaan
            $totaalInclProducten = 0.0;

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

            // 5) Totalen
            $totaalVoorKorting = round($totaalInclProducten + $verzendkostenIncl, 2);

            $kortingBedrag = 0.0;
            if ($kortingType === 'percent') {
                $kortingBedrag = round($totaalVoorKorting * ($kortingWaarde / 100), 2);
            } elseif ($kortingType === 'amount') {
                $kortingBedrag = round($kortingWaarde, 2);
            }
            $kortingBedrag = max(0, min($kortingBedrag, $totaalVoorKorting));

            $totaalIncl = round($totaalVoorKorting - $kortingBedrag, 2);

            $subtotaalEx = ($btwPercentage > 0)
                ? round($totaalIncl / (1 + $btwPercentage / 100), 2)
                : round($totaalIncl, 2);

            $btwBedrag = round($totaalIncl - $subtotaalEx, 2);

            $factuur->update([
                'subtotaal_ex' => $subtotaalEx,
                'btw_bedrag' => $btwBedrag,
                'totaal_incl' => $totaalIncl,
                'verzendkosten_incl' => $verzendkostenIncl,
                'korting_type' => $kortingType,
                'korting_waarde' => $kortingWaarde,
                'korting_bedrag' => $kortingBedrag,
            ]);

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

            // ✅ hiermee pakt je PDF altijd de juiste velden
            'kortingBedrag' => (float) ($factuur->korting_bedrag ?? 0),
            'verzendKosten' => (float) ($factuur->verzendkosten_incl ?? 0),
        ]);

        return $pdf->download('factuur-' . $factuur->factuurnummer . '.pdf');
    }

    public function destroy(Factuur $factuur)
    {
        $factuurnummer = $factuur->factuurnummer;

        $factuur->load('regels');
        $factuur->regels()->delete();
        $factuur->delete();

        // ✅ handmatige bestelling ook opruimen op basis van factuurnummer -> id
        $bestellingId = $this->bestellingIdFromFactuurnummer($factuurnummer);

        if ($bestellingId) {
            $b = Bestelling::find($bestellingId);

            if (
                $b
                && (($b->betaalmethode ?? '') === 'handmatig')
                && str_starts_with((string) ($b->transactie_id ?? ''), 'HANDMATIG-')
            ) {
                if (method_exists($b, 'producten')) {
                    $b->producten()->detach();
                }
                $b->delete();
            }
        }

        return redirect()->route('beheer.facturen')->with('success', 'Factuur verwijderd.');
    }
}
