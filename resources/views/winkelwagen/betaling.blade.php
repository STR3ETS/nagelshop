@extends('layouts.pages')
@section('content')
<div class="w-full h-auto">
    <div class="max-w-[1100px] mx-auto py-16">
        <div class="w-full flex items-center justify-between mb-2">
            <a href="/winkelwagen/contact" class="text-[#191919] opacity-50 text-[12px] hover:underline">
                Terug naar gegevens
            </a>
        </div>

        <h1 class="text-[#191919] text-[38px] font-semibold leading-[1.15] mb-8">Bevestig je <i class="instrument-serif-font text-[#ff64ba]">bestelling</i></h1>

        <div class="w-full flex gap-[1rem]">
            <div class="w-2/3 flex gap-[1rem] h-fit">
                <div class="w-full bg-white rounded-lg p-[2rem] mb-8">
                    <h2 class="text-[18px] font-medium mb-2">Klantgegevens</h2>
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
                                        <img src="{{ asset('storage/producten/' . $item['foto']) }}" class="w-12 h-12 object-cover rounded">
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
            <div class="w-1/3 bg-white p-[1.5rem] rounded-lg h-fit">
                <h2 class="text-[18px] font-semibold mb-4">Overzicht</h2>
                @php
                    $gratisVerzendingDrempel = 75;
                    $totaalIncl = collect($cart)->sum(fn($i) => $i['prijs'] * $i['aantal']);
                    $nogTeGaan = max(0, $gratisVerzendingDrempel - $totaalIncl);
                    $voortgang = min(100, round(($totaalIncl / $gratisVerzendingDrempel) * 100));
                    $btw = $totaalIncl * (21 / 121);
                    $subtotaal = $totaalIncl - $btw;
                    $btw = $totaalIncl * 0.21;
                    $verzendkosten = $totaalIncl >= $gratisVerzendingDrempel ? 0 : 4.90;
                    $totaalMetVerzending = $totaalIncl + $verzendkosten;
                @endphp
                <div class="flex justify-between mb-2 text-[15px]">
                    <span>Subtotaal</span>
                    <span>&euro;{{ number_format($totaalIncl, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between mb-2 text-[15px]">
                    <span>BTW 21%</span>
                    <span>&euro;{{ number_format($btw, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between mb-2 text-[15px]">
                    <span>Bezorging</span>
                    <span>&euro;{{ number_format($verzendkosten, 2, ',', '.') }}</span>
                </div>
                @if ($totaalIncl < $gratisVerzendingDrempel)
                    <div class="bg-pink-50 text-pink-600 text-sm px-3 py-2 rounded-md my-4">
                        Besteed nog <span class="font-medium">{{ number_format($nogTeGaan, 2, ',', '.') }}</span><br>om gratis verzending te krijgen!
                        <div class="flex flex-col justify-end gap-[0.5rem] mt-2">
                            <div class="w-full bg-pink-100 rounded-full h-2 mt-2">
                                <div class="bg-[#ff64ba] h-2 rounded-full transition-all duration-300" style="width: {{ $voortgang }}%"></div>
                            </div>
                            <span class="text-end text-xs">Gratis verzending</span>
                        </div>
                    </div>
                @else
                    <div class="bg-pink-50 text-pink-600 text-sm px-3 py-2 rounded-md my-4 relative">
                        Hoppa! Jouw bestelling word<br>volledig gratis geleverd.
                        <div class="w-full bg-pink-100 rounded-full h-2 mt-2">
                            <div class="bg-[#ff64ba] h-2 rounded-full transition-all duration-300" style="width: {{ $voortgang }}%"></div>
                        </div>
                        <lord-icon class="absolute z-1 right-3 top-3"
                            src="https://cdn.lordicon.com/lomfljuq.json"
                            trigger="hover"
                            colors="primary:#ff64ba"
                            style="width:30px;height:30px">
                        </lord-icon>
                    </div>
                @endif
                <div class="flex justify-between mt-4 pt-4 border-t border-[#eeeeee] font-semibold text-[16px]">
                    <span>Totaal</span>
                    <span>&euro;{{ number_format($totaalMetVerzending, 2, ',', '.') }}</span>
                </div>
                <form action="{{ route('winkelwagen.afronden') }}" method="POST">
                    @csrf
                    <input type="hidden" name="betaalmethode" value="ideal">
                    <button type="submit" class="mt-5 w-full py-3 bg-black text-white rounded-md text-[15px] font-medium hover:bg-gray-900 transition">
                        Ga naar betaling via Mollie
                    </button>
                </form>
            </div>
        </div>

        @php
            $totaalIncl = collect($cart)->sum(fn($i) => $i['prijs'] * $i['aantal']);
            $btw = $totaalIncl * (21 / 121);
            $verzendkosten = $totaalIncl >= 75 ? 0 : 4.90;
            $totaalMetVerzending = $totaalIncl + $verzendkosten;
        @endphp
    </div>
</div>
@endsection
