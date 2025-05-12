<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;


class InlogController extends Controller
{
    public function toonFormulier()
    {
        return view('auth.inloggen');
    }

    public function verwerk(Request $request)
    {
        $gegevens = $request->validate([
            'email' => ['required', 'email'],
            'wachtwoord' => ['required'],
        ]);

        // Let op: in je formulier moet het veld nog 'password' heten
        if (Auth::attempt([
            'email' => $gegevens['email'],
            'password' => $gegevens['wachtwoord']
        ])) {
            $request->session()->regenerate();
            return redirect()->intended(route('beheer.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Ongeldige inloggegevens.',
        ]);
    }

    public function uitloggen(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/inloggen');
    }
}
