{{-- resources/views/beheer/facturen/index.blade.php --}}
@extends('layouts.beheer')

@section('content')
<div class="w-full h-auto">
  <div class="py-[1.5rem] max-w-[1100px] mx-auto">

    {{-- Topnav --}}
    <div class="w-full flex items-center justify-between mb-6">
      <ul class="flex items-center gap-[2rem]">
        <li><a href="/beheer" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Dashboard</a></li>
        <li><a href="/beheer/producten" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Producten</a></li>
        <li><a href="/beheer/bestellingen" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Bestellingen</a></li>
        <li><a href="/beheer/voorraad" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Voorraad</a></li>
        <li><a href="/beheer/facturen" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition text-[#b38867]">Facturen</a></li>
        <li><a href="/beheer/instellingen" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Instellingen</a></li>
      </ul>

      <form method="POST" action="{{ route('uitloggen') }}">
        @csrf
        <button type="submit" class="px-[1.5rem] py-[0.4rem] bg-gray-200 hover:bg-gray-300 text-gray-500 transition rounded-md text-[15px] font-medium cursor-pointer">
          Uitloggen
        </button>
      </form>
    </div>

    {{-- Header --}}
    <h1 class="text-[#191919] text-[38px] font-semibold leading-[1.15] mb-2">
      Beheer hier <i class="instrument-serif-font text-[#b38867]">jouw facturen</i>
    </h1>
    <p class="text-[#191919] opacity-80 text-[15px] mb-8">
      Hier beheer je handmatig aangemaakte facturen. Voeg nieuwe facturen toe en download direct de PDF.
    </p>

    {{-- Card --}}
    <div class="w-full bg-white p-[1.5rem] rounded-lg">
      <div class="mb-2 flex items-center justify-between">
        <h2 class="text-[#191919] text-[24px] font-medium leading-[1.15]">Facturen</h2>

        <a href="{{ route('facturen.aanmaken') }}"
           class="px-[0.9rem] py-[0.5rem] bg-[#b38867] hover:bg-[#e652a7] text-white rounded-md text-[15px] font-medium transition">
          +
        </a>
      </div>

      <table class="w-full border border-gray-200 rounded-lg text-[15px]">
        <thead>
          <tr class="bg-gray-100">
            <th class="text-left px-4 py-4 font-normal">Factuurnummer</th>
            <th class="text-left px-4 py-4 font-normal">Datum</th>
            <th class="text-left px-4 py-4 font-normal">Klant</th>
            <th class="text-left px-4 py-4 font-normal">E-mail</th>
            <th class="text-right px-4 py-4 font-normal">Totaal (incl)</th>
            <th class="text-right px-4 py-4 font-normal">Acties</th>
          </tr>
        </thead>

        <tbody>
          @forelse($facturen as $factuur)
            <tr class="border-t border-gray-100">
              <td class="px-4 py-4">
                <span class="font-medium text-[#191919]">
                  {{ $factuur->factuurnummer ?? ('#' . $factuur->id) }}
                </span>
              </td>

              <td class="px-4 py-4">
                @if(!empty($factuur->datum))
                  {{ \Carbon\Carbon::parse($factuur->datum)->format('d-m-Y') }}
                @else
                  -
                @endif
              </td>

              <td class="px-4 py-4">{{ $factuur->naam ?? '-' }}</td>
              <td class="px-4 py-4">{{ $factuur->email ?? '-' }}</td>

              <td class="px-4 py-4 text-right">
                @php $totaal = (float)($factuur->totaal_incl ?? 0); @endphp
                €{{ number_format($totaal, 2, ',', '.') }}
              </td>

              <td class="px-4 py-4 text-right align-middle">
              <div class="flex justify-end items-center gap-[1rem]">
                <a href="{{ route('facturen.factuur.download', $factuur) }}"
                  class="text-gray-500 hover:underline">
                  PDF
                </a>

                <form method="POST" action="{{ route('facturen.verwijderen', $factuur) }}"
                      onsubmit="return confirm('Weet je zeker dat je deze factuur wilt verwijderen?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="text-red-600 hover:underline cursor-pointer">
                    Verwijderen
                  </button>
                </form>
              </div>
              </td>
            </tr>
          @empty
            <tr>
              <td class="px-4 py-4" colspan="6">Nog geen facturen aangemaakt.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>
</div>
@endsection
