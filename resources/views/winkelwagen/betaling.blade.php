@extends('layouts.pages')
@section('content')
<div class="w-full h-auto">
    <div class="max-w-[1100px] px-[1rem] md:px-[3rem] mx-auto py-16">
        <div class="w-full flex items-center justify-between mb-2">
            <a href="/winkelwagen/contact" class="text-[#191919] opacity-50 text-[12px] hover:underline">
                Terug naar gegevens
            </a>
        </div>

        <h1 class="text-[#191919] text-[38px] font-semibold leading-[1.15] mb-8">
            Bevestig je <i class="instrument-serif-font text-[#b38867]">bestelling</i>
        </h1>

        <div class="w-full flex flex-col md:flex-row gap-[1rem]">
            <div class="w-full md:w-2/3 flex gap-[1rem] h-fit">
                <div class="w-full bg-white rounded-lg p-[2rem] mb-8">
                    <h2 class="text-[18px] font-medium mb-2">Ingevulde gegevens</h2>
                    <div class="text-[15px] text-[#191919] opacity-80 space-y-1">
                        <p><strong>Naam:</strong> {{ $gegevens['naam'] }}</p>
                        <p><strong>Email:</strong> {{ $gegevens['email'] }}</p>
                        <p><strong>Adres:</strong> {{ $gegevens['adres'] }}, {{ $gegevens['postcode'] }} {{ $gegevens['plaats'] }}</p>
                    </div>

                    <h2 class="text-[18px] font-medium mt-6">Bestelling</h2>
                    <ul class="divide-y divide-gray-100 text-[15px] text-[#191919] opacity-90">
                        @foreach($cart as $item)
                            <li class="py-3 grid grid-cols-12 items-center gap-4">
                                <div class="col-span-6 flex items-center gap-4">
                                    @if($item['foto'])
                                        <img src="{{ asset('storage/producten/' . $item['foto']) }}" class="w-12 h-12 object-cover rounded" alt="{{ $item['naam'] }}">
                                    @endif
                                    <span class="font-medium max-w-[200px]">{{ $item['naam'] }}</span>
                                </div>
                                <div class="col-span-3 text-center">
                                    {{ $item['aantal'] }}
                                </div>
                                <div class="col-span-3 text-right font-medium">
                                    &euro;{{ number_format($item['prijs'] * $item['aantal'], 2, ',', '.') }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Rechterkolom: geen geneste forms, afronden-form staat onderaan --}}
            <div class="w-full md:w-1/3 bg-white p-[1.5rem] rounded-lg h-fit">
                <h2 class="text-[18px] font-semibold mb-4">Overzicht</h2>
                @php
                    $gratisVerzendingDrempel = 75;
                    $kortingscode = session('checkout.kortingscode');

                    // Gebruik doorgegeven $gegevens als bron; fallback naar sessie
                    $gegevensSess = $gegevens ?? session('checkout.gegevens', []);

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

                    // 2) BTW over bedrag na korting
                    $totaalExcl = $totaalNaKorting / 1.21;
                    $btw = $totaalNaKorting - $totaalExcl;

                    // 3) Verzendkosten (alleen als adres compleet)
                    $adresOk = !empty($gegevensSess['adres'] ?? null)
                        && !empty($gegevensSess['postcode'] ?? null)
                        && !empty($gegevensSess['plaats'] ?? null);

                    $verzendkosten = null;
                    if ($adresOk) {
                        $pc = strtoupper(trim($gegevensSess['postcode']));
                        $isNL = (bool) preg_match('/^\d{4}\s?[A-Z]{2}$/', $pc); // 1234 AB
                        $isBE = !$isNL && (bool) preg_match('/^\d{4}$/', $pc);  // 1234
                        $tarief = $isBE ? 9.50 : 5.95;

                        // Gratis vanaf drempel NA korting
                        if ($totaalNaKorting >= $gratisVerzendingDrempel) {
                            $verzendkosten = 0.0;
                        } else {
                            $verzendkosten = $tarief;
                        }
                    }

                    // 4) Eindtotaal
                    $totaalMetVerzending = $totaalNaKorting + ($verzendkosten ?? 0);

                    // Hint/voortgang op basis van items-totaal vóór korting
                    $nogTeGaan = max(0, $gratisVerzendingDrempel - $totaalInclItems);
                    $voortgang = min(100, round(($totaalInclItems / $gratisVerzendingDrempel) * 100));
                @endphp

                {{-- Subtotaal (vóór korting, incl. btw) --}}
                <div class="flex justify-between mb-2 text-[15px]">
                    <span>Subtotaal</span>
                    <span>&euro;{{ number_format($totaalInclItems, 2, ',', '.') }}</span>
                </div>

                {{-- Kortingscode UI: knoppen sturen naar losse forms via form="..." --}}
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

                {{-- Toelichting btw (over NA-korting items-bedrag) --}}
                <p class="text-right text-[12px] opacity-70 mt-1">
                    Waarvan BTW 21%: &euro;{{ number_format($btw, 2, ',', '.') }}
                </p>

                {{-- Optionele gratis-verzending hint op basis van bedrag vóór korting --}}
                @if ($totaalInclItems < $gratisVerzendingDrempel)
                    <div class="bg-[#b38867]/10 text-[#b38867] text-sm px-3 py-2 rounded-md my-4">
                        Besteed nog <span class="font-medium">{{ number_format($nogTeGaan, 2, ',', '.') }}</span> om gratis verzending te krijgen!
                        <div class="w-full bg-[#b38867]/25 rounded-full h-2 mt-2">
                            <div class="bg-[#b38867] h-2 rounded-full transition-all duration-300" style="width: {{ $voortgang }}%"></div>
                        </div>
                    </div>
                @else
                    <div class="bg-[#b38867]/10 text-[#b38867] text-sm px-3 py-2 rounded-md my-4">
                        Hoppa! Jouw bestelling wordt volledig gratis geleverd.
                    </div>
                @endif

                {{-- Los afronden-form --}}
                <form action="{{ route('winkelwagen.afronden') }}" method="POST" class="mt-5">
                    @csrf
                    <input type="hidden" name="betaalmethode" value="ideal">
                    <button type="submit" class="cursor-pointer w-full py-3 bg-black text-white rounded-md text-[15px] font-medium hover:bg-gray-900 transition">
                        Ga naar betaling via Mollie
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Losse, ongeneste forms voor kortingscode --}}
<form id="kortingscode-apply-form" method="POST" action="{{ route('winkelwagen.kortingscode.toepassen') }}" class="hidden">
    @csrf
</form>
<form id="kortingscode-remove-form" method="POST" action="{{ route('winkelwagen.kortingscode.verwijderen') }}" class="hidden">
    @csrf
</form>
@endsection
