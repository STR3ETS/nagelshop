@extends('layouts.pages')
@section('content')
<div class="p-2 h-[350px] md:h-auto">
    <div class="w-full h-full md:h-[350px] overflow-y-hidden rounded-3xl flex items-end relative bg-cover bg-center bg-[url(https://i.imgur.com/UA8Iztb.jpeg)]">
        <div class="w-full h-full absolute z-[1] bg-[#00000050]"></div>
    </div>
</div>
<div class="w-full h-auto">
    <div class="max-w-[1100px] px-[1rem] md:px-[3rem] mx-auto py-16">
        <div class="w-full flex items-center justify-between mb-2">
            <a href="/producten" class="text-[#191919] opacity-50 text-[12px] hover:underline">
                Terug naar de shop
            </a>
        </div>

        <h1 class="text-[#191919] text-[38px] font-semibold leading-[1.15] mb-2">
            Jouw <i class="instrument-serif-font text-[#b38867]">winkelwagen</i>
        </h1>

        @if(count($cart))
        <div class="w-full flex flex-col md:flex-row gap-[1rem]">
            {{-- LINKERKOLOM: items --}}
            <div class="w-full md:w-2/3 bg-white p-[1.5rem] rounded-lg h-fit">
                <table class="w-full border border-pink-50 rounded-lg text-[15px]">
                    <tbody>
                        @foreach($cart as $id => $item)
                            <tr class="border-t border-gray-100">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-4">
                                        @if(!empty($item['foto']))
                                            <img src="{{ asset('storage/producten/' . $item['foto']) }}" class="w-18 h-18 object-cover rounded" alt="{{ $item['naam'] }}">
                                        @endif
                                        <div class="flex flex-col">
                                            <span class="text-[16px] font-medium max-w-[300px] leading-[1] mb-2">{{ $item['naam'] }}</span>
                                            {{-- Regel hieronder toont item-totaal (prijs * aantal), doorgaans INCL. btw --}}
                                            <span class="font-light">&euro;{{ number_format($item['prijs'] * $item['aantal'], 2, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-2">
                                        <form action="{{ route('winkelwagen.aantal', $id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="aantal" value="{{ max(1, $item['aantal'] - 1) }}">
                                            <button type="submit" class="px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm cursor-pointer">−</button>
                                        </form>

                                        <span class="w-[20px] text-center">{{ $item['aantal'] }}</span>

                                        <form action="{{ route('winkelwagen.aantal', $id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="aantal" value="{{ $item['aantal'] + 1 }}">
                                            <button type="submit" class="px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm cursor-pointer">+</button>
                                        </form>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <form action="{{ route('winkelwagen.verwijderen', $id) }}" method="POST" class="flex items-center justify-center">
                                        @csrf
                                        <button type="submit" class="text-gray-400 hover:text-red-500 transition cursor-pointer" title="Verwijderen" aria-label="Verwijderen">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m5 0H6" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- RECHTERKOLOM: overzicht --}}
            <div class="w-full md:w-1/3 bg-white p-[1.5rem] rounded-lg h-fit">
                <h2 class="text-[18px] font-semibold mb-4">Overzicht</h2>

                @php
                    // Drempel gratis verzending (alleen info op cart; verzending zelf pas bij adresstap)
                    $gratisVerzendingDrempel = 75;

                    // Kortingscode uit sessie: ['code','type' => 'percent'|'amount','value' => float]
                    $kortingscode = session('checkout.kortingscode');

                    // Totaal items (verondersteld INCL. btw per stukprijs)
                    $totaalInclItems = collect($cart)->sum(fn($i) => $i['prijs'] * $i['aantal']);

                    // Korting op items-totaal
                    $kortingBedrag = 0.0;
                    if ($kortingscode) {
                        $kortingBedrag = $kortingscode['type'] === 'percent'
                            ? ($kortingscode['value'] / 100) * $totaalInclItems
                            : (float) $kortingscode['value'];
                        $kortingBedrag = min($kortingBedrag, $totaalInclItems); // nooit meer dan totaal
                    }

                    // Totaal NA korting (nog zonder eventuele verzending)
                    $totaalNaKorting = max(0, $totaalInclItems - $kortingBedrag);

                    // BTW-breakdown over bedrag NA korting
                    $totaalExcl = $totaalNaKorting / 1.21;
                    $btw = $totaalNaKorting - $totaalExcl;

                    // Voortgang naar gratis verzending, gebaseerd op bedrag vóór korting (zoals vaak in marketing)
                    $nogTeGaan = max(0, $gratisVerzendingDrempel - $totaalInclItems);
                    $voortgang = $gratisVerzendingDrempel > 0
                        ? min(100, round(($totaalInclItems / $gratisVerzendingDrempel) * 100))
                        : 100;
                @endphp

                {{-- Subtotaal (vóór korting, incl. btw) --}}
                <div class="flex justify-between mb-2 text-[15px]">
                    <span>Subtotaal</span>
                    <span>&euro;{{ number_format($totaalInclItems, 2, ',', '.') }}</span>
                </div>

                {{-- Kortingscode UI + regel "Korting" --}}
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
                            <form method="POST" action="{{ route('winkelwagen.kortingscode.verwijderen') }}">
                                @csrf
                                <button class="text-red-600 hover:underline text-xs cursor-pointer">Verwijderen</button>
                            </form>
                        </div>

                        <div class="flex justify-between mt-2 text-[15px]">
                            <span>Korting</span>
                            <span>− &euro;{{ number_format($kortingBedrag, 2, ',', '.') }}</span>
                        </div>
                    @else
                        <form method="POST" action="{{ route('winkelwagen.kortingscode.toepassen') }}" class="flex gap-2">
                            @csrf
                            <input name="code" placeholder="Kortingscode" class="flex-1 px-3 py-2 border rounded-md text-[14px]" />
                            <button class="px-3 py-2 bg-black text-white rounded-md text-[14px] hover:bg-gray-900 cursor-pointer">Toepassen</button>
                        </form>
                        @error('code') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    @endif
                </div>

                {{-- Totaal (incl. btw, NA korting) --}}
                <div class="flex justify-between mt-4 pt-4 border-t border-[#eeeeee] font-semibold text-[16px]">
                    <span>Totaal</span>
                    <span>&euro;{{ number_format($totaalNaKorting, 2, ',', '.') }}</span>
                </div>

                {{-- Optioneel: BTW toelichting (over NA-korting bedrag) --}}
                <p class="text-right text-[12px] opacity-70 mt-1">
                    Waarvan BTW 21%: &euro;{{ number_format($btw, 2, ',', '.') }}
                </p>

                {{-- (Optioneel) gratis verzending hint op basis van bedrag vóór korting --}}
                @if ($totaalInclItems < $gratisVerzendingDrempel)
                    <div class="bg-[#b38867]/10 text-[#b38867] text-sm px-3 py-2 rounded-md my-4">
                        Besteed nog <span class="font-medium">{{ number_format($nogTeGaan, 2, ',', '.') }}</span> voor gratis verzending.
                        <div class="w-full bg-[#b38867]/25 rounded-full h-2 mt-2">
                            <div class="bg-[#b38867] h-2 rounded-full transition-all duration-300" style="width: {{ $voortgang }}%"></div>
                        </div>
                    </div>
                @else
                    <div class="bg-[#b38867]/10 text-[#b38867] text-sm px-3 py-2 rounded-md my-4">
                        Hoppa! Jouw bestelling komt in aanmerking voor gratis verzending.
                    </div>
                @endif

                <form action="{{ route('winkelwagen.contact') }}" method="GET">
                    <button type="submit" class="cursor-pointer mt-2 w-full py-3 bg-black text-white rounded-md text-[15px] font-medium hover:bg-gray-900 transition">
                        Verder met bestellen
                    </button>
                </form>
            </div>
        </div>
        @else
            <p class="text-[15px] text-[#191919] opacity-80">Je winkelwagen is momenteel leeg.</p>
        @endif
    </div>
</div>

{{-- Let op: fetch gebruikt meta csrf in je layout --}}
<script>
    function updateAantal(id, aantal) {
        if (aantal < 1) return;
        const meta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = meta ? meta.getAttribute('content') : '';

        fetch(`/winkelwagen/${id}/aantal`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ aantal })
        })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); })
        .catch(err => console.error('Fout bij bijwerken:', err));
    }
</script>
@endsection