{{-- resources/views/beheer/producten/index.blade.php --}}
@extends('layouts.beheer')
@section('content')

@php
  use Illuminate\Support\Str;

  /**
   * Bouw altijd een geldige publieke URL voor productfoto’s.
   * Ondersteunt:
   * - "abc.jpg" (alleen bestandsnaam) -> /storage/producten/abc.jpg
   * - "producten/abc.jpg" (pad)       -> /storage/producten/abc.jpg
   * - "/storage/..." of "storage/..."  -> asset(...)
   * - "http(s)://..."                 -> 그대로
   */
  $fotoUrl = function ($path) {
      if (!$path) return null;

      $path = (string) $path;

      if (Str::startsWith($path, ['http://', 'https://'])) {
          return $path;
      }

      // Als iemand al "storage/..." of "/storage/..." opslaat
      if (Str::startsWith($path, ['/storage/', 'storage/'])) {
          return asset(ltrim($path, '/'));
      }

      // Als er al een map in zit (bv. "producten/xxx.jpg")
      if (Str::contains($path, '/')) {
          // Laravel public disk + storage:link -> /storage/{path}
          return asset('storage/' . ltrim($path, '/'));
      }

      // Alleen bestandsnaam -> standaard map "producten"
      return asset('storage/producten/' . ltrim($path, '/'));
  };
@endphp

<div class="w-full h-auto">
    <div class="py-[1.5rem] max-w-[1100px] mx-auto">
        <div class="w-full flex items-center justify-between mb-6">
            <ul class="flex items-center gap-[2rem]">
                <li><a href="/beheer" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Dashboard</a></li>
                <li><a href="/beheer/producten" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition text-[#b38867]">Producten</a></li>
                <li><a href="/beheer/bestellingen" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Bestellingen</a></li>
                <li><a href="/beheer/voorraad" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Voorraad</a></li>
                <li><a href="/beheer/instellingen" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Instellingen</a></li>
            </ul>
            <form method="POST" action="{{ route('uitloggen') }}">
                @csrf
                <button type="submit" class="px-[1.5rem] py-[0.4rem] bg-gray-200 hover:bg-gray-300 text-gray-500 transition rounded-md text-[15px] font-medium cursor-pointer">
                    Uitloggen
                </button>
            </form>
        </div>

        <h1 class="text-[#191919] text-[38px] font-semibold leading-[1.15] mb-2">
            Beheer hier <i class="instrument-serif-font text-[#b38867]">jouw producten</i>
        </h1>
        <p class="text-[#191919] opacity-80 text-[15px] mb-8">
            Hier beheer je alle producten in je webshop. Voeg nieuwe kleuren toe of<br>
            bewerk bestaande producten. Zorg ervoor dat je aanbod up-to-date blijft voor je klanten.
        </p>

        <div class="w-full bg-white p-[1.5rem] rounded-lg">
            <div class="mb-2 flex items-center justify-between">
                <h2 class="text-[#191919] text-[24px] font-medium leading-[1.15]">Producten</h2>
                <a href="{{ route('producten.aanmaken') }}"
                   class="px-[0.9rem] py-[0.5rem] bg-[#b38867] hover:bg-[#e652a7] text-white rounded-md text-[15px] font-medium transition">
                    +
                </a>
            </div>

            <table class="w-full border border-gray-200 rounded-lg text-[15px]">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="text-left px-4 py-4 font-normal">Foto</th>
                        <th class="text-left px-4 py-4 font-normal">Naam</th>
                        <th class="text-left px-4 py-4 font-normal">Prijs</th>
                        <th class="text-left px-4 py-4 font-normal">Voorraad</th>
                        <th class="text-right px-4 py-4 font-normal">Acties</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($producten as $product)
                        <tr class="border-t border-gray-100">
                            <td class="px-4 py-4">
                                @php $src = $fotoUrl($product->foto); @endphp

                                @if($src)
                                    <img
                                        src="{{ $src }}"
                                        alt="{{ $product->naam }}"
                                        class="w-12 h-12 object-cover rounded"
                                        loading="lazy"
                                    >
                                @else
                                    -
                                @endif
                            </td>

                            <td class="px-4 py-4">{{ $product->naam }}</td>
                            <td class="px-4 py-4">€{{ number_format($product->prijs, 2, ',', '.') }}</td>
                            <td class="px-4 py-4">{{ $product->voorraad }}</td>

                            <td class="px-4 py-4 text-right align-middle">
                                <div class="flex justify-end items-center gap-[1rem]">
                                    {{-- Oogje: toggle zichtbaar/verborgen --}}
                                    <form action="{{ route('producten.toggleVisibility', $product) }}" method="POST"
                                          onsubmit="return confirm('Zichtbaarheid van dit product wijzigen?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-gray-500 hover:text-[#b38867]"
                                                title="{{ $product->is_visible ? 'Verbergen' : 'Zichtbaar maken' }}">
                                            @if($product->is_visible)
                                                <p class="text-gray-500 hover:underline">Verbergen</p>
                                            @else
                                                <p class="text-gray-500 hover:underline">Zichtbaar maken</p>
                                            @endif
                                        </button>
                                    </form>

                                    <a href="{{ route('producten.bewerken', $product) }}" class="text-orange-500 hover:underline">
                                        Bewerken
                                    </a>

                                    <form action="{{ route('producten.verwijderen', $product) }}" method="POST"
                                          onsubmit="return confirm('Weet je zeker dat je dit product wilt verwijderen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-gray-500 hover:underline cursor-pointer" type="submit">
                                            Verwijderen
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-4" colspan="5">Nog geen producten toegevoegd.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $producten->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
