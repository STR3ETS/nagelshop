@extends('layouts.pages')
@section('content')
<div class="p-2 h-[350px] md:h-auto">
    <div class="w-full h-full md:h-[350px] overflow-y-hidden rounded-3xl flex items-end relative bg-cover bg-center bg-[url(https://i.imgur.com/UA8Iztb.jpeg)]">
        <div class="w-full h-full absolute z-[1] bg-[#00000050]"></div>
        <div class="absolute z-[3] max-w-[1100px] px-[1rem] md:px-[3rem] left-0 right-0 ml-auto mr-auto pb-8">
            <h1 class="text-white text-[34px] md:text-[50px] font-bold leading-[1.15] text-center md:text-start">Veelgestelde <i class="instrument-serif-font text-[#fff]">vragen</i><br></h1>
        </div>
    </div>
</div>
<div class="w-full h-auto relative">
    <div class="max-w-[1100px] px-[1rem] md:px-[3rem] mx-auto py-[5rem] flex flex-col md:flex-row gap-8">
        <div class="w-full">
            <ul class="faq grid grid-cols-1 md:grid-cols-2 gap-4">
                <li class="faq-item bg-white rounded-lg p-[1.5rem]">
                    <h2 class="text-lg text-[#191919] font-semibold">Bestellen & Betalen</h2>
                    <div class="faq-item-content">
                        <hr class="my-3 border-gray-200">
                        <div class="mb-3">
                            <p class="opacity-80 text-sm text-[#191919] font-semibold">Hoe plaats ik een bestelling?</p>
                            <p class="opacity-80 text-sm text-[#191919]">Kies je favoriete producten, voeg ze toe aan je winkelmandje en reken veilig af via iDEAL, creditcard, PayPal, Bancontact of KBC/CBC.</p>
                        </div>
                        <div class="mb-3">
                            <p class="opacity-80 text-sm text-[#191919] font-semibold">Kan ik mijn bestelling nog wijzigen of annuleren?</p>
                            <p class="opacity-80 text-sm text-[#191919]">Dat kan zolang je bestelling nog niet is verzonden. Stuur ons binnen 2 uur na je bestelling een berichtje via de e-mail, dan passen we het direct aan.</p>
                        </div>
                        <div>
                            <p class="opacity-80 text-sm text-[#191919] font-semibold">Welke betaalmethodes accepteren jullie?</p>
                            <p class="opacity-80 text-sm text-[#191919]">We accepteren iDEAL, creditcard, PayPal, Bancontact of KBC/CBC. Zo kies je wat voor jou het makkelijkst is.</p>
                        </div>
                    </div>
                </li>
                <li class="faq-item bg-white rounded-lg p-[1.5rem]">
                    <h2 class="text-lg text-[#191919] font-semibold">Verzending & Levertijd</h2>
                    <div class="faq-item-content">
                        <hr class="my-3 border-gray-200">
                        <div class="mb-3">
                            <p class="opacity-80 text-sm text-[#191919] font-semibold">Hoe snel wordt mijn bestelling geleverd?</p>
                            <p class="opacity-80 text-sm text-[#191919]">Bestellingen die vóór 15:00 uur zijn geplaatst, worden dezelfde dag verzonden. Meestal heb je ze binnen 1–2 werkdagen in huis (NL &amp; BE).</p>
                        </div>
                        <div class="mb-3">
                            <p class="opacity-80 text-sm text-[#191919] font-semibold">Wat zijn de verzendkosten?</p>
                            <p class="opacity-80 text-sm text-[#191919]">Binnen Nederland: €6,35 (gratis vanaf €50). België: €9,50 (gratis vanaf €75). We versturen met PostNL.</p>
                        </div>
                    </div>
                </li>
                <li class="faq-item bg-white rounded-lg p-[1.5rem]">
                    <h2 class="text-lg text-[#191919] font-semibold">Wat zijn de openingstijden?</h2>
                    <div class="faq-item-content">
                        <hr class="my-3 border-gray-200">

                        @php
                            // Haal JSON op uit tabel 'instellingen' (kolom 'openingstijden')
                            // Voorbeeld JSON:
                            // {"maandag":"09:00-17:00","dinsdag":"09:00-17:00","woensdag":"09:00-17:00","donderdag":"09:00-17:00","vrijdag":"09:00-17:00","zaterdag":"Gesloten","zondag":"Gesloten"}
                            $json = DB::table('instellingen')->value('openingstijden');
                            $oh   = $json ? json_decode($json, true) : [];

                            // Fallback als er niets is
                            $labels = [
                                'maandag'   => 'Ma',
                                'dinsdag'   => 'Di',
                                'woensdag'  => 'Wo',
                                'donderdag' => 'Do',
                                'vrijdag'   => 'Vr',
                                'zaterdag'  => 'Za',
                                'zondag'    => 'Zo',
                            ];

                            // Helper om netjes "09:00 - 17:00" te tonen i.p.v. "09:00-17:00"
                            $fmt = function ($v) {
                                if (!$v || strtolower($v) === 'gesloten') return 'Gesloten';
                                return str_replace('-', ' - ', $v);
                            };

                            // Bouw lijst per dag
                            $perDag = [];
                            foreach ($labels as $key => $short) {
                                $perDag[] = [
                                    'key'   => $key,
                                    'short' => $short,
                                    'val'   => isset($oh[$key]) ? $fmt($oh[$key]) : 'Gesloten',
                                ];
                            }

                            // Probeer Ma–Vr te groeperen als alle werkdagen identiek zijn
                            $workdays = array_slice($perDag, 0, 5);
                            $sameWork = count(array_unique(array_column($workdays, 'val'))) === 1;

                            // Resultregels opbouwen
                            $regels = [];
                            if ($sameWork) {
                                $regels[] = [
                                    'label' => 'Ma - Vr',
                                    'val'   => $workdays[0]['val'],
                                ];
                            } else {
                                foreach ($workdays as $d) {
                                    $regels[] = ['label' => $d['short'], 'val' => $d['val']];
                                }
                            }
                            // Weekend altijd apart tonen
                            foreach (array_slice($perDag, 5) as $d) {
                                $regels[] = ['label' => $d['short'], 'val' => $d['val']];
                            }
                        @endphp

                        <p class="opacity-80 text-sm text-[#191919]">
                            @foreach ($regels as $i => $r)
                                {{ $r['label'] }}: {{ $r['val'] }}@if($i < count($regels)-1)<br>@endif
                            @endforeach
                        </p>
                    </div>
                </li>
                <li class="faq-item bg-white rounded-lg p-[1.5rem]">
                    <h2 class="text-lg text-[#191919] font-semibold">Retourneren & Ruilen</h2>
                    <div class="faq-item-content">
                        <hr class="my-3 border-gray-200">
                        <div class="mb-3">
                            <p class="opacity-80 text-sm text-[#191919] font-semibold">Kan ik producten retourneren?</p>
                            <p class="opacity-80 text-sm text-[#191919]">Ja, binnen 14 dagen na ontvangst. Producten moeten ongebruikt en ongeopend zijn. Meld je retour aan door ons een e-mail te sturen.</p>
                        </div>
                        <div class="mb-3">
                            <p class="opacity-80 text-sm text-[#191919] font-semibold">Wanneer krijg ik mijn geld terug?</p>
                            <p class="opacity-80 text-sm text-[#191919]">Binnen 5 werkdagen na ontvangst van je retourzending. Let op: de verzendkosten voor het retourneren zijn voor eigen rekening.</p>
                        </div>
                    </div>
                </li>
                <li class="faq-item bg-white rounded-lg p-[1.5rem]">
                    <h2 class="text-lg text-[#191919] font-semibold">Over Deluxe Nail Shop</h2>
                    <div class="faq-item-content">
                        <hr class="my-3 border-gray-200">
                        <div class="mb-3">
                            <p class="opacity-80 text-sm text-[#191919] font-semibold">Waar staat Deluxe Nail Shop voor?</p>
                            <p class="opacity-80 text-sm text-[#191919]">Wij geloven in kwaliteit, luxe en liefde voor detail. Elk product is zorgvuldig geselecteerd en getest, zodat jij het beste resultaat krijgt, zowel thuis als in je salon.</p>
                        </div>
                        <div class="mb-3">
                            <p class="opacity-80 text-sm text-[#191919] font-semibold">Hebben jullie ook een fysieke winkel?</p>
                            <p class="opacity-80 text-sm text-[#191919]">Ja! We zijn gevestigd aan Lentemorgen 5, kamer 5.36, 6903 CT Zevenaar. We staan voor je klaar!</p>
                        </div>
                    </div>
                </li>
                <li class="faq-item bg-white rounded-lg p-[1.5rem]">
                    <h2 class="text-lg text-[#191919] font-semibold">Contact & Service</h2>
                    <div class="faq-item-content">
                        <hr class="my-3 border-gray-200">
                        <div class="mb-3">
                            <p class="opacity-80 text-sm text-[#191919] font-semibold">Hoe kan ik contact opnemen?</p>
                            <p class="opacity-80 text-sm text-[#191919]">Via e-mail, WhatsApp of Instagram DM. We reageren meestal binnen enkele uren (ma–vrij 09:00–18:00).</p>
                        </div>
                        <div class="mb-3">
                            <p class="opacity-80 text-sm text-[#191919] font-semibold">Kan ik advies krijgen over de producten?</p>
                            <p class="opacity-80 text-sm text-[#191919]">Zeker! We helpen je graag persoonlijk bij het kiezen van de juiste producten of kleuren. Stuur ons gerust een bericht via e-mail, WhatsApp of Instagram DM, we denken met je mee!</p>
                        </div>
                    </div>
                </li>
            </ul>
            {{-- Download sectie: Algemene Voorwaarden & Privacybeleid --}}
            <div class="w-full mt-4">
                <div class="max-w-[1100px] mx-auto pb-[3rem]">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Algemene Voorwaarden --}}
                        <div class="bg-white rounded-lg p-[1.5rem] border border-gray-100 flex items-start gap-4">
                            <div class="shrink-0 w-10 h-10 rounded-full bg-[#b38867]/15 grid place-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#b38867]" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6M9 8h6m-9 8V6a2 2 0 0 1 2-2h6l4 4v10a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2z"/>
                            </svg>
                            </div>
                            <div class="flex-1">
                            <h3 class="text-lg text-[#191919] font-semibold">Algemene voorwaarden</h3>
                            <p class="opacity-80 text-sm text-[#191919] mt-1">Download onze algemene voorwaarden als PDF.</p>
                            <a href="{{ route('download.algemene_voorwaarden') }}"
                                class="inline-flex items-center gap-2 mt-3 px-4 py-2 rounded-md bg-[#b38867] text-white text-sm hover:bg-[#947054] transition">
                                Download PDF
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2M7 10l5 5 5-5M12 15V3"/>
                                </svg>
                            </a>
                            </div>
                        </div>
                        {{-- Privacybeleid --}}
                        <div class="bg-white rounded-lg p-[1.5rem] border border-gray-100 flex items-start gap-4">
                            <div class="shrink-0 w-10 h-10 rounded-full bg-[#b38867]/15 grid place-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#b38867]" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 12c2.21 0 4-1.79 4-4V5a4 4 0 1 0-8 0v3c0 2.21 1.79 4 4 4zm6 0V9a6 6 0 1 0-12 0v3a6 6 0 0 0-4 5v2h20v-2a6 6 0 0 0-4-5z"/>
                            </svg>
                            </div>
                            <div class="flex-1">
                            <h3 class="text-lg text-[#191919] font-semibold">Privacybeleid</h3>
                            <p class="opacity-80 text-sm text-[#191919] mt-1">Lees ons privacybeleid in PDF-vorm.</p>
                            <a href="{{ route('download.privacybeleid') }}"
                                class="inline-flex items-center gap-2 mt-3 px-4 py-2 rounded-md bg-[#b38867] text-white text-sm hover:bg-[#947054] transition">
                                Download PDF
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2M7 10l5 5 5-5M12 15V3"/>
                                </svg>
                            </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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