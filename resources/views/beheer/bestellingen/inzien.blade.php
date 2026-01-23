@extends('layouts.beheer')
@section('content')
@php
    $levermethode = $bestelling->levermethode ?? 'verzenden';
@endphp
<div class="w-full h-auto">
    <div class="max-w-[1100px] mx-auto py-[1.5rem]">
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
        <div class="relative">
            <a href="/beheer/producten" class="text-[#191919] opacity-50 text-[12px] hover:underline">
                Terug naar het overzicht
            </a>
            <h1 class="text-[#191919] text-[38px] font-semibold leading-[1.15] my-2">Bestelling <i class="instrument-serif-font text-[#b38867]">inzien</i></h1>
            <p class="text-[#191919] opacity-80 text-[15px] mb-8">
                Beheer hier de bestelling met transactie id <strong>{{ $bestelling->transactie_id }}</strong>.<br>
                Geef een track & trace code op en de status van de bestelling zal veranderen naar "Onderweg".<br><br>

                <strong>Levermethode:</strong>
                {{ $levermethode === 'ophalen' ? 'Ophalen' : 'Verzenden' }}
            </p>
            @if(session('success'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 2000)"
                    x-show="show"
                    x-transition
                    class="bg-green-100 text-sm text-green-500 border border-green-500 px-2 py-1 rounded-md relative w-fit mb-4"
                >
                    {{ session('success') }}
                </div>
            @endif
            <div class="absolute right-0 top-1/2 -translate-y-1/2">
                @if($bestelling->status === 'open')
                    <p class="px-2 py-1 rounded-sm border-1 border-[#b38867] bg-[#b3886725] text-[#b38867] text-sm w-fit">Nieuw!</p>
                @elseif($bestelling->status === 'onderweg')
                    <p class="px-2 py-1 rounded-sm border-1 border-orange-500 bg-orange-100 text-orange-500 text-sm w-fit">Onderweg</p>
                @elseif($bestelling->status === 'opgehaald')
                    <p class="px-2 py-1 rounded-sm border-1 border-blue-500 bg-blue-100 text-blue-500 text-sm w-fit">Opgehaald</p>
                @elseif($bestelling->status === 'afgerond')
                    <p class="px-2 py-1 rounded-sm border-1 border-green-500 bg-green-100 text-green-500 text-sm w-fit">Afgerond</p>
                @endif
            </div>
        </div>
        <div class="w-full bg-white p-[1.5rem] rounded-lg mb-4">
            <h2 class="text-[#191919] text-[24px] font-semibold leading-[1.15] mb-2">Verzendgegevens</h2>
            <form method="POST" action="{{ route('bestellingen.verzendgegevens', $bestelling) }}" class="w-full space-y-6 flex flex-col items-end">
                @csrf
                @method('PUT')
                <div class="w-full flex gap-6">
                    <div class="flex flex-col w-1/2">
                        <label for="naam" class="text-sm font-medium">Naam</label>
                        <input type="text" name="naam" required value="{{ $bestelling->naam }}" class="w-full px-[0.75rem] py-[0.55rem] ring-1 ring-gray-200 focus:ring-[#b38867] outline-none rounded-md">
                    </div>
                    <div class="flex flex-col w-1/2">
                        <label for="email" class="text-sm font-medium">E-mail</label>
                        <input type="email" name="email" required value="{{ $bestelling->email }}" class="w-full px-[0.75rem] py-[0.55rem] ring-1 ring-gray-200 focus:ring-[#b38867] outline-none rounded-md">
                    </div>
                </div>
                {{-- Levermethode zichtbaar (read-only) --}}
                <div class="w-full">
                    <label class="text-sm font-medium">Levermethode</label>
                    <div class="w-full px-[0.75rem] py-[0.55rem] ring-1 ring-gray-200 rounded-md bg-gray-50 text-[15px]">
                        {{ $levermethode === 'ophalen' ? 'Ophalen' : 'Verzenden' }}
                    </div>
                </div>
                @if($levermethode === 'verzenden')
                    <div class="w-full flex gap-6">
                        <div class="flex flex-col w-1/3">
                            <label for="adres" class="text-sm font-medium">Adres</label>
                            <input
                                type="text"
                                name="adres"
                                required
                                value="{{ $bestelling->adres }}"
                                class="w-full px-[0.75rem] py-[0.55rem] ring-1 ring-gray-200 focus:ring-[#b38867] outline-none rounded-md"
                            >
                        </div>
                        <div class="flex flex-col w-1/3">
                            <label for="postcode" class="text-sm font-medium">Postcode</label>
                            <input
                                type="text"
                                name="postcode"
                                required
                                value="{{ $bestelling->postcode }}"
                                class="w-full px-[0.75rem] py-[0.55rem] ring-1 ring-gray-200 focus:ring-[#b38867] outline-none rounded-md"
                            >
                        </div>
                        <div class="flex flex-col w-1/3">
                            <label for="plaats" class="text-sm font-medium">Plaats</label>
                            <input
                                type="text"
                                name="plaats"
                                required
                                value="{{ $bestelling->plaats }}"
                                class="w-full px-[0.75rem] py-[0.55rem] ring-1 ring-gray-200 focus:ring-[#b38867] outline-none rounded-md"
                            >
                        </div>
                    </div>
                @else
                    <div class="w-full">
                        <div class="text-[14px] text-[#191919] opacity-70 bg-gray-50 ring-1 ring-gray-200 rounded-md px-3 py-2">
                            Deze bestelling wordt opgehaald. Geen adresgegevens nodig.
                        </div>
                    </div>
                @endif
                <button type="submit" class="cursor-pointer w-fit px-[1.5rem] py-[0.55rem] bg-[#b38867] hover:bg-[#96366c] transition rounded-md text-white text-[15px] font-medium">Opslaan</button>
            </form>
        </div>
        <div class="w-full bg-white p-[1.5rem] rounded-lg mb-4">
            <h2 class="text-[#191919] text-[24px] font-semibold leading-[1.15] mb-2">Besteloverzicht</h2>
            <ul class="divide-y divide-gray-200 text-[15px] text-[#191919] opacity-90 p-[1.5rem] border-1 border-gray-200 rounded-lg bg-gray-100">
                @foreach($bestelling->producten as $item)
                    <li class="py-3 grid grid-cols-3 items-center gap-4">
                        <div class="col-span-1 flex items-center gap-4">
                            <span class="font-medium leading-tight">{{ $item->naam }}</span>
                        </div>
                        <div class="col-span-1 text-center">
                            {{ $item->pivot->aantal }}x
                        </div>
                        <div class="col-span-1 text-right font-medium">
                            &euro;{{ number_format($item->prijs * $item->pivot->aantal, 2, ',', '.') }}
                        </div>
                    </li>
                @endforeach
                <div class="w-full flex items-center justify-end mt-4 text-[#b38867]">
                    <span class="text-[15px] font-medium">Totaal: &euro;{{ number_format($bestelling->totaalprijs, 2, ',', '.') }}</span>
                </div>
            </ul>

            <div class="mt-4 flex justify-end">
                <a href="{{ route('bestellingen.factuur.download', $bestelling) }}"
                   class="inline-flex items-center gap-2 px-[1.5rem] py-[0.55rem] bg-[#191919] hover:bg-[#b38867] transition rounded-md text-white text-[15px] font-medium cursor-pointer">
                    Factuur downloaden (PDF)
                </a>
            </div>
        </div>
        <div class="w-full bg-white p-[1.5rem] rounded-lg mb-4">
            <h2 class="text-[#191919] text-[24px] font-semibold leading-[1.15] mb-2">Status</h2>

            <form method="POST" action="{{ route('bestellingen.status', $bestelling) }}" class="w-full flex gap-4 items-end">
                @csrf
                @method('PUT')

                <div class="flex flex-col w-full">
                    <label for="status" class="text-sm font-medium">Kies status</label>
                    <select name="status" class="w-full px-4 py-2 ring-1 ring-gray-200 rounded-md focus:ring-[#b38867] outline-none">
                        <option value="open" {{ $bestelling->status === 'open' ? 'selected' : '' }}>Nieuw</option>
                        <option value="onderweg" {{ $bestelling->status === 'onderweg' ? 'selected' : '' }}>Onderweg</option>
                        <option value="opgehaald" {{ $bestelling->status === 'opgehaald' ? 'selected' : '' }}>Opgehaald</option>
                        <option value="afgerond" {{ $bestelling->status === 'afgerond' ? 'selected' : '' }}>Afgerond</option>
                    </select>
                    @error('status')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="cursor-pointer w-fit px-[1.5rem] py-[0.55rem] bg-[#b38867] hover:bg-[#96366c] transition rounded-md text-white text-[15px] font-medium">
                    Opslaan
                </button>
            </form>
        </div>
        <div class="w-full bg-white p-[1.5rem] rounded-lg">
            <h2 class="text-[#191919] text-[24px] font-semibold leading-[1.15] mb-2">Track & Trace</h2>
            <form method="POST" action="{{ route('bestellingen.tracktrace', $bestelling) }}" class="w-full flex gap-4 items-end">
                @csrf
                @method('PUT')
                <div class="flex flex-col w-full">
                    <label for="track_trace" class="text-sm font-medium">Link</label>
                    <input type="text" name="track_trace" required value="{{ $bestelling->track_trace }}" class="w-full px-4 py-2 ring-1 ring-gray-200 rounded-md focus:ring-[#b38867] outline-none">
                </div>
                <button type="submit" class="cursor-pointer w-fit px-[1.5rem] py-[0.55rem] bg-[#b38867] hover:bg-[#96366c] transition rounded-md text-white text-[15px] font-medium">Opslaan</button>
            </form>
        </div>
    </div>
</div>
@endsection