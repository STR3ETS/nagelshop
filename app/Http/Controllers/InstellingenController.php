<?php

namespace App\Http\Controllers;

use App\Models\Instelling;
use App\Models\Kortingscode;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class InstellingenController extends Controller
{
    public function index()
    {
        $instellingen  = Instelling::first() ?? new Instelling();
        $kortingscodes = Kortingscode::orderBy('vervalt_op')->get();

        return view('beheer.instellingen.index', compact('instellingen', 'kortingscodes'));
    }

    public function opslaanAlgemeen(Request $request)
    {
        $data = $request->validate([
            'email'          => 'nullable|email',
            'telefoon'       => 'nullable|string',
            'btw_nummer'     => 'nullable|string',
            'kvk_nummer'     => 'nullable|string',
            'openingstijden' => 'nullable|array',
        ]);

        // Zorg voor consistente JSON-opslag
        $data['openingstijden'] = json_encode($data['openingstijden'] ?? []);

        $instelling = Instelling::first();
        if (!$instelling) {
            Instelling::create($data);
        } else {
            $instelling->update($data);
        }

        return back()->with('success', 'Instellingen opgeslagen.');
    }

    public function opslaanKortingscode(Request $request)
    {
        // Basisvalidatie
        $validated = $request->validate([
            'code'       => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9\-_]+$/', // alleen letters, cijfers, - en _
                Rule::unique('kortingscodes', 'code'),
            ],
            'type'       => ['required', Rule::in(['percent', 'amount'])],
            'value'      => ['required', 'numeric'],
            'vervalt_op' => ['required', 'date'],
        ], [
            'code.regex' => 'Gebruik alleen letters, cijfers, streepjes (-) of underscores (_).',
        ]);

        // Conditionele grenzen
        if ($validated['type'] === 'percent') {
            $request->validate([
                'value' => ['numeric', 'min:1', 'max:100'],
            ]);
        } else { // amount (vast bedrag in €)
            $request->validate([
                'value' => ['numeric', 'min:0.01'],
            ]);
        }

        // Datum moet in de toekomst liggen
        $expires = Carbon::parse($validated['vervalt_op']);
        if (now()->greaterThanOrEqualTo($expires)) {
            return back()
                ->withErrors(['vervalt_op' => 'De vervaldatum moet in de toekomst liggen.'])
                ->withInput();
        }

        // Aanmaken (model normaliseert code naar uppercase)
        Kortingscode::create([
            'code'       => $validated['code'],
            'type'       => $validated['type'],
            'value'      => $validated['value'],
            'vervalt_op' => $expires,
        ]);

        return back()->with('success', 'Kortingscode aangemaakt.');
    }

    public function verwijderKortingscode(Kortingscode $kortingscode)
    {
        $kortingscode->delete();
        return back()->with('success', 'Kortingscode verwijderd.');
    }
}
