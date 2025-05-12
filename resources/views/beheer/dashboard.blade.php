@extends('layouts.pages')
@section('content')
<div class="w-full h-auto">
    <div class="py-[5rem] max-w-[1100px] mx-auto">
        <div class="w-full flex items-center justify-between mb-6">
            <ul class="flex items-center gap-[2rem]">
                <li><a href="/beheer" class="hover:text-[#ff64ba] text-[15px] font-medium rounded-sm transition">Dashboard</a></li>
                <li><a href="/beheer/producten" class="hover:text-[#ff64ba] text-[15px] font-medium rounded-sm transition">Producten</a></li>
                <li><a href="/beheer/bestellingen" class="hover:text-[#ff64ba] text-[15px] font-medium rounded-sm transition">Bestellingen</a></li>
                <li><a href="/beheer/voorraad" class="hover:text-[#ff64ba] text-[15px] font-medium rounded-sm transition">Voorraad</a></li>
                <li><a href="/beheer/instellingen" class="hover:text-[#ff64ba] text-[15px] font-medium rounded-sm transition">Instellingen</a></li>
            </ul>
            <form method="POST" action="{{ route('uitloggen') }}">
                @csrf
                <button type="submit" class="px-[1.5rem] py-[0.4rem] bg-gray-200 hover:bg-gray-300 text-gray-500 transition rounded-md text-[15px] font-medium cursor-pointer">Uitloggen</button>
            </form>
        </div>
        <h1 class="text-[#191919] text-[38px] font-semibold leading-[1.15] mb-2">Welkom in <i class="instrument-serif-font text-[#ff64ba]">jouw beheerpaneel</i></h1>
        <p class="text-[#191919] opacity-80 text-[15px] mb-8">
            💸 Je hebt deze maand <strong>142 producten</strong> verkocht.<br>📦 Er staan nog <strong>8 openstaande bestellingen</strong> klaar.
        </p>
        <div class="w-full bg-white p-[1.5rem] rounded-lg">
            <h2 class="text-[#191919] text-[24px] font-medium leading-[1.15]">Snelle acties</h2>
            <div class="mt-2">
                <a href="#" class="px-[1.5rem] py-[0.4rem] bg-[#191919] hover:bg-[#353535] transition rounded-md text-white text-[15px] font-medium">Product toevoegen</a>
                <a href="#" class="px-[1.5rem] py-[0.4rem] bg-[#191919] hover:bg-[#353535] transition rounded-md text-white text-[15px] font-medium">Besteloverzicht</a>
                <a href="#" class="px-[1.5rem] py-[0.4rem] bg-[#191919] hover:bg-[#353535] transition rounded-md text-white text-[15px] font-medium">Voorraad beheren</a>
            </div>
        </div>
    </div>
</div>
@endsection