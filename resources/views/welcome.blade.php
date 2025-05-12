@extends('layouts.pages')
@section('content')
<div class="w-full h-[500px] overflow-y-hidden flex items-center relative">
    <video src="assets/home-hero.mp4" autoplay muted loop></video>
    <div class="w-full h-full absolute z-[1] bg-[#00000050]"></div>
    <div class="absolute z-[3] max-w-[1100px] left-0 right-0 ml-auto mr-auto">
        <h1 class="text-white text-[50px] font-bold leading-[1.15]">Creëer <i class="instrument-serif-font text-[#ff64ba]">jouw droomnagels</i><br>met onze luxe producten</h1>
        <p class="text-white opacity-80 text-[15px] mt-4 mb-6">Ontdek hoogwaardige nagelproducten voor thuis of in de salon. Van gelpolish tot nail art.<br>Alles wat je nodig hebt om jouw droomnagels te creëren, vind je hier.</p>
        <div class="flex items-center gap-[1rem]">
            <a href="#" class="px-[1.5rem] py-[0.4rem] bg-[#ff64ba] hover:bg-[#96366c] transition rounded-md text-white text-[15px] font-medium">Bekijk Collectie '25</a>
        </div>
    </div>
</div>
<div class="w-full h-auto">
    <div class="max-w-[1100px] mx-auto py-[5rem]">
        <div class="flex items-end gap-[2rem] mb-8">
            <div class="w-1/2">
                <h2 class="text-[#191919] text-[38px] font-light leading-[1.15]"><i class="instrument-serif-font text-[#ff64ba]">Populairste</i> catogorieën</h2>
            </div>
            <div class="w-1/2">
                <p class="text-[#191919] opacity-80 text-[15px]">Bekijk onze bestsellers en ontdek welke nagelproducten het meest geliefd zijn bij onze klanten.</p>
            </div>
        </div>
        <div class="w-full h-auto flex gap-[1rem]">
            <a href="#" class="w-1/3 min-h-[250px] p-[2rem] bg-cover bg-center rounded-md relative overflow-hidden group">
                <div class="absolute inset-0 bg-cover bg-center transition-transform duration-500 scale-100 group-hover:scale-110"
                style="background-image: url('{{ Vite::asset('resources/images/catogory-1.jpg') }}'); z-index: 0;">
            </div>
            <div class="w-full h-full bg-[#00000050] absolute z-10 left-0 top-0"></div>
            <div class="w-full h-auto p-[2rem] absolute z-20 bottom-0 left-0 flex items-center justify-between">
                <h3 class="text-white text-[26px] font-medium leading-[1.15] w-fit">Gelpolish</h3>
                <svg class="rotate-[45deg]" width="20px" height="20px" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" fill="#fff" stroke="#fff">
                    </svg>
                </div>
            </a>
            <a href="#" class="w-1/3 min-h-[250px] p-[2rem] bg-cover bg-center rounded-md relative overflow-hidden group">
                <div class="absolute inset-0 bg-cover bg-center transition-transform duration-500 scale-100 group-hover:scale-110"
                style="background-image: url('{{ Vite::asset('resources/images/catogory-2.jpg') }}'); z-index: 0;">
            </div>
            <div class="w-full h-full bg-[#00000050] absolute z-10 left-0 top-0"></div>
            <div class="w-full h-auto p-[2rem] absolute z-20 bottom-0 left-0 flex items-center justify-between">
                <h3 class="text-white text-[26px] font-medium leading-[1.15] w-fit">Liguid gel</h3>
                <svg class="rotate-[45deg]" width="20px" height="20px" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" fill="#fff" stroke="#fff">
                    </svg>
                </div>
            </a>
            <a href="#" class="w-1/3 min-h-[250px] p-[2rem] bg-cover bg-center rounded-md relative overflow-hidden group">
                <div class="absolute inset-0 bg-cover bg-center transition-transform duration-500 scale-100 group-hover:scale-110"
                style="background-image: url('{{ Vite::asset('resources/images/catogory-3.jpg') }}'); z-index: 0;">
            </div>
            <div class="w-full h-full bg-[#00000050] absolute z-10 left-0 top-0"></div>
            <div class="w-full h-auto p-[2rem] absolute z-20 bottom-0 left-0 flex items-center justify-between">
                <h3 class="text-white text-[26px] font-medium leading-[1.15] w-fit">Acryl- en builder gels</h3>
                <svg class="rotate-[45deg]" width="20px" height="20px" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" fill="#fff" stroke="#fff">
                    </svg>
                </div>
            </a>
        </div>
    </div>
</div>
<div class="w-full h-auto bg-white">
    <div class="max-w-[1100px] mx-auto py-[5rem]">
        <h2 class="text-[#191919] text-[38px] font-light leading-[1.15] text-center mb-6">Welke <i class="instrument-serif-font text-[#ff64ba]">manicure</i> past bij jou?</h2>
        <div class="w-full flex justify-center gap-[2rem]">
            <div class="max-w-[25rem]">
                <img src="assets/manicure-1.webp" class="rounded-lg">
                <h3 class="text-[#191919] text-[30px] font-medium leading-[1.15] my-4 instrument-serif-font italic">Gel Stickers</h3>
                <p class="text-[#191919] opacity-80 text-[15px] mb-6">Perfecte gelnagels in enkele minuten. Kies simpelweg je favoriete kleur, effect of design, plak ze op en laat je nagels spreken. Snel aan te brengen en makkelijk te verwijderen — de ideale oplossing voor jouw drukke dagen.</p>
                <a href="#" class="px-[1.5rem] py-[0.4rem] bg-[#191919] hover:bg-[#222222] transition rounded-md text-white text-[15px] font-medium">Ontdek nu</a>
            </div>
            <div class="max-w-[25rem]">
                <img src="assets/manicure-2.webp" class="rounded-lg">
                <h3 class="text-[#191919] text-[30px] font-medium leading-[1.15] my-4 instrument-serif-font italic">Gellak</h3>
                <p class="text-[#191919] opacity-80 text-[15px] mb-6">Creëer salonwaardige gelnagels gewoon thuis en laat je creativiteit de vrije loop. Kies je favoriete kleur, laag, blend of ontwerp je eigen nail art voor een perfecte, gepersonaliseerde look die lang meegaat en eindeloze mogelijkheden biedt.</p>
                <a href="#" class="px-[1.5rem] py-[0.4rem] bg-[#191919] hover:bg-[#222222] transition rounded-md text-white text-[15px] font-medium">Ontdek nu</a>
            </div>
        </div>
    </div>
</div>
@endsection