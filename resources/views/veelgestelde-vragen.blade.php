@extends('layouts.pages')
@section('content')
<div class="w-full h-[250px] overflow-y-hidden flex items-center relative bg-cover bg-center bg-[url(https://i.imgur.com/UA8Iztb.jpeg)]">
    <div class="w-full h-full absolute z-[1] bg-[#00000050]"></div>
    <div class="absolute z-[3] max-w-[1100px] px-[1rem] md:px-[3rem] left-0 right-0 ml-auto mr-auto">
        <h1 class="text-white text-[34px] md:text-[50px] font-bold leading-[1.15]">Veelgestelde <i class="instrument-serif-font text-[#fff]">vragen</i><br></h1>
    </div>
</div>
<div class="w-full h-auto relative">
    <div class="max-w-[1100px] px-[1rem] md:px-[3rem] mx-auto py-[5rem] flex flex-col md:flex-row gap-8">
        <div class="w-full">
            <ul class="faq grid grid-cols-1 md:grid-cols-2 gap-4">
                <li class="faq-item bg-white rounded-lg p-[1.5rem]">
                    <h2 class="text-lg text-[#191919] font-semibold">Wat zijn de openingstijden?</h2>
                    <div class="faq-item-content">
                        <hr class="my-3 border-gray-200">
                        <p class="opacity-80 text-sm text-[#191919]">Ma - Vr: 09:00 - 18:00 uur<br>Za: 11:00 - 16:00 uur<br>Zo: Gesloten</p>
                    </div>
                </li>
                <li class="faq-item bg-white rounded-lg p-[1.5rem]">
                    <h2 class="text-lg text-[#191919] font-semibold">Hoe kan ik bestellen?</h2>
                    <div class="faq-item-content">
                        <hr class="my-3 border-gray-200">
                        <p class="opacity-80 text-sm text-[#191919]">Hoe kan ik bestellen?</p>
                    </div>
                </li>
                <li class="faq-item bg-white rounded-lg p-[1.5rem]">
                    <h2 class="text-lg text-[#191919] font-semibold">Welke betaalmogelijkheden zijn er?</h2>
                    <div class="faq-item-content">
                        <hr class="my-3 border-gray-200">
                        <p class="opacity-80 text-sm text-[#191919]">Je kunt bij ons betalen met iDeal, Visa en Paypal</p>
                    </div>
                </li>
                <li class="faq-item bg-white rounded-lg p-[1.5rem]">
                    <h2 class="text-lg text-[#191919] font-semibold">Wat is jullie retourbeleid?</h2>
                    <div class="faq-item-content">
                        <hr class="my-3 border-gray-200">
                        <p class="opacity-80 text-sm text-[#191919]">Wil je een product retourneren, dan kan dat binnen 14 dagen na ontvangst zonder reden. Meld je retourzending aan via info@deluxenailshop.com. Producten dienen binnen 14 dagen retour gestuurd te worden, verzendkosten zijn voor eigen rekening.  Na ontvangst van de goederen wordt het verschuldigde bedrag retour gestort.</p>
                    </div>
                </li>
                <li class="faq-item bg-white rounded-lg p-[1.5rem]">
                    <h2 class="text-lg text-[#191919] font-semibold">Wat zijn de verzend mogelijkheden?</h2>
                    <div class="faq-item-content">
                        <hr class="my-3 border-gray-200">
                        <p class="opacity-80 text-sm text-[#191919]">Nederland €6.95 ( boven €75,- GRATIS )<br>België €11,- ( boven €75,- GRATIS )<br>We versturen met PostNL.</p>
                    </div>
                </li>
                <li class="faq-item bg-white rounded-lg p-[1.5rem]">
                    <h2 class="text-lg text-[#191919] font-semibold">Een product is niet op voorraad, wat nu?</h2>
                    <div class="faq-item-content">
                        <hr class="my-3 border-gray-200">
                        <p class="opacity-80 text-sm text-[#191919]">Binnen paar dagen komt het product weer op voorraad. Houdt de webshop in de gaten.</p>
                    </div>
                </li>
                <li class="faq-item bg-white rounded-lg p-[1.5rem]">
                    <h2 class="text-lg text-[#191919] font-semibold">Ik heb een klacht/opmering, wat moet ik doen?</h2>
                    <div class="faq-item-content">
                        <hr class="my-3 border-gray-200">
                        <p class="opacity-80 text-sm text-[#191919]">Stuur een email naar info@deluxenailshop.com met een duidelijke omschrijving van je klacht/opmerking. Er wordt zo snel mogelijk contact met je opgenomen.</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        gsap.registerPlugin(ScrollTrigger);

        const sidebar = document.getElementById('beschrijvingSidebar');

        ScrollTrigger.create({
            trigger: sidebar,
            start: 'top 26px', // begint sticky na 100px scroll
            endTrigger: '.grid', // einde is bij product-grid (pas aan indien nodig)
            end: 'bottom bottom',
            pin: true,
            pinSpacing: false,
            markers: false // zet op true voor debugging
        });
    });
</script>
@endsection