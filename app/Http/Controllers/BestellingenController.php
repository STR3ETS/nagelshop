<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bestelling;

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
}
