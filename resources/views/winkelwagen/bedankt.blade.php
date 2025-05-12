@extends('layouts.pages')
@section('content')
<div class="w-full h-auto">
    <div class="max-w-[1100px] mx-auto py-16">
        <div class="w-full flex items-center justify-between mb-2">
            <a href="/" class="text-[#191919] opacity-50 text-[12px] hover:underline">
                Terug naar de homepagina
            </a>
        </div>

        <h1 class="text-[#191919] text-[38px] font-semibold leading-[1.15] mb-2"><i class="instrument-serif-font text-[#ff64ba]">Bedankt</i> voor je bestelling! 🎉</h1>
        <p class="text-[#191919] opacity-80 text-[15px] mb-8"><strong>Bestelling ID:</strong> {{ $bestelling->transactie_id }}</p>

        <div class="w-full bg-white rounded-lg p-[2rem] mb-8">
            <div class="w-full h-[250px] rounded-lg" style="background: url({{ Vite::asset('resources/images/bedankt.jpg') }}); background-position: center; background-size: cover;"></div>
            <p class="text-[15px] text-[#191919] opacity-80 mt-4">
                Je bestelling is succesvol ontvangen.<br>We zullen je via <span class="font-medium">{{ $bestelling->email }}</span> op de hoogte houden.
            </p>
        </div>

        <ul class="divide-y divide-gray-100 text-[15px] text-[#191919] opacity-90">
            @foreach($producten as $item)
                <li class="py-3 grid grid-cols-12 items-center gap-4">
                    <div class="col-span-6 flex items-center gap-4">
                        <span class="font-medium max-w-[200px]">{{ $item->naam }}</span>
                    </div>
                    <div class="col-span-3 text-center">
                        {{ $item->pivot->aantal }}
                    </div>
                    <div class="col-span-3 text-right font-medium">
                        &euro;{{ number_format($item->prijs * $item->pivot->aantal, 2, ',', '.') }}
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
