@extends('layouts.pages')

@section('content')
<div class="w-full h-auto">
    <div class="py-[5rem] max-w-[1100px] mx-auto">
        <div class="w-full flex items-center justify-between mb-6">
            <ul class="flex items-center gap-[2rem]">
                <li><a href="/beheer" class="hover:text-[#ff64ba] text-[15px] font-medium">Dashboard</a></li>
                <li><a href="/beheer/producten" class="hover:text-[#ff64ba] text-[15px] font-medium">Producten</a></li>
                <li><a href="/beheer/bestellingen" class="hover:text-[#ff64ba] text-[15px] font-medium">Bestellingen</a></li>
                <li><a href="/beheer/voorraad" class="hover:text-[#ff64ba] text-[15px] font-medium text-[#ff64ba]">Voorraad</a></li>
                <li><a href="/beheer/instellingen" class="hover:text-[#ff64ba] text-[15px] font-medium">Instellingen</a></li>
            </ul>
            <form method="POST" action="{{ route('uitloggen') }}">
                @csrf
                <button type="submit" class="px-[1.5rem] py-[0.4rem] bg-gray-200 hover:bg-gray-300 text-gray-500 transition rounded-md text-[15px] font-medium cursor-pointer">Uitloggen</button>
            </form>
        </div>
        <h1 class="text-[#191919] text-[38px] font-semibold leading-[1.15] mb-2">Beheer hier <i class="instrument-serif-font text-[#ff64ba]">jouw voorraad</i></h1>
        <p class="text-[#191919] opacity-80 text-[15px] mb-8">
            Pas de voorraad direct aan per product.<br>Wijzig het aantal en druk op <strong>Opslaan</strong> om de wijzigingen op te slaan.
        </p>
        <div class="w-full bg-white p-[1.5rem] rounded-lg">
            <h2 class="text-[#191919] text-[24px] font-medium leading-[1.15] mb-2">Producten</h2>
            <form action="{{ route('beheer.voorraad.bijwerken') }}" method="POST">
                @csrf
                <table class="w-full border border-gray-200 rounded-lg text-[15px]">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="text-left px-4 py-4 font-normal">Foto</th>
                            <th class="text-left px-4 py-4 font-normal">Naam</th>
                            <th class="text-left px-4 py-4 font-normal">Voorraad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($producten as $product)
                            <tr class="border-t border-gray-100">
                                <td class="px-4 py-4">
                                    @if($product->foto)
                                        <img src="{{ asset('storage/producten/' . $product->foto) }}" class="w-12 h-12 object-cover rounded">
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-4">{{ $product->naam }}</td>
                                <td class="px-4 py-4">
                                    <input type="number"
                                           name="voorraad[{{ $product->id }}]"
                                           value="{{ $product->voorraad }}"
                                           min="0"
                                           class="w-[200px] border ring-1 ring-gray-300 focus:ring-[#ff64ba] transition rounded px-2 py-1 text-start outline-none border-none">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-4" colspan="3">Nog geen producten beschikbaar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-2 text-right">
                    <button type="submit" class="px-6 py-2 bg-[#ff64ba] text-white rounded-md hover:bg-[#e652a7] transition">
                        Wijzigingen opslaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
