@extends('layouts.pages')

@section('title', ($product->naam ?? 'Product') . ' - Product')

@section('content')
@php
  use Illuminate\Support\Str;
  use Illuminate\Support\Facades\Storage;

  $slug = Str::slug($product->naam);
  $inStock = (int) ($product->voorraad ?? 0) > 0;
  $prijsHuman = isset($product->prijs) ? number_format((float)$product->prijs, 2, ',', '.') : '0,00';

  // Foto URL fix (werkt bij: 'producten/xxx.jpg', 'xxx.jpg', of volledige URL)
  $foto = $product->foto ?? null;
  if (!empty($foto) && Str::startsWith($foto, ['http://', 'https://'])) {
    $imageUrl = $foto;
  } elseif (!empty($foto)) {
    $path = Str::startsWith($foto, 'producten/') ? $foto : 'producten/' . ltrim($foto, '/');
    $imageUrl = Storage::disk('public')->url($path);
  } else {
    $imageUrl = null;
  }

  $canonicalUrl = route('producten.show', ['product' => $product->id, 'slug' => $slug]);
@endphp

{{-- Hero --}}
<div class="p-2 h-[350px] md:h-auto">
  <div class="w-full h-full md:h-[350px] overflow-y-hidden rounded-3xl flex items-end relative bg-cover bg-center bg-[url(https://i.imgur.com/99DomHP.jpeg)]">
    <div class="w-full h-full absolute z-[1] bg-[#00000050]"></div>
    <div class="absolute z-[3] max-w-[1100px] px-[1rem] md:px-[3rem] left-0 right-0 ml-auto mr-auto pb-8">
      <h1 class="text-white text-[34px] md:text-[50px] font-bold leading-[1.15] text-center md:text-start">
        {{ $product->naam }}
      </h1>
    </div>
  </div>
</div>

{{-- Main --}}
<div class="w-full">
  <div class="max-w-[1100px] px-[1rem] md:px-[3rem] mx-auto py-[3.5rem] grid grid-cols-12 gap-8">
    <a href="{{ route('producten.index') }}" class="col-span-12 text-xs opacity-50 hover:underline">Terug naar alle producten</a>

    {{-- Afbeelding --}}
    <div class="col-span-12 md:col-span-5">
      <div class="w-full aspect-square overflow-hidden rounded-lg p-[3rem] bg-white border-1 border-gray-100">
        @if($imageUrl)
          <img src="{{ $imageUrl }}" alt="{{ $product->naam }}" class="w-full h-full object-cover">
        @else
          <div class="w-full h-full grid place-items-center text-xs text-gray-400">Geen afbeelding</div>
        @endif
      </div>
    </div>

    {{-- Inhoud + koopblok --}}
    <div class="col-span-12 md:col-span-7">
      <div>
        <h2 class="text-xl font-semibold text-[#191919]">{{ $product->naam }}</h2>

        <div class="prose prose-sm md:prose max-w-none text-[#191919] opacity-80 mt-2 leading-relaxed">
          {!! $product->beschrijving !!}
        </div>

        <div class="mt-6 flex flex-col">
          <div class="mb-6">
            <p class="text-sm text-gray-500">Prijs</p>
            <p class="text-2xl font-semibold">€{{ $prijsHuman }}</p>
          </div>

          <form action="{{ route('winkelwagen.toevoegen', $product) }}"
                method="POST"
                id="add-to-cart-form"
                class="flex items-center gap-3">
            @csrf

            @if(!$inStock)
              <div class="cursor-not-allowed select-none px-4 py-[0.6rem] bg-[#b38867] opacity-25 rounded-md text-white text-[15px] font-medium">
                Uitverkocht
              </div>
            @else
              <button type="submit"
                      class="px-5 py-[0.6rem] bg-[#b38867] hover:bg-[#947054] transition rounded-md text-white text-[15px] font-medium">
                In winkelwagen
              </button>
            @endif

            @if(!$inStock)
              <div class="inline-flex items-center gap-2 text-red-600 text-sm font-medium">
                <span class="w-2 h-2 rounded-full bg-red-600 inline-block"></span> Uitverkocht
              </div>
            @else
              <div class="inline-flex items-center gap-2 text-emerald-600 text-sm font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-600 inline-block"></span> Op voorraad
              </div>
            @endif
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- Overlay: toegevoegd aan winkelwagen --}}
  <div id="overlay" class="fixed z-50 flex items-center justify-center bottom-4 right-4 hidden opacity-0 translate-y-4 transition-all duration-500">
    <div class="bg-white p-8 rounded-lg w-[350px] border-1 border-gray-200 shadow-lg">
      <h2 class="text-[#191919] text-[22px] font-semibold leading-[1.15] mb-4">
        Product is toegevoegd <br>aan <i class="instrument-serif-font text-[#b38867]">jouw winkelwagen</i>
      </h2>
      <div class="flex justify-between gap-4">
        <a href="{{ route('producten.index') }}" class="flex-1 py-2 bg-gray-200 hover:bg-gray-300 text-center rounded text-sm">Verder winkelen</a>
        <a href="{{ route('winkelwagen.index') }}" class="flex-1 py-2 bg-[#b38867] hover:bg-[#947054] text-white text-center rounded text-sm">Afrekenen</a>
      </div>
    </div>
  </div>
</div>
@endsection

@push('head')
  {{-- Canonical --}}
  <link rel="canonical" href="{{ $canonicalUrl }}" />

  {{-- JSON-LD Product schema --}}
  <script type="application/ld+json">
  {!! json_encode([
      '@context' => 'https://schema.org',
      '@type' => 'Product',
      'name' => $product->naam,
      'image' => $imageUrl ?: null,
      'description' => trim(strip_tags($product->beschrijving ?? '')),
      'sku' => (string) $product->id,
      'offers' => [
          '@type' => 'Offer',
          'price' => number_format((float)$product->prijs, 2, '.', ''),
          'priceCurrency' => 'EUR',
          'availability' => $inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
          'url' => $canonicalUrl,
      ],
  ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
  </script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('add-to-cart-form');
  const overlay = document.getElementById('overlay');

  if (!form || !overlay) return;

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const action = this.getAttribute('action');
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = tokenMeta ? tokenMeta.getAttribute('content') : '';

    fetch(action, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf },
      body: formData
    }).then(resp => {
      if (!resp.ok) throw new Error('Netwerkfout');

      overlay.classList.remove('hidden');
      overlay.classList.add('opacity-0', 'translate-y-4');
      setTimeout(() => overlay.classList.remove('opacity-0', 'translate-y-4'), 10);

      setTimeout(() => {
        overlay.classList.add('opacity-0', 'translate-y-4');
        setTimeout(() => overlay.classList.add('hidden'), 500);
      }, 3000);

    }).catch(() => {
      alert('Er ging iets mis bij het toevoegen.');
    });
  });
});
</script>
@endpush
