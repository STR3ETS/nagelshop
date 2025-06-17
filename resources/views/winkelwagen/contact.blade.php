@extends('layouts.pages')
@section('content')
<div class="w-full h-auto">
    <div class="max-w-[1100px] mx-auto py-16">
        <div class="w-full flex items-center justify-between mb-2">
            <a href="/winkelwagen" class="text-[#191919] opacity-50 text-[12px] hover:underline">
                Terug naar winkelwagen
            </a>
        </div>
        <h1 class="text-[#191919] text-[38px] font-semibold leading-[1.15] mb-8">Contact- en <i class="instrument-serif-font text-[#ff64ba]">aflevergegevens</i></h1>
        @php
            $gegevens = session('checkout.gegevens', []);
        @endphp
        <form action="{{ route('winkelwagen.contactOpslaan') }}" method="POST" class="w-full flex gap-[1rem]">
            @csrf
            <div class="bg-white p-[2rem] rounded-lg w-2/3 h-fit">
                <div class="mb-2">
                    <label class="text-sm font-medium">Voor- en achternaam</label>
                    <input type="text" name="naam" value="{{ old('naam', $gegevens['naam'] ?? '') }}" required class="w-full px-[0.75rem] py-[0.55rem] ring-1 ring-gray-200 focus:ring-[#ff64ba] outline-none rounded-md">
                </div>
                <div class="mb-2">
                    <label class="text-sm font-medium">E-mailadres</label>
                    <input type="email" name="email" value="{{ old('email', $gegevens['email'] ?? '') }}" required class="w-full px-[0.75rem] py-[0.55rem] ring-1 ring-gray-200 focus:ring-[#ff64ba] outline-none rounded-md">
                </div>
                <div class="mb-2">
                    <label class="text-sm font-medium">Adres</label>
                    <input type="text" name="adres" value="{{ old('adres', $gegevens['adres'] ?? '') }}" required class="w-full px-[0.75rem] py-[0.55rem] ring-1 ring-gray-200 focus:ring-[#ff64ba] outline-none rounded-md">
                </div>
                <div class="flex gap-4 mb-2">
                    <div class="w-1/2">
                        <label class="text-sm font-medium">Postcode</label>
                        <input type="text" name="postcode" value="{{ old('postcode', $gegevens['postcode'] ?? '') }}" required class="w-full px-[0.75rem] py-[0.55rem] ring-1 ring-gray-200 focus:ring-[#ff64ba] outline-none rounded-md">
                    </div>
                    <div class="w-1/2">
                        <label class="text-sm font-medium">Plaats</label>
                        <input type="text" name="plaats" value="{{ old('plaats', $gegevens['plaats'] ?? '') }}" required class="w-full px-[0.75rem] py-[0.55rem] ring-1 ring-gray-200 focus:ring-[#ff64ba] outline-none rounded-md">
                    </div>
                </div>
            </div>
            <div class="w-1/3 bg-white p-[1.5rem] rounded-lg flex flex-col justify-between h-fit">
                <div>
                    <h2 class="text-[18px] font-semibold mb-4">Overzicht</h2>
                    @php
                        $gratisVerzendingDrempel = 75;

                        $totaalIncl = collect($cart)->sum(fn($i) => $i['prijs'] * $i['aantal']); // bijv €120
                        $totaalExcl = $totaalIncl / 1.21;                                         // €99.17
                        $btw = $totaalIncl - $totaalExcl;  
                        
                        $subtotaal = $totaalIncl - $btw;

                        $verzendkosten = $totaalIncl >= $gratisVerzendingDrempel ? 0 : 4.90;
                        $totaalMetVerzending = $totaalIncl + $verzendkosten;

                        $nogTeGaan = max(0, $gratisVerzendingDrempel - $totaalIncl);
                        $voortgang = min(100, round(($totaalIncl / $gratisVerzendingDrempel) * 100));
                    @endphp
                    <div class="flex justify-between mb-2 text-[15px]">
                        <span>Subtotaal</span>
                        <span>&euro;{{ number_format($subtotaal, 2, ',', '.') }}</span>
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
                </div>
                <button type="submit" class="cursor-pointer mt-5 w-full py-3 bg-black text-white rounded-md text-[15px] font-medium hover:bg-gray-900 transition">
                    Naar besteloverzicht
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
