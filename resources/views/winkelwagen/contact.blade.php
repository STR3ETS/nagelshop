@extends('layouts.pages')
@section('content')
<div class="w-full h-auto">
    <div class="max-w-[1100px] px-[1rem] md:px-[3rem] mx-auto py-16">
        <div class="w-full flex items-center justify-between mb-2">
            <a href="/winkelwagen" class="text-[#191919] opacity-50 text-[12px] hover:underline">
                Terug naar winkelwagen
            </a>
        </div>

        <h1 class="text-[#191919] text-[38px] font-semibold leading-[1.15] mb-8">
            Contact- en <i class="instrument-serif-font text-[#b38867]">aflevergegevens</i>
        </h1>

        @php
            // Data uit sessie en cart
            $cart = $cart ?? session('cart', []);
            $gegevens = session('checkout.gegevens', []);
            $kortingscode = session('checkout.kortingscode'); // ['code','type','value'] of null

            // Bedragen
            $gratisVerzendingDrempel = 75;
            $totaalInclItems = collect($cart)->sum(fn($i) => $i['prijs'] * $i['aantal']);

            // 1) Korting
            $kortingBedrag = 0.0;
            if ($kortingscode) {
                $kortingBedrag = $kortingscode['type'] === 'percent'
                    ? ($kortingscode['value'] / 100) * $totaalInclItems
                    : (float) $kortingscode['value'];
                $kortingBedrag = min($kortingBedrag, $totaalInclItems);
            }
            $totaalNaKorting = max(0, $totaalInclItems - $kortingBedrag);

            // 2) BTW over bedrag na korting (items)
            $totaalExcl = $totaalNaKorting / 1.21;
            $btw = $totaalNaKorting - $totaalExcl;

            // 3) Bezorging alleen tonen/berekenen als adres + postcode + plaats aanwezig
            $adresOk = !empty($gegevens['adres'] ?? null) && !empty($gegevens['postcode'] ?? null) && !empty($gegevens['plaats'] ?? null);

            $verzendkosten = null; // null = niet tonen
            if ($adresOk) {
                $pc = strtoupper(trim($gegevens['postcode']));
                // NL: 1234 AB (spatie optioneel)
                $isNL = (bool) preg_match('/^\d{4}\s?[A-Z]{2}$/', $pc);
                // BE: 1234 (exact 4 cijfers)
                $isBE = !$isNL && (bool) preg_match('/^\d{4}$/', $pc);

                $tarief = $isBE ? 9.50 : 5.95; // default NL indien onherkenbaar
                // Gratis boven drempel NA korting
                if ($totaalNaKorting >= $gratisVerzendingDrempel) {
                    $verzendkosten = 0.0;
                } else {
                    $verzendkosten = $tarief;
                }
            }

            // 4) Eindtotaal
            $totaalMetVerzending = $totaalNaKorting + ($verzendkosten ?? 0);

            // Voortgang (op basis van items-totaal vóór korting)
            $nogTeGaan = max(0, $gratisVerzendingDrempel - $totaalInclItems);
            $voortgang = $gratisVerzendingDrempel > 0
                ? min(100, round(($totaalInclItems / $gratisVerzendingDrempel) * 100))
                : 100;
        @endphp

        <div class="w-full flex flex-col md:flex-row gap-[1rem]">
            {{-- LINKER KOLOM: ENIG FORMULIER (POST naar contactOpslaan) --}}
            <form action="{{ route('winkelwagen.contactOpslaan') }}" method="POST" class="bg-white p-[2rem] rounded-lg w-full md:w-2/3 h-fit">
                @csrf

                <div class="mb-2">
                    <label class="text-sm font-medium">Voor- en achternaam</label>
                    <input type="text" name="naam" value="{{ old('naam', $gegevens['naam'] ?? '') }}" required class="w-full px-[0.75rem] py-[0.55rem] ring-1 ring-gray-200 focus:ring-[#b38867] outline-none rounded-md">
                </div>

                <div class="mb-2">
                    <label class="text-sm font-medium">E-mailadres</label>
                    <input type="email" name="email" value="{{ old('email', $gegevens['email'] ?? '') }}" required class="w-full px-[0.75rem] py-[0.55rem] ring-1 ring-gray-200 focus:ring-[#b38867] outline-none rounded-md">
                </div>

                <div class="mb-2">
                    <label class="text-sm font-medium">Telefoonnummer</label>
                    <input type="tel" name="telefoon" value="{{ old('telefoon', $gegevens['telefoon'] ?? '') }}" required inputmode="tel" autocomplete="tel" class="w-full px-[0.75rem] py-[0.55rem] ring-1 ring-gray-200 focus:ring-[#b38867] outline-none rounded-md">
                </div>

                <div class="mb-2">
                    <label class="text-sm font-medium">Adres</label>
                    <input type="text" name="adres" value="{{ old('adres', $gegevens['adres'] ?? '') }}" required class="w-full px-[0.75rem] py-[0.55rem] ring-1 ring-gray-200 focus:ring-[#b38867] outline-none rounded-md">
                </div>

                <div class="flex flex-col md:flex-row gap-2 md:gap-4 mb-2">
                    <div class="w-full md:w-1/2">
                        <label class="text-sm font-medium">Postcode</label>
                        <input type="text" name="postcode" value="{{ old('postcode', $gegevens['postcode'] ?? '') }}" required class="w-full px-[0.75rem] py-[0.55rem] ring-1 ring-gray-200 focus:ring-[#b38867] outline-none rounded-md">
                    </div>
                    <div class="w-full md:w-1/2">
                        <label class="text-sm font-medium">Plaats</label>
                        <input type="text" name="plaats" value="{{ old('plaats', $gegevens['plaats'] ?? '') }}" required class="w-full px-[0.75rem] py-[0.55rem] ring-1 ring-gray-200 focus:ring-[#b38867] outline-none rounded-md">
                    </div>
                </div>

                <button type="submit" class="cursor-pointer mt-5 w-full py-3 bg-black text-white rounded-md text-[15px] font-medium hover:bg-gray-900 transition">
                    Naar besteloverzicht
                </button>
            </form>

            {{-- RECHTER KOLOM: OVERZICHT (geen form hierbinnen) --}}
            <div class="w-full md:w-1/3 bg-white p-[1.5rem] rounded-lg flex flex-col justify-between h-fit">
                <div>
                    <h2 class="text-[18px] font-semibold mb-4">Overzicht</h2>

                    {{-- Subtotaal (vóór korting, incl. btw) --}}
                    <div class="flex justify-between mb-2 text-[15px]">
                        <span>Subtotaal</span>
                        <span>&euro;{{ number_format($totaalInclItems, 2, ',', '.') }}</span>
                    </div>

                    {{-- Kortingscode UI (knoppen sturen naar losse forms via form="...") --}}
                    <div class="mt-3">
                        @if($kortingscode)
                            <div class="flex items-center justify-between text-sm bg-[#b38867]/10 rounded px-3 py-2">
                                <div>
                                    <span class="font-medium">Kortingscode:<br></span>
                                    <span class="font-mono">{{ $kortingscode['code'] }}</span>
                                    <span class="ml-2 opacity-80">
                                        ({{ $kortingscode['type'] === 'amount'
                                            ? '€'.number_format($kortingscode['value'],2,',','.')
                                            : rtrim(rtrim(number_format($kortingscode['value'],2,',','.'),'0'),',').'%'
                                        }})
                                    </span>
                                </div>
                                <button form="kortingscode-remove-form" class="text-red-600 hover:underline text-xs cursor-pointer">
                                    Verwijderen
                                </button>
                            </div>
                            <div class="flex justify-between mt-2 text-[15px]">
                                <span>Korting</span>
                                <span>− &euro;{{ number_format($kortingBedrag, 2, ',', '.') }}</span>
                            </div>
                        @else
                            <div class="flex gap-2">
                                <input name="code" form="kortingscode-apply-form" placeholder="Kortingscode" class="flex-1 px-3 py-2 border rounded-md text-[14px]" />
                                <button form="kortingscode-apply-form" class="px-3 py-2 bg-black text-white rounded-md text-[14px] hover:bg-gray-900 cursor-pointer">
                                    Toepassen
                                </button>
                            </div>
                            @error('code') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        @endif
                    </div>

                    {{-- Bezorging (optioneel, indien adres OK) --}}
                    @if(!is_null($verzendkosten))
                        <div class="flex justify-between mb-2 text-[15px] mt-2">
                            <span>Bezorging</span>
                            <span>
                                @if($verzendkosten == 0)
                                    Gratis
                                @else
                                    &euro;{{ number_format($verzendkosten, 2, ',', '.') }}
                                @endif
                            </span>
                        </div>
                    @endif

                    {{-- Totaal (incl. btw, NA korting + verzending) --}}
                    <div class="flex justify-between mt-4 pt-4 border-t border-[#eeeeee] font-semibold text-[16px]">
                        <span>Totaal</span>
                        <span>&euro;{{ number_format($totaalMetVerzending, 2, ',', '.') }}</span>
                    </div>

                    {{-- Toelichting btw over NA-korting (items) --}}
                    <p class="text-right text-[12px] opacity-70 mt-1">
                        Waarvan BTW 21%: &euro;{{ number_format($btw, 2, ',', '.') }}
                    </p>

                    {{-- Voortgang gratis verzending (op basis van bedrag vóór korting) --}}
                    @if ($totaalNaKorting < $gratisVerzendingDrempel)
                        <div class="bg-[#b38867]/10 text-[#b38867] text-sm px-3 py-2 rounded-md my-4">
                            Besteed nog <span class="font-medium">{{ number_format($nogTeGaan, 2, ',', '.') }}</span><br>om gratis verzending te krijgen!
                            <div class="flex flex-col justify-end gap-[0.5rem] mt-2">
                                <div class="w-full bg-[#b38867]/25 rounded-full h-2 mt-2">
                                    <div class="bg-[#b38867] h-2 rounded-full transition-all duration-300" style="width: {{ $voortgang }}%"></div>
                                </div>
                                <span class="text-end text-xs">Gratis verzending</span>
                            </div>
                        </div>
                    @else
                        <div class="bg-[#b38867]/10 text-[#b38867] text-sm px-3 py-2 rounded-md my-4">
                            Hoppa! Jouw bestelling wordt volledig gratis geleverd.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- LOSSE (verborgen) FORMS VOOR KORTINGSCODE — BUITEN ELK ANDER FORM --}}
        <form id="kortingscode-apply-form" method="POST" action="{{ route('winkelwagen.kortingscode.toepassen') }}" class="hidden">
            @csrf
        </form>
        <form id="kortingscode-remove-form" method="POST" action="{{ route('winkelwagen.kortingscode.verwijderen') }}" class="hidden">
            @csrf
        </form>
    </div>
</div>
@endsection
