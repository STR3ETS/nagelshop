@extends('layouts.pages')
@section('content')

@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;

    // Foto helper: ondersteunt zowel "producten/abc.jpg" als "abc.jpg"
    $fotoUrl = function ($foto) {
        if (empty($foto)) return null;

        if (Str::startsWith($foto, ['http://','https://','//'])) return $foto;

        if (Str::contains($foto, '/')) return Storage::disk('public')->url($foto);

        return asset('storage/producten/' . ltrim($foto, '/'));
    };

    // Bestsellers: werkt met Collection én met Paginator
    $bestsellers = $producten instanceof \Illuminate\Contracts\Pagination\Paginator
        ? $producten->getCollection()->take(4)
        : collect($producten)->take(4);
@endphp

<div class="p-2 h-[680px] md:h-auto">
    <div class="w-full h-full md:h-[600px] overflow-y-hidden flex items-start md:items-center relative rounded-3xl">
        <!-- <video class="w-full h-full absolute z-1 object-cover" src="assets/home-hero.mp4" autoplay muted loop></video> -->
        <div class="w-full h-full absolute z-1 bg-cover bg-center hidden md:block" style="background-image: url('/images/hero.webp')"></div>
        <div class="w-full h-full absolute z-1 bg-cover bg-center block md:hidden" style="background-image: url('/images/mobile-bg-hero-home.jpeg')"></div>
        <div class="w-full h-full absolute z-2 bg-[#00000060]"></div>

        <div class="absolute z-3 max-w-[1100px] px-[1rem] md:px-[3rem] left-0 right-0 ml-auto mr-auto pt-24 md:pt-0">
            <h1 class="text-white text-[30px] md:text-[50px] text-center md:text-start font-bold leading-[1.15] pt-[100px]">
                Creëer <i class="instrument-serif-font">jouw droomnagels</i><br>met onze luxe producten
            </h1>
            <p class="text-white opacity-80 text-[15px] mt-4 mb-6 text-center md:text-start">
                Ontdek hoogwaardige nagelproducten voor thuis of in de salon. Van gelpolish tot nail art.<br class="hidden md:block">
                Alles wat je nodig hebt om jouw droomnagels te creëren, vind je hier.
            </p>
        </div>

        <div class="p-4 rounded-xl bg-[#b38867]/50 border border-[#a0795c]/75 w-fit max-w-[80%] absolute z-10 left-4 md:left-auto md:right-4 bottom-4">
            <h3 class="text-white text-sm font-medium tracking-tight">Nieuw jasje, zelfde topkwaliteit!</h3>
            <p class="text-white opacity-80 text-xs">
                We stappen geleidelijk over op onze nieuwe verpakking. <br class="hidden md:block">
                Het product blijft precies hetzelfde alleen de look is vernieuwd.
            </p>
        </div>
    </div>
</div>

<div class="w-full h-auto">
    <div class="max-w-[1100px] px-[1rem] md:px-[3rem] mx-auto py-[5rem]">
        <div class="flex items-end gap-[2rem] mb-8">
            <div class="w-1/2">
                <h2 class="text-[#191919] text-[38px] font-light leading-[1.15]">
                    <i class="instrument-serif-font text-[#b38867]">Bestsellers</i>
                </h2>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($bestsellers as $product)
                <div class="bg-white p-[1.5rem] rounded-lg flex flex-col h-full border-1 border-gray-100 relative">
                    @if (isset($product->voorraad) && (int)$product->voorraad === 0)
                        <span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded">
                            Uitverkocht
                        </span>
                    @endif

                    @if ($product->uitverkoop)
                        @if (!(isset($product->voorraad) && (int)$product->voorraad === 0))
                            <span class="absolute top-2 left-2 bg-red-400 text-white text-xs font-semibold px-2 py-1 rounded flex items-center gap-2">
                                <i class="fa-solid fa-tag"></i>
                                In de uitverkoop!
                            </span>
                        @endif
                    @endif

                    @php
                        $slug = Str::slug($product->naam);
                        $img = $fotoUrl($product->foto ?? null);
                    @endphp

                    <!-- Afbeelding -->
                    <a href="{{ route('producten.show', ['product' => $product->id, 'slug' => $slug]) }}"
                       class="w-full aspect-square overflow-hidden border border-gray-200 rounded-lg p-[1rem] block">
                        @if($img)
                            <img src="{{ $img }}" alt="{{ $product->naam }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full grid place-items-center text-xs text-gray-400">Geen afbeelding</div>
                        @endif
                    </a>

                    <!-- Inhoud -->
                    <div class="flex flex-col justify-between flex-1 mt-4">
                        <div class="flex flex-col gap-[0.5rem]">
                            <h2 class="text-[16px] font-medium">{{ $product->naam }}</h2>
                        </div>

                        <div class="mt-4">
                            <p class="text-[#191919] opacity-80 text-[15px] mb-2">
                                €{{ number_format((float)$product->prijs, 2, ',', '.') }}
                            </p>

                            <form action="{{ route('winkelwagen.toevoegen', $product) }}" method="POST" class="toevoegen-form" data-product-id="{{ $product->id }}">
                                @csrf

                                @if (isset($product->voorraad) && (int)$product->voorraad === 0)
                                    <div class="cursor-not-allowed select-none w-full py-[0.4rem] bg-[#b38867] opacity-25 transition rounded-md text-white text-[15px] font-medium flex items-center justify-center gap-2">
                                        <lord-icon
                                            src="https://cdn.lordicon.com/pbrgppbb.json"
                                            trigger="hover"
                                            colors="primary:#ffffff"
                                            style="width:20px;height:20px">
                                        </lord-icon>
                                        Toevoegen
                                    </div>
                                @else
                                    <button type="submit" class="cursor-pointer w-full py-[0.4rem] bg-[#b38867] hover:bg-[#947054] transition rounded-md text-white text-[15px] font-medium flex items-center justify-center gap-2">
                                        <lord-icon
                                            src="https://cdn.lordicon.com/pbrgppbb.json"
                                            trigger="hover"
                                            colors="primary:#ffffff"
                                            style="width:20px;height:20px">
                                        </lord-icon>
                                        Toevoegen
                                    </button>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
