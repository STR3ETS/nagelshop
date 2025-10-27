@extends('layouts.pages')
@section('content')
<div class="p-2">
    <div class="w-full h-[350px] md:h-[600px] overflow-y-hidden flex items-center relative rounded-3xl">
        <!-- <video class="w-full h-full absolute z-1 object-cover" src="assets/home-hero.mp4" autoplay muted loop></video> -->
         <div class="w-full h-full absolute z-1 bg-cover bg-center" style="background-image: url('/images/hero.webp')"></div>
        <div class="w-full h-full absolute z-2 bg-[#00000050]"></div>
        <div class="absolute z-3 max-w-[1100px] px-[1rem] md:px-[3rem] left-0 right-0 ml-auto mr-auto">
            <h1 class="text-white text-[34px] md:text-[50px] font-bold leading-[1.15] pt-[100px]">Creëer <i class="instrument-serif-font">jouw droomnagels</i><br>met onze luxe producten</h1>
            <p class="text-white opacity-80 text-[15px] mt-4 mb-6">Ontdek hoogwaardige nagelproducten voor thuis of in de salon. Van gelpolish tot nail art.<br class="hidden md:block">Alles wat je nodig hebt om jouw droomnagels te creëren, vind je hier.</p>
        </div>
    </div>
</div>
<!-- <div class="w-full h-auto">
    <div class="max-w-[1100px] px-[1rem] md:px-[3rem] mx-auto py-[5rem]">
        <div class="flex items-end gap-[2rem] mb-8">
            <div class="w-1/2">
                <h2 class="text-[#191919] text-[38px] font-light leading-[1.15]"><i class="instrument-serif-font text-[#b38867]">Catogorieën</i></h2>
            </div>
        </div>
        <div class="grid grid-cols-3 md:grid-cols-7 gap-4">
            <a href="/producten" class="flex flex-col items-center">
                <div class="w-32 h-32 bg-white rounded-full overflow-hidden flex items-center justify-center relative">
                    <img class="absolute z-1" src="{{ asset('images/catogorieen-fotos/bases-tops.webp') }}" alt="">
                </div>
                <p class="text-[#191919] font-medium text-sm text-center mt-4">Bases & Tops</p>
            </a>
            <a href="/producten" class="flex flex-col items-center">
                <div class="w-32 h-32 bg-white rounded-full overflow-hidden flex items-center justify-center relative">
                    <img class="absolute z-1" src="{{ asset('images/catogorieen-fotos/french-base.webp') }}" alt="">
                </div>
                <p class="text-[#191919] font-medium text-sm text-center mt-4">French Base</p>
            </a>
            <a href="/producten" class="flex flex-col items-center">
                <div class="w-32 h-32 bg-white rounded-full overflow-hidden flex items-center justify-center relative">
                    <img class="absolute z-1" src="{{ asset('/images/catogorieen-fotos/gel.webp') }}" alt="">
                </div>
                <p class="text-[#191919] font-medium text-sm text-center mt-4">Gel</p>
            </a>
            <a href="/producten" class="flex flex-col items-center">
                <div class="w-32 h-32 bg-white rounded-full overflow-hidden flex items-center justify-center relative">
                    <img class="absolute z-1" src="{{ asset('/images/catogorieen-fotos/gel-polish.webp') }}" alt="">
                </div>
                <p class="text-[#191919] font-medium text-sm text-center mt-4">Gel Polish</p>
            </a>
            <a href="/producten" class="flex flex-col items-center">
                <div class="w-32 h-32 bg-white rounded-full overflow-hidden flex items-center justify-center relative">
                    <img class="absolute z-1" src="{{ asset('/images/catogorieen-fotos/design.webp') }}" alt="">
                </div>
                <p class="text-[#191919] font-medium text-sm text-center mt-4">Design</p>
            </a>
            <a href="/producten" class="flex flex-col items-center">
                <div class="w-32 h-32 bg-white rounded-full overflow-hidden flex items-center justify-center relative">
                    <img class="absolute z-1" src="{{ asset('/images/catogorieen-fotos/liquids.webp') }}" alt="">
                </div>
                <p class="text-[#191919] font-medium text-sm text-center mt-4">Liquids</p>
            </a>
            <a href="/producten" class="flex flex-col items-center">
                <div class="w-32 h-32 bg-white rounded-full overflow-hidden flex items-center justify-center relative">
                    <img class="absolute z-1" src="{{ asset('/images/catogorieen-fotos/forms-tips.webp') }}" alt="">
                </div>
                <p class="text-[#191919] font-medium text-sm text-center mt-4">Forms & Tips</p>
            </a>
        </div>
    </div>
</div> -->
<div class="w-full h-auto">
    <div class="max-w-[1100px] px-[1rem] md:px-[3rem] mx-auto py-[5rem]">
        <div class="flex items-end gap-[2rem] mb-8">
            <div class="w-1/2">
                <h2 class="text-[#191919] text-[38px] font-light leading-[1.15]"><i class="instrument-serif-font text-[#b38867]">Bestsellers</i></h2>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($producten->take(4) as $product)
                <div class="bg-white p-[1.5rem] rounded-lg flex flex-col h-full border-1 border-gray-100 relative">
                    @if ($product->voorraad === 0)
                        <span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded">
                            Uitverkocht
                        </span>
                    @endif
                    <!-- Afbeelding -->
                    <div class="w-full aspect-square overflow-hidden border border-gray-200 rounded-lg p-[1rem]">
                        <img src="{{ asset('storage/producten/' . $product->foto) }}" alt="{{ $product->naam }}" class="w-full h-full object-cover">
                    </div>
                    <!-- Inhoud -->
                    <div class="flex flex-col justify-between flex-1 mt-4">
                        <div class="flex flex-col gap-[0.5rem]">
                            <h2 class="text-[16px] font-medium">{{ $product->naam }}</h2>
                        </div>
                        <!-- Prijs en button onderaan -->
                        <div class="mt-4">
                            <p class="text-[#191919] opacity-80 text-[15px] mb-2">€{{ number_format($product->prijs, 2, ',', '.') }}</p>
                            <form action="{{ route('winkelwagen.toevoegen', $product) }}" method="POST" class="toevoegen-form" data-product-id="{{ $product->id }}">
                                @csrf
                                @if ($product->voorraad === 0)
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
<!-- <div class="w-full h-auto bg-white">
    <div class="max-w-[1100px] mx-auto py-[5rem]">
        <h2 class="text-[#191919] text-[38px] font-light leading-[1.15] text-center mb-6">Welke <i class="instrument-serif-font text-[#e5b2a4]">manicure</i> past bij jou?</h2>
        <div class="w-full flex justify-center gap-[2rem]">
            <div class="max-w-[25rem]">
                <img src="assets/manicure-1.webp" class="rounded-lg">
                <h3 class="text-[#191919] text-[30px] font-medium leading-[1.15] my-4 instrument-serif-font italic">Gel Stickers</h3>
                <a href="#" class="px-[1.5rem] py-[0.4rem] bg-[#191919] hover:bg-[#222222] transition rounded-md text-white text-[15px] font-medium">Ontdek nu</a>
            </div>
            <div class="max-w-[25rem]">
                <img src="assets/manicure-2.webp" class="rounded-lg">
                <h3 class="text-[#191919] text-[30px] font-medium leading-[1.15] my-4 instrument-serif-font italic">Gellak</h3>
                <a href="#" class="px-[1.5rem] py-[0.4rem] bg-[#191919] hover:bg-[#222222] transition rounded-md text-white text-[15px] font-medium">Ontdek nu</a>
            </div>
        </div>
    </div>
</div> -->
@endsection