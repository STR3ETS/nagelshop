@extends('layouts.beheer')
@section('content')
<div class="w-full h-auto">
    <div class="py-[1.5rem] max-w-[1100px] mx-auto">
        <div class="w-full flex items-center justify-between mb-6">
            <ul class="flex items-center gap-[2rem]">
                <li><a href="/beheer" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Dashboard</a></li>
                <li><a href="/beheer/producten" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Producten</a></li>
                <li><a href="/beheer/bestellingen" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition text-[#b38867]">Bestellingen</a></li>
                <li><a href="/beheer/voorraad" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Voorraad</a></li>
                <li><a href="/beheer/facturen" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Facturen</a></li>
                <li><a href="/beheer/instellingen" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Instellingen</a></li>
            </ul>
            <form method="POST" action="{{ route('uitloggen') }}">
                @csrf
                <button type="submit" class="px-[1.5rem] py-[0.4rem] bg-gray-200 hover:bg-gray-300 text-gray-500 transition rounded-md text-[15px] font-medium cursor-pointer">Uitloggen</button>
            </form>
        </div>
        <h1 class="text-[#191919] text-[38px] font-semibold leading-[1.15] mb-2">Beheer hier <i class="instrument-serif-font text-[#b38867]">jouw bestellingen</i></h1>
        <p class="text-[#191919] opacity-80 text-[15px] mb-8">
        Hier beheer je alle bestellingen in je webshop. Bekijk recente aankopen,<br>verwerk openstaande orders en houd overzicht op je verkoopgeschiedenis. Zorg ervoor dat je klanten tijdig worden geholpen.
        </p>
        <div class="w-full bg-white p-[1.5rem] rounded-lg">
            <div class="mb-2 flex items-center justify-between">
                <h2 class="text-[#191919] text-[24px] font-medium leading-[1.15]">Bestellingen</h2>
            </div>
            <table class="w-full border border-gray-200 rounded-lg text-[15px]">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="text-left px-4 py-4 font-normal">Transactie ID</th>
                        <th class="text-left px-4 py-4 font-normal">Totaalprijs</th>
                        <th class="text-left px-4 py-4 font-normal">Besteld op</th>
                        <th class="text-left px-4 py-4 font-normal">Status</th>
                        <th class="text-right px-4 py-4 font-normal">Acties</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bestellingen as $bestelling)
                        <tr class="border-t border-gray-100">
                            <td class="px-4 py-4">{{ $bestelling->transactie_id }}</td>
                            <td class="px-4 py-4">€{{ number_format($bestelling->totaalprijs, 2, ',', '.') }}</td>
                            <td class="px-4 py-4">{{ $bestelling->created_at }}</td>
                            <td class="px-4 py-4">
                                @php
                                    $status = $bestelling->status ?? 'open';
                                @endphp
                                @if($status === 'open')
                                    <p class="px-2 py-1 rounded-sm border-1 border-[#b38867] bg-[#b3886725] text-[#b38867] text-sm w-fit">
                                        Nieuw!
                                    </p>
                                @elseif($status === 'onderweg')
                                    <p class="px-2 py-1 rounded-sm border-1 border-orange-500 bg-orange-100 text-orange-500 text-sm w-fit">
                                        Onderweg
                                    </p>
                                @elseif($status === 'opgehaald')
                                    <p class="px-2 py-1 rounded-sm border-1 border-blue-500 bg-blue-100 text-blue-500 text-sm w-fit">
                                        Opgehaald
                                    </p>
                                @elseif($status === 'afgerond')
                                    <p class="px-2 py-1 rounded-sm border-1 border-green-500 bg-green-100 text-green-500 text-sm w-fit">
                                        Afgerond
                                    </p>
                                @else
                                    <p class="px-2 py-1 rounded-sm border-1 border-gray-300 bg-gray-100 text-gray-500 text-sm w-fit">
                                        Onbekend
                                    </p>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right align-middle">
                                <div class="flex justify-end items-center gap-[1rem]">
                                    <a href="{{ route('bestellingen.inzien', $bestelling) }}" class="text-orange-500 hover:underline flex items-center jusitfy-center">
                                        <lord-icon
                                            src="https://cdn.lordicon.com/srgccmsj.json"
                                            trigger="hover"
                                            colors="primary:#b4b4b4"
                                            style="width:20px;height:20px">
                                        </lord-icon>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-2" colspan="4">Nog geen bestellingen.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection