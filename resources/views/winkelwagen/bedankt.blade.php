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
            <a href="/" class="text-[#191919] opacity-50 text-[12px] hover:underline">
                Terug naar de homepagina
            </a>
        </div>

        <h1 class="text-[#191919] text-[28px] md:text-[38px] font-semibold leading-[1.15] mb-2"><i class="instrument-serif-font text-[#b38867]">Bedankt</i> voor je bestelling! 🎉</h1>
        <p class="text-[#191919] opacity-80 text-[15px] mb-8"><strong>Bestelling ID:</strong> {{ $bestelling->transactie_id }}</p>

        <div class="w-full bg-white rounded-lg p-[2rem] mb-8 flex flex-col md:flex-row gap-8">
            {{-- Afbeelding links (1/3) --}}
            <div class="w-full md:w-1/3">
                <div class="w-full aspect-square rounded-lg overflow-hidden bg-[url(https://i.gifer.com/FBdV.gif)] bg-cover bg-center"></div>
            </div>

            {{-- Inhoud rechts (2/3) --}}
            <div class="w-full md:w-2/3 flex flex-col justify-between">
                <div>
                    <p class="text-[15px] text-[#191919] opacity-80 mb-2">
                        Wat leuk dat je hebt besteld bij Deluxe Nailshop 🥰
                    </p>
                    <p class="text-[15px] text-[#191919] opacity-80 mb-2">
                        We gaan direct voor je aan de slag en houden je via <span class="font-medium">{{ $bestelling->email }}</span> op de hoogte van de voortgang. Zodra je bestelling onderweg is, laten we het je weten.
                    </p>
                    <p class="text-[15px] text-[#191919] opacity-80 mb-8">
                        Heb je in de tussentijd vragen of wil je iets wijzigen? Neem dan gerust contact met ons op, we helpen je graag!
                    </p>
                </div>

                <div>
                    <h2 class="text-[#191919] text-[24px] font-semibold leading-[1.15] mb-2">Besteloverzicht</h2>
                    <ul class="divide-y divide-gray-200 text-[15px] text-[#191919] opacity-90 p-[1.5rem] border-1 border-gray-200 rounded-lg bg-gray-100">
                        @foreach($bestellingProducten as $item)
                            <li class="py-3 grid grid-cols-16 items-center gap-4">
                                <div class="col-span-10 flex items-center gap-4">
                                    <span class="font-medium max-w-[200px] leading-tight">{{ $item->naam }}</span>
                                </div>
                                <div class="col-span-3 text-center">
                                    {{ $item->pivot->aantal }}
                                </div>
                                <div class="col-span-3 text-right font-medium">
                                    &euro;{{ number_format($item->prijs * $item->pivot->aantal, 2, ',', '.') }}
                                </div>
                            </li>
                        @endforeach
                        <div class="w-full flex items-center justify-end mt-4 text-[#b38867]">
                            <span class="text-[15px] font-medium">Totaal: &euro;{{ number_format($bestelling->totaalprijs, 2, ',', '.') }}</span>
                        </div>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
