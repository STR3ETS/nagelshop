<?php

namespace App\Http\Controllers;

use App\Models\Instelling;
use App\Models\Kortingscode;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class InstellingenController extends Controller
{
    public function index()
    {
        $instellingen = Instelling::first() ?? new Instelling();
        $kortingscodes = Kortingscode::orderBy('vervalt_op')->get();

        return view('beheer.instellingen.index', compact('instellingen', 'kortingscodes'));
    }

    public function opslaanAlgemeen(Request $request)
    {
        $data = $request->validate([
            'email' => 'nullable|email',
            'telefoon' => 'nullable|string',
            'btw_nummer' => 'nullable|string',
            'kvk_nummer' => 'nullable|string',
            'openingstijden' => 'nullable|array',
        ]);

        $data['openingstijden'] = json_encode($data['openingstijden']);

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
        $data = $request->validate([
            'korting' => 'required|integer|min:1|max:100',
            'vervalt_op' => 'required|date',
        ]);

        Kortingscode::create([
            'code' => strtoupper(Str::random(8)),
            'korting' => $data['korting'],
            'vervalt_op' => $data['vervalt_op'],
        ]);

        return back()->with('success', 'Kortingscode aangemaakt.');
    }

    public function verwijderKortingscode(Kortingscode $kortingscode)
    {
        $kortingscode->delete();
        return back()->with('success', 'Kortingscode verwijderd.');
    }
}