<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Bestelling;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

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
            'status' => ['required', Rule::in(['open', 'onderweg', 'opgehaald', 'afgerond'])],
        ]);

        $bestelling->update(['status' => $validated['status']]);

        return back()->with('success', 'Status succesvol bijgewerkt');
    }

    public function updateTrackTrace(Request $request, Bestelling $bestelling)
    {
        $request->validate([
            'track_trace' => 'nullable|string|max:255',
        ]);

        $update = ['track_trace' => $request->track_trace];

        if (($bestelling->status ?? 'open') === 'open') {
            $update['status'] = 'onderweg';
        }

        $bestelling->update($update);

        return back()->with('success', 'Track & Trace code succesvol toegevoegd');
    }

    private function makeFactuurnummerFromBestellingId(int $id): string
    {
        return 'INV-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    public function downloadFactuur(Bestelling $bestelling)
    {
        if (!Schema::hasColumn('bestellingen', 'factuurnummer')) {
            abort(500, "Kolom 'factuurnummer' ontbreekt in tabel 'bestellingen'. Draai eerst je migratie.");
        }

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

        $bestelling->loadMissing('producten');

        // ✅ Factuurnummer = gebaseerd op bestelling.id (altijd gelijk systeem)
        $bestelling = DB::transaction(function () use ($bestelling) {
            $locked = Bestelling::whereKey($bestelling->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (empty($locked->factuurnummer)) {
                $locked->factuurnummer = $this->makeFactuurnummerFromBestellingId((int) $locked->id);

                if (Schema::hasColumn('bestellingen', 'factuur_datum')) {
                    $locked->factuur_datum = now();
                }

                $locked->save();
            }

            return $locked->loadMissing('producten');
        });

        $totaalIncl  = (float) $bestelling->totaalprijs;
        $subtotaalEx = round($totaalIncl / (1 + $btwPercentage / 100), 2);
        $btwBedrag   = round($totaalIncl - $subtotaalEx, 2);

        $factuurnummer = $bestelling->factuurnummer;

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