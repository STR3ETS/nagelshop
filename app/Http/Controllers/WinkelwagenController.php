<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Bestelling;
use App\Models\Kortingscode;
use Illuminate\Support\Str;
use Mollie\Laravel\Facades\Mollie;
use Illuminate\Validation\Rule;

class WinkelwagenController extends Controller
{
    // -------------------------------
    // WINKELWAGEN
    // -------------------------------
    public function toevoegen(Request $request, Product $product)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['aantal']++;
        } else {
            $cart[$product->id] = [
                'naam'   => $product->naam,
                'prijs'  => $product->prijs,
                'aantal' => 1,
                'foto'   => $product->foto,
            ];
        }

        session()->put('cart', $cart);

        return back()->with('toegevoegd', $product->naam);
    }

    public function index()
    {
        $cart = session()->get('cart', []);
        return view('winkelwagen.index', compact('cart'));
    }

    public function verwijderen($id)
    {
        $cart = session()->get('cart', []);
        unset($cart[$id]);
        session()->put('cart', $cart);

        return back()->with('success', 'Product verwijderd uit winkelwagen.');
    }

    public function updateAantal(Request $request, $id)
    {
        $request->validate([
            'aantal' => 'required|integer|min:1'
        ]);

        $cart = session()->get('cart', []);
        if (isset($cart[$id])) {
            $cart[$id]['aantal'] = $request->aantal;
            session()->put('cart', $cart);
        }

        return redirect()->back();
    }

    // -------------------------------
    // CONTACT / AFLEVERGEGEVENS
    // -------------------------------
    public function toonContactForm()
    {
        $cart = session()->get('cart', []);
        $gegevens = session('checkout.gegevens', []);

        return view('winkelwagen.contact', compact('cart', 'gegevens'));
    }

    public function contactOpslaan(Request $request)
    {
        $data = $request->validate([
            'levermethode' => ['required', Rule::in(['verzenden', 'ophalen'])],

            'naam'     => ['required', 'string', 'max:120'],
            'email'    => ['required', 'email'],
            'telefoon' => ['required', 'string', 'min:6', 'max:20'],

            'adres'    => ['required_if:levermethode,verzenden', 'nullable', 'string', 'max:200'],
            'postcode' => ['required_if:levermethode,verzenden', 'nullable', 'string', 'max:20'],
            'plaats'   => ['required_if:levermethode,verzenden', 'nullable', 'string', 'max:120'],
        ]);

        if (($data['levermethode'] ?? 'verzenden') === 'ophalen') {
            $data['adres'] = null;
            $data['postcode'] = null;
            $data['plaats'] = null;
        }

        session(['checkout.gegevens' => $data]);

        return redirect()->route('winkelwagen.betaling');
    }

    // -------------------------------
    // KORTINGSCODES
    // -------------------------------
    public function kortingscodeToepassen(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9\-_]+$/'],
        ], [
            'code.regex' => 'Gebruik alleen letters, cijfers, streepjes (-) of underscores (_).',
        ]);

        $code = mb_strtoupper(trim($data['code']));
        $k = Kortingscode::where('code', $code)->first();

        if (!$k) {
            return back()->withErrors(['code' => 'Ongeldige kortingscode.'])->withInput();
        }
        if ($k->isExpired()) {
            return back()->withErrors(['code' => 'Deze kortingscode is verlopen.'])->withInput();
        }

        session(['checkout.kortingscode' => [
            'code'  => $k->code,
            'type'  => $k->type,   // 'percent' | 'amount'
            'value' => (float) $k->value,
        ]]);

        return back()->with('success', 'Kortingscode toegepast.');
    }

    public function kortingscodeVerwijderen()
    {
        session()->forget('checkout.kortingscode');
        return back()->with('success', 'Kortingscode verwijderd.');
    }

    // -------------------------------
    // BETALING / BEVESTIGEN
    // -------------------------------
    public function toonBetaling()
    {
        $cart = session()->get('cart', []);
        $gegevens = session('checkout.gegevens', []);

        return view('winkelwagen.betaling', compact('cart', 'gegevens'));
    }

    // -------------------------------
    // AFRONDEN + MOLLIE START
    // -------------------------------
    public function afronden(Request $request)
    {
        $request->validate([
            'betaalmethode' => 'required|string|in:ideal,paypal,creditcard',
        ]);

        $klant       = session('checkout.gegevens');
        $winkelwagen = session('cart', []);

        if (!$klant || empty($winkelwagen)) {
            return redirect()->route('winkelwagen.index')
                ->with('error', 'Je sessie is verlopen of je winkelwagen is leeg.');
        }

        // 1) Producten totaal
        $productenTotaal = collect($winkelwagen)->sum(fn($item) => $item['prijs'] * $item['aantal']);

        // 2) Kortingscode
        $sessieCode = session('checkout.kortingscode');
        $kortingBedrag = 0.0;
        $actieveKortingscode = null;

        if ($sessieCode && !empty($sessieCode['code'])) {
            $k = Kortingscode::where('code', mb_strtoupper($sessieCode['code']))->first();

            if ($k && !$k->isExpired()) {
                $actieveKortingscode = [
                    'code'  => $k->code,
                    'type'  => $k->type,
                    'value' => (float) $k->value,
                ];

                $kortingBedrag = $k->type === 'percent'
                    ? ($k->value / 100) * $productenTotaal
                    : (float) $k->value;

                $kortingBedrag = min($kortingBedrag, $productenTotaal);
            } else {
                session()->forget('checkout.kortingscode');
            }
        }

        $totaalNaKorting = max(0, $productenTotaal - $kortingBedrag);

        // 3) Verzendkosten (respecteer levermethode)
        $levermethode = $klant['levermethode'] ?? 'verzenden';

        $verzendkosten = 0.00;

        $adresOk = $levermethode === 'verzenden'
            && !empty($klant['adres'] ?? null)
            && !empty($klant['postcode'] ?? null)
            && !empty($klant['plaats'] ?? null);

        if ($levermethode === 'ophalen') {
            $verzendkosten = 0.00;
        } elseif ($adresOk) {
            $pc = strtoupper(trim($klant['postcode']));

            $isNL = (bool) preg_match('/^\d{4}\s?[A-Z]{2}$/', $pc);
            $isBE = !$isNL && (bool) preg_match('/^\d{4}$/', $pc);

            $tariefNL  = 6.35;
            $tariefBE  = 12.35;
            $drempelNL = 75.00;
            $drempelBE = 100.00;

            // fallback NL
            $isNL = $isNL || (!$isBE);

            $tarief  = $isBE ? $tariefBE : $tariefNL;
            $drempel = $isBE ? $drempelBE : $drempelNL;

            // gratis drempel op NA-korting
            $verzendkosten = $totaalNaKorting >= $drempel ? 0.00 : $tarief;
        }

        // 4) Eindtotaal
        $totaalprijs = round($totaalNaKorting + $verzendkosten, 2);

        // 5) UUID voor tijdelijke opslag
        $bestellingUuid = (string) Str::uuid();

        // 6) Tijdelijke sessie-bestelling
        session()->put("temp_bestellingen.$bestellingUuid", [
            'klant'          => $klant,
            'cart'           => $winkelwagen,
            'kortingscode'   => $actieveKortingscode,
            'korting_bedrag' => $kortingBedrag,
            'verzendkosten'  => $verzendkosten,
            'totaalprijs'    => $totaalprijs,
            'betaalmethode'  => $request->betaalmethode,
        ]);

        // 7) Mollie payment
        $payment = Mollie::api()->payments->create([
            'amount' => [
                'currency' => 'EUR',
                'value'    => number_format($totaalprijs, 2, '.', ''),
            ],
            'description' => 'Bestelling bij Deluxe Nailshop',
            'redirectUrl' => route('mollie.callback', ['bestelling' => $bestellingUuid]),
            'metadata'    => [
                'bestelling_id' => $bestellingUuid,
            ],
            // 'webhookUrl' => route('mollie.webhook'),
        ]);

        session()->put('mollie_payment_id', $payment->id);

        return redirect($payment->getCheckoutUrl());
    }

    // -------------------------------
    // MOLLIE CALLBACK (maakt DB record)
    // -------------------------------
    public function mollieCallback()
    {
        $paymentId = session('mollie_payment_id');
        if (!$paymentId) {
            return redirect()->route('winkelwagen.index')->with('error', 'Betaling sessie ontbreekt.');
        }

        $payment = Mollie::api()->payments->get($paymentId);

        if (!$payment->isPaid()) {
            return redirect()->route('winkelwagen.index')->with('error', 'Betaling is niet gelukt.');
        }

        $bestellingId = request('bestelling');
        $data = session("temp_bestellingen.$bestellingId");

        if (!$data) {
            return redirect()->route('winkelwagen.index')->with('error', 'Geen betalingsgegevens gevonden.');
        }

        // voorkom dubbele orders
        $bestaan = Bestelling::where('transactie_id', $payment->id)->first();
        if ($bestaan) {
            return redirect()->route('winkelwagen.bedankt', ['id' => $bestaan->id]);
        }

        $levermethode = $data['klant']['levermethode'] ?? 'verzenden';

        $bestelling = Bestelling::create([
            'transactie_id' => $payment->id,
            'naam'          => $data['klant']['naam'],
            'email'         => $data['klant']['email'],
            'telefoon'      => $data['klant']['telefoon'],

            'levermethode'  => $levermethode,

            'adres'         => $levermethode === 'verzenden' ? ($data['klant']['adres'] ?? null) : null,
            'postcode'      => $levermethode === 'verzenden' ? ($data['klant']['postcode'] ?? null) : null,
            'plaats'        => $levermethode === 'verzenden' ? ($data['klant']['plaats'] ?? null) : null,

            'betaalmethode' => $data['betaalmethode'],
            'totaalprijs'   => $data['totaalprijs'],
            'status'        => 'open',
        ]);

        // Koppel producten + verlaag voorraad
        $product_data = [];

        foreach ($data['cart'] as $id => $item) {
            $product_data[$id] = ['aantal' => $item['aantal']];

            $product = Product::find($id);
            if ($product) {
                $product->voorraad = max(0, $product->voorraad - $item['aantal']);
                $product->save();
            }
        }

        $bestelling->producten()->sync($product_data);

        // Ruim sessie op
        session()->forget([
            'cart',
            'checkout.gegevens',
            'checkout.kortingscode',
            'mollie_payment_id',
            "temp_bestellingen.$bestellingId"
        ]);

        return redirect()->route('winkelwagen.bedankt', ['id' => $bestelling->id]);
    }

    // -------------------------------
    // (OPTIONEEL) WEBHOOK
    // Let op: jij maakt de DB-bestelling pas in callback.
    // Daarom is deze webhook in jouw huidige flow niet betrouwbaar.
    // -------------------------------
    public function mollieWebhook(Request $request)
    {
        $payment = Mollie::api()->payments->get($request->id);

        // Als je dit later wilt gebruiken, moet je eerst een bestelling record
        // aanmaken vóór de betaling en hier opzoeken via payment_id.
        return response()->json(['status' => 'ok']);
    }

    // -------------------------------
    // BEDANKT-PAGINA
    // -------------------------------
    public function bedankt($id)
    {
        $bestelling = Bestelling::findOrFail($id);
        $bestellingProducten = $bestelling->producten;

        $gegevens = [
            'naam'         => $bestelling->naam,
            'email'        => $bestelling->email,
            'telefoon'     => $bestelling->telefoon,
            'adres'        => $bestelling->adres,
            'postcode'     => $bestelling->postcode,
            'plaats'       => $bestelling->plaats,
            'levermethode' => $bestelling->levermethode ?? 'verzenden',
        ];

        // Subtotaal (weergave)
        $totaalIncl = $bestellingProducten->sum(fn($item) => $item->prijs * $item->pivot->aantal);
        $btw = $totaalIncl * 0.21;

        $levermethode = $bestelling->levermethode ?? 'verzenden';

        if ($levermethode === 'ophalen') {
            $verzendkosten = 0.00;
        } else {
            $pc = strtoupper(trim($bestelling->postcode));

            $isNL = (bool) preg_match('/^\d{4}\s?[A-Z]{2}$/', $pc);
            $isBE = !$isNL && (bool) preg_match('/^\d{4}$/', $pc);

            $tariefNL  = 6.35;
            $tariefBE  = 12.35;
            $drempelNL = 75.00;
            $drempelBE = 100.00;

            $isNL = $isNL || (!$isBE);

            $tarief  = $isBE ? $tariefBE : $tariefNL;
            $drempel = $isBE ? $drempelBE : $drempelNL;

            $verzendkosten = $totaalIncl >= $drempel ? 0.00 : $tarief;
        }

        $totaalMetVerzending = $totaalIncl + $verzendkosten;

        return view('winkelwagen.bedankt', compact(
            'bestelling',
            'gegevens',
            'bestellingProducten',
            'totaalIncl',
            'btw',
            'verzendkosten',
            'totaalMetVerzending'
        ));
    }
}