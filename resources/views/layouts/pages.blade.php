@php
use App\Models\Category;
use App\Models\Subcategory;

// Haal IDs op o.b.v. NAAM (moet 1-op-1 matchen met je DB)
$cat = Category::pluck('id', 'naam')->toArray();
$sub = Subcategory::pluck('id', 'naam')->toArray();

// Helpers om direct naar productenlijst met filter te linken
$catUrl = function (string $naam) use ($cat) {
    return isset($cat[$naam]) ? route('producten.index', ['categorie' => [$cat[$naam]]]) : '#';
};
$subUrl = function (string $naam) use ($sub) {
    return isset($sub[$naam]) ? route('producten.index', ['subcategorie' => [$sub[$naam]]]) : '#';
};
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'], 'build')

        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
        <script src="https://cdn.lordicon.com/lordicon.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://kit.fontawesome.com/4180a39c11.js"></script>


        <style>
            @import url('https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap');
            @import url('https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&display=swap');
            body {
                font-family: 'Rubik', sans-serif;
            }
            .instrument-serif-font {
                font-family: 'Instrument Serif', serif;
            }

            #mobile-menu {
                transform: translateX(-100%);
                transition: 0.3s ease-in-out;
            }
            #mobile-menu.active {
                transform: translateX(0%);
            }
        </style>

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                /*! tailwindcss v4.0.7 | MIT License | https://tailwindcss.com */@layer theme{:root,:host{--font-sans:'Instrument Sans',ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";--font-serif:ui-serif,Georgia,Cambria,"Times New Roman",Times,serif;--font-mono:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;--color-red-50:oklch(.971 .013 17.38);--color-red-100:oklch(.936 .032 17.717);--color-red-200:oklch(.885 .062 18.334);--color-red-300:oklch(.808 .114 19.571);--color-red-400:oklch(.704 .191 22.216);--color-red-500:oklch(.637 .237 25.331);--color-red-600:oklch(.577 .245 27.325);--color-red-700:oklch(.505 .213 27.518);--color-red-800:oklch(.444 .177 26.899);--color-red-900:oklch(.396 .141 25.723);--color-red-950:oklch(.258 .092 26.042);--color-orange-50:oklch(.98 .016 73.684);--color-orange-100:oklch(.954 .038 75.164);--color-orange-200:oklch(.901 .076 70.697);--color-orange-300:oklch(.837 .128 66.29);--color-orange-400:oklch(.75 .183 55.934);--color-orange-500:oklch(.705 .213 47.604);--color-orange-600:oklch(.646 .222 41.116);--color-orange-700:oklch(.553 .195 38.402);--color-orange-800:oklch(.47 .157 37.304);--color-orange-900:oklch(.408 .123 38.172);--color-orange-950:oklch(.266 .079 36.259);--color-amber-50:oklch(.987 .022 95.277);--color-amber-100:oklch(.962 .059 95.617);--color-amber-200:oklch(.924 .12 95.746);--color-amber-300:oklch(.879 .169 91.605);--color-amber-400:oklch(.828 .189 84.429);--color-amber-500:oklch(.769 .188 70.08);--color-amber-600:oklch(.666 .179 58.318);--color-amber-700:oklch(.555 .163 48.998);--color-amber-800:oklch(.473 .137 46.201);--color-amber-900:oklch(.414 .112 45.904);--color-amber-950:oklch(.279 .077 45.635);--color-yellow-50:oklch(.987 .026 102.212);--color-yellow-100:oklch(.973 .071 103.193);--color-yellow-200:oklch(.945 .129 101.54);--color-yellow-300:oklch(.905 .182 98.111);--color-yellow-400:oklch(.852 .199 91.936);--color-yellow-500:oklch(.795 .184 86.047);--color-yellow-600:oklch(.681 .162 75.834);--color-yellow-700:oklch(.554 .135 66.442);--color-yellow-800:oklch(.476 .114 61.907);--color-yellow-900:oklch(.421 .095 57.708);--color-yellow-950:oklch(.286 .066 53.813);--color-lime-50:oklch(.986 .031 120.757);--color-lime-100:oklch(.967 .067 122.328);--color-lime-200:oklch(.938 .127 124.321);--color-lime-300:oklch(.897 .196 126.665);--color-lime-400:oklch(.841 .238 128.85);--color-lime-500:oklch(.768 .233 130.85);--color-lime-600:oklch(.648 .2 131.684);--color-lime-700:oklch(.532 .157 131.589);--color-lime-800:oklch(.453 .124 130.933);--color-lime-900:oklch(.405 .101 131.063);--color-lime-950:oklch(.274 .072 132.109);--color-green-50:oklch(.982 .018 155.826);--color-green-100:oklch(.962 .044 156.743);--color-green-200:oklch(.925 .084 155.995);--color-green-300:oklch(.871 .15 154.449);--color-green-400:oklch(.792 .209 151.711);--color-green-500:oklch(.723 .219 149.579);--color-green-600:oklch(.627 .194 149.214);--color-green-700:oklch(.527 .154 150.069);--color-green-800:oklch(.448 .119 151.328);--color-green-900:oklch(.393 .095 152.535);--color-green-950:oklch(.266 .065 152.934);--color-emerald-50:oklch(.979 .021 166.113);--color-emerald-100:oklch(.95 .052 163.051);--color-emerald-200:oklch(.905 .093 164.15);--color-emerald-300:oklch(.845 .143 164.978);--color-emerald-400:oklch(.765 .177 163.223);--color-emerald-500:oklch(.696 .17 162.48);--color-emerald-600:oklch(.596 .145 163.225);--color-emerald-700:oklch(.508 .118 165.612);--color-emerald-800:oklch(.432 .095 166.913);--color-emerald-900:oklch(.378 .077 168.94);--color-emerald-950:oklch(.262 .051 172.552);--color-teal-50:oklch(.984 .014 180.72);--color-teal-100:oklch(.953 .051 180.801);--color-teal-200:oklch(.91 .096 180.426);--color-teal-300:oklch(.855 .138 181.071);--color-teal-400:oklch(.777 .152 181.912);--color-teal-500:oklch(.704 .14 182.503);--color-teal-600:oklch(.6 .118 184.704);--color-teal-700:oklch(.511 .096 186.391);--color-teal-800:oklch(.437 .078 188.216);--color-teal-900:oklch(.386 .063 188.416);--color-teal-950:oklch(.277 .046 192.524);--color-cyan-50:oklch(.984 .019 200.873);--color-cyan-100:oklch(.956 .045 203.388);--color-cyan-200:oklch(.917 .08 205.041);--color-cyan-300:oklch(.865 .127 207.078);--color-cyan-400:oklch(.789 .154 211.53);--color-cyan-500:oklch(.715 .143 215.221);--color-cyan-600:oklch(.609 .126 221.723);--color-cyan-700:oklch(.52 .105 223.128);--color-cyan-800:oklch(.45 .085 224.283);--color-cyan-900:oklch(.398 .07 227.392);--color-cyan-950:oklch(.302 .056 229.695);--color-sky-50:oklch(.977 .013 236.62);--color-sky-100:oklch(.951 .026 236.824);--color-sky-200:oklch(.901 .058 230.902);--color-sky-300:oklch(.828 .111 230.318);--color-sky-400:oklch(.746 .16 232.661);--color-sky-500:oklch(.685 .169 237.323);--color-sky-600:oklch(.588 .158 241.966);--color-sky-700:oklch(.5 .134 242.749);--color-sky-800:oklch(.443 .11 240.79);--color-sky-900:oklch(.391 .09 240.876);--color-sky-950:oklch(.293 .066 243.157);--color-blue-50:oklch(.97 .014 254.604);--color-blue-100:oklch(.932 .032 255.585);--color-blue-200:oklch(.882 .059 254.128);--color-blue-300:oklch(.809 .105 251.813);--color-blue-400:oklch(.707 .165 254.624);--color-blue-500:oklch(.623 .214 259.815);--color-blue-600:oklch(.546 .245 262.881);--color-blue-700:oklch(.488 .243 264.376);--color-blue-800:oklch(.424 .199 265.638);--color-blue-900:oklch(.379 .146 265.522);--color-blue-950:oklch(.282 .091 267.935);--color-indigo-50:oklch(.962 .018 272.314);--color-indigo-100:oklch(.93 .034 272.788);--color-indigo-200:oklch(.87 .065 274.039);--color-indigo-300:oklch(.785 .115 274.713);--color-indigo-400:oklch(.673 .182 276.935);--color-indigo-500:oklch(.585 .233 277.117);--color-indigo-600:oklch(.511 .262 276.966);--color-indigo-700:oklch(.457 .24 277.023);--color-indigo-800:oklch(.398 .195 277.366);--color-indigo-900:oklch(.359 .144 278.697);--color-indigo-950:oklch(.257 .09 281.288);--color-violet-50:oklch(.969 .016 293.756);--color-violet-100:oklch(.943 .029 294.588);--color-violet-200:oklch(.894 .057 293.283);--color-violet-300:oklch(.811 .111 293.571);--color-violet-400:oklch(.702 .183 293.541);--color-violet-500:oklch(.606 .25 292.717);--color-violet-600:oklch(.541 .281 293.009);--color-violet-700:oklch(.491 .27 292.581);--color-violet-800:oklch(.432 .232 292.759);--color-violet-900:oklch(.38 .189 293.745);--color-violet-950:oklch(.283 .141 291.089);--color-purple-50:oklch(.977 .014 308.299);--color-purple-100:oklch(.946 .033 307.174);--color-purple-200:oklch(.902 .063 306.703);--color-purple-300:oklch(.827 .119 306.383);--color-purple-400:oklch(.714 .203 305.504);--color-purple-500:oklch(.627 .265 303.9);--color-purple-600:oklch(.558 .288 302.321);--color-purple-700:oklch(.496 .265 301.924);--color-purple-800:oklch(.438 .218 303.724);--color-purple-900:oklch(.381 .176 304.987);--color-purple-950:oklch(.291 .149 302.717);--color-fuchsia-50:oklch(.977 .017 320.058);--color-fuchsia-100:oklch(.952 .037 318.852);--color-fuchsia-200:oklch(.903 .076 319.62);--color-fuchsia-300:oklch(.833 .145 321.434);--color-fuchsia-400:oklch(.74 .238 322.16);--color-fuchsia-500:oklch(.667 .295 322.15);--color-fuchsia-600:oklch(.591 .293 322.896);--color-fuchsia-700:oklch(.518 .253 323.949);--color-fuchsia-800:oklch(.452 .211 324.591);--color-fuchsia-900:oklch(.401 .17 325.612);--color-fuchsia-950:oklch(.293 .136 325.661);--color-pink-50:oklch(.971 .014 343.198);--color-pink-100:oklch(.948 .028 342.258);--color-pink-200:oklch(.899 .061 343.231);--color-pink-300:oklch(.823 .12 346.018);--color-pink-400:oklch(.718 .202 349.761);--color-pink-500:oklch(.656 .241 354.308);--color-pink-600:oklch(.592 .249 .584);--color-pink-700:oklch(.525 .223 3.958);--color-pink-800:oklch(.459 .187 3.815);--color-pink-900:oklch(.408 .153 2.432);--color-pink-950:oklch(.284 .109 3.907);--color-rose-50:oklch(.969 .015 12.422);--color-rose-100:oklch(.941 .03 12.58);--color-rose-200:oklch(.892 .058 10.001);--color-rose-300:oklch(.81 .117 11.638);--color-rose-400:oklch(.712 .194 13.428);--color-rose-500:oklch(.645 .246 16.439);--color-rose-600:oklch(.586 .253 17.585);--color-rose-700:oklch(.514 .222 16.935);--color-rose-800:oklch(.455 .188 13.697);--color-rose-900:oklch(.41 .159 10.272);--color-rose-950:oklch(.271 .105 12.094);--color-slate-50:oklch(.984 .003 247.858);--color-slate-100:oklch(.968 .007 247.896);--color-slate-200:oklch(.929 .013 255.508);--color-slate-300:oklch(.869 .022 252.894);--color-slate-400:oklch(.704 .04 256.788);--color-slate-500:oklch(.554 .046 257.417);--color-slate-600:oklch(.446 .043 257.281);--color-slate-700:oklch(.372 .044 257.287);--color-slate-800:oklch(.279 .041 260.031);--color-slate-900:oklch(.208 .042 265.755);--color-slate-950:oklch(.129 .042 264.695);--color-gray-50:oklch(.985 .002 247.839);--color-gray-100:oklch(.967 .003 264.542);--color-gray-200:oklch(.928 .006 264.531);--color-gray-300:oklch(.872 .01 258.338);--color-gray-400:oklch(.707 .022 261.325);--color-gray-500:oklch(.551 .027 264.364);--color-gray-600:oklch(.446 .03 256.802);--color-gray-700:oklch(.373 .034 259.733);--color-gray-800:oklch(.278 .033 256.848);--color-gray-900:oklch(.21 .034 264.665);--color-gray-950:oklch(.13 .028 261.692);--color-zinc-50:oklch(.985 0 0);--color-zinc-100:oklch(.967 .001 286.375);--color-zinc-200:oklch(.92 .004 286.32);--color-zinc-300:oklch(.871 .006 286.286);--color-zinc-400:oklch(.705 .015 286.067);--color-zinc-500:oklch(.552 .016 285.938);--color-zinc-600:oklch(.442 .017 285.786);--color-zinc-700:oklch(.37 .013 285.805);--color-zinc-800:oklch(.274 .006 286.033);--color-zinc-900:oklch(.21 .006 285.885);--color-zinc-950:oklch(.141 .005 285.823);--color-neutral-50:oklch(.985 0 0);--color-neutral-100:oklch(.97 0 0);--color-neutral-200:oklch(.922 0 0);--color-neutral-300:oklch(.87 0 0);--color-neutral-400:oklch(.708 0 0);--color-neutral-500:oklch(.556 0 0);--color-neutral-600:oklch(.439 0 0);--color-neutral-700:oklch(.371 0 0);--color-neutral-800:oklch(.269 0 0);--color-neutral-900:oklch(.205 0 0);--color-neutral-950:oklch(.145 0 0);--color-stone-50:oklch(.985 .001 106.423);--color-stone-100:oklch(.97 .001 106.424);--color-stone-200:oklch(.923 .003 48.717);--color-stone-300:oklch(.869 .005 56.366);--color-stone-400:oklch(.709 .01 56.259);--color-stone-500:oklch(.553 .013 58.071);--color-stone-600:oklch(.444 .011 73.639);--color-stone-700:oklch(.374 .01 67.558);--color-stone-800:oklch(.268 .007 34.298);--color-stone-900:oklch(.216 .006 56.043);--color-stone-950:oklch(.147 .004 49.25);--color-black:#000;--color-white:#fff;--spacing:.25rem;--breakpoint-sm:40rem;--breakpoint-md:48rem;--breakpoint-lg:64rem;--breakpoint-xl:80rem;--breakpoint-2xl:96rem;--container-3xs:16rem;--container-2xs:18rem;--container-xs:20rem;--container-sm:24rem;--container-md:28rem;--container-lg:32rem;--container-xl:36rem;--container-2xl:42rem;--container-3xl:48rem;--container-4xl:56rem;--container-5xl:64rem;--container-6xl:72rem;--container-7xl:80rem;--text-xs:.75rem;--text-xs--line-height:calc(1/.75);--text-sm:.875rem;--text-sm--line-height:calc(1.25/.875);--text-base:1rem;--text-base--line-height: 1.5 ;--text-lg:1.125rem;--text-lg--line-height:calc(1.75/1.125);--text-xl:1.25rem;--text-xl--line-height:calc(1.75/1.25);--text-2xl:1.5rem;--text-2xl--line-height:calc(2/1.5);--text-3xl:1.875rem;--text-3xl--line-height: 1.2 ;--text-4xl:2.25rem;--text-4xl--line-height:calc(2.5/2.25);--text-5xl:3rem;--text-5xl--line-height:1;--text-6xl:3.75rem;--text-6xl--line-height:1;--text-7xl:4.5rem;--text-7xl--line-height:1;--text-8xl:6rem;--text-8xl--line-height:1;--text-9xl:8rem;--text-9xl--line-height:1;--font-weight-thin:100;--font-weight-extralight:200;--font-weight-light:300;--font-weight-normal:400;--font-weight-medium:500;--font-weight-semibold:600;--font-weight-bold:700;--font-weight-extrabold:800;--font-weight-black:900;--tracking-tighter:-.05em;--tracking-tight:-.025em;--tracking-normal:0em;--tracking-wide:.025em;--tracking-wider:.05em;--tracking-widest:.1em;--leading-tight:1.25;--leading-snug:1.375;--leading-normal:1.5;--leading-relaxed:1.625;--leading-loose:2;--radius-xs:.125rem;--radius-sm:.25rem;--radius-md:.375rem;--radius-lg:.5rem;--radius-xl:.75rem;--radius-2xl:1rem;--radius-3xl:1.5rem;--radius-4xl:2rem;--shadow-2xs:0 1px #0000000d;--shadow-xs:0 1px 2px 0 #0000000d;--shadow-sm:0 1px 3px 0 #0000001a,0 1px 2px -1px #0000001a;--shadow-md:0 4px 6px -1px #0000001a,0 2px 4px -2px #0000001a;--shadow-lg:0 10px 15px -3px #0000001a,0 4px 6px -4px #0000001a;--shadow-xl:0 20px 25px -5px #0000001a,0 8px 10px -6px #0000001a;--shadow-2xl:0 25px 50px -12px #00000040;--inset-shadow-2xs:inset 0 1px #0000000d;--inset-shadow-xs:inset 0 1px 1px #0000000d;--inset-shadow-sm:inset 0 2px 4px #0000000d;--drop-shadow-xs:0 1px 1px #0000000d;--drop-shadow-sm:0 1px 2px #00000026;--drop-shadow-md:0 3px 3px #0000001f;--drop-shadow-lg:0 4px 4px #00000026;--drop-shadow-xl:0 9px 7px #0000001a;--drop-shadow-2xl:0 25px 25px #00000026;--ease-in:cubic-bezier(.4,0,1,1);--ease-out:cubic-bezier(0,0,.2,1);--ease-in-out:cubic-bezier(.4,0,.2,1);--animate-spin:spin 1s linear infinite;--animate-ping:ping 1s cubic-bezier(0,0,.2,1)infinite;--animate-pulse:pulse 2s cubic-bezier(.4,0,.6,1)infinite;--animate-bounce:bounce 1s infinite;--blur-xs:4px;--blur-sm:8px;--blur-md:12px;--blur-lg:16px;--blur-xl:24px;--blur-2xl:40px;--blur-3xl:64px;--perspective-dramatic:100px;--perspective-near:300px;--perspective-normal:500px;--perspective-midrange:800px;--perspective-distant:1200px;--aspect-video:16/9;--default-transition-duration:.15s;--default-transition-timing-function:cubic-bezier(.4,0,.2,1);--default-font-family:var(--font-sans);--default-font-feature-settings:var(--font-sans--font-feature-settings);--default-font-variation-settings:var(--font-sans--font-variation-settings);--default-mono-font-family:var(--font-mono);--default-mono-font-feature-settings:var(--font-mono--font-feature-settings);--default-mono-font-variation-settings:var(--font-mono--font-variation-settings)}}@layer base{*,:after,:before,::backdrop{box-sizing:border-box;border:0 solid;margin:0;padding:0}::file-selector-button{box-sizing:border-box;border:0 solid;margin:0;padding:0}html,:host{-webkit-text-size-adjust:100%;-moz-tab-size:4;tab-size:4;line-height:1.5;font-family:var(--default-font-family,ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji");font-feature-settings:var(--default-font-feature-settings,normal);font-variation-settings:var(--default-font-variation-settings,normal);-webkit-tap-highlight-color:transparent}body{line-height:inherit}hr{height:0;color:inherit;border-top-width:1px}abbr:where([title]){-webkit-text-decoration:underline dotted;text-decoration:underline dotted}h1,h2,h3,h4,h5,h6{font-size:inherit;font-weight:inherit}a{color:inherit;-webkit-text-decoration:inherit;text-decoration:inherit}b,strong{font-weight:bolder}code,kbd,samp,pre{font-family:var(--default-mono-font-family,ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace);font-feature-settings:var(--default-mono-font-feature-settings,normal);font-variation-settings:var(--default-mono-font-variation-settings,normal);font-size:1em}small{font-size:80%}sub,sup{vertical-align:baseline;font-size:75%;line-height:0;position:relative}sub{bottom:-.25em}sup{top:-.5em}table{text-indent:0;border-color:inherit;border-collapse:collapse}:-moz-focusring{outline:auto}progress{vertical-align:baseline}summary{display:list-item}ol,ul,menu{list-style:none}img,svg,video,canvas,audio,iframe,embed,object{vertical-align:middle;display:block}img,video{max-width:100%;height:auto}button,input,select,optgroup,textarea{font:inherit;font-feature-settings:inherit;font-variation-settings:inherit;letter-spacing:inherit;color:inherit;opacity:1;background-color:#0000;border-radius:0}::file-selector-button{font:inherit;font-feature-settings:inherit;font-variation-settings:inherit;letter-spacing:inherit;color:inherit;opacity:1;background-color:#0000;border-radius:0}:where(select:is([multiple],[size])) optgroup{font-weight:bolder}:where(select:is([multiple],[size])) optgroup option{padding-inline-start:20px}::file-selector-button{margin-inline-end:4px}::placeholder{opacity:1;color:color-mix(in oklab,currentColor 50%,transparent)}textarea{resize:vertical}::-webkit-search-decoration{-webkit-appearance:none}::-webkit-date-and-time-value{min-height:1lh;text-align:inherit}::-webkit-datetime-edit{display:inline-flex}::-webkit-datetime-edit-fields-wrapper{padding:0}::-webkit-datetime-edit{padding-block:0}::-webkit-datetime-edit-year-field{padding-block:0}::-webkit-datetime-edit-month-field{padding-block:0}::-webkit-datetime-edit-day-field{padding-block:0}::-webkit-datetime-edit-hour-field{padding-block:0}::-webkit-datetime-edit-minute-field{padding-block:0}::-webkit-datetime-edit-second-field{padding-block:0}::-webkit-datetime-edit-millisecond-field{padding-block:0}::-webkit-datetime-edit-meridiem-field{padding-block:0}:-moz-ui-invalid{box-shadow:none}button,input:where([type=button],[type=reset],[type=submit]){-webkit-appearance:button;-moz-appearance:button;appearance:button}::file-selector-button{-webkit-appearance:button;-moz-appearance:button;appearance:button}::-webkit-inner-spin-button{height:auto}::-webkit-outer-spin-button{height:auto}[hidden]:where(:not([hidden=until-found])){display:none!important}}@layer components;@layer utilities{.absolute{position:absolute}.relative{position:relative}.static{position:static}.inset-0{inset:calc(var(--spacing)*0)}.-mt-\[4\.9rem\]{margin-top:-4.9rem}.-mb-px{margin-bottom:-1px}.mb-1{margin-bottom:calc(var(--spacing)*1)}.mb-2{margin-bottom:calc(var(--spacing)*2)}.mb-4{margin-bottom:calc(var(--spacing)*4)}.mb-6{margin-bottom:calc(var(--spacing)*6)}.-ml-8{margin-left:calc(var(--spacing)*-8)}.flex{display:flex}.hidden{display:none}.inline-block{display:inline-block}.inline-flex{display:inline-flex}.table{display:table}.aspect-\[335\/376\]{aspect-ratio:335/376}.h-1{height:calc(var(--spacing)*1)}.h-1\.5{height:calc(var(--spacing)*1.5)}.h-2{height:calc(var(--spacing)*2)}.h-2\.5{height:calc(var(--spacing)*2.5)}.h-3{height:calc(var(--spacing)*3)}.h-3\.5{height:calc(var(--spacing)*3.5)}.h-14{height:calc(var(--spacing)*14)}.h-14\.5{height:calc(var(--spacing)*14.5)}.min-h-screen{min-height:100vh}.w-1{width:calc(var(--spacing)*1)}.w-1\.5{width:calc(var(--spacing)*1.5)}.w-2{width:calc(var(--spacing)*2)}.w-2\.5{width:calc(var(--spacing)*2.5)}.w-3{width:calc(var(--spacing)*3)}.w-3\.5{width:calc(var(--spacing)*3.5)}.w-\[448px\]{width:448px}.w-full{width:100%}.max-w-\[335px\]{max-width:335px}.max-w-none{max-width:none}.flex-1{flex:1}.shrink-0{flex-shrink:0}.translate-y-0{--tw-translate-y:calc(var(--spacing)*0);translate:var(--tw-translate-x)var(--tw-translate-y)}.transform{transform:var(--tw-rotate-x)var(--tw-rotate-y)var(--tw-rotate-z)var(--tw-skew-x)var(--tw-skew-y)}.flex-col{flex-direction:column}.flex-col-reverse{flex-direction:column-reverse}.items-center{align-items:center}.justify-center{justify-content:center}.justify-end{justify-content:flex-end}.gap-3{gap:calc(var(--spacing)*3)}.gap-4{gap:calc(var(--spacing)*4)}:where(.space-x-1>:not(:last-child)){--tw-space-x-reverse:0;margin-inline-start:calc(calc(var(--spacing)*1)*var(--tw-space-x-reverse));margin-inline-end:calc(calc(var(--spacing)*1)*calc(1 - var(--tw-space-x-reverse)))}.overflow-hidden{overflow:hidden}.rounded-full{border-radius:3.40282e38px}.rounded-sm{border-radius:var(--radius-sm)}.rounded-t-lg{border-top-left-radius:var(--radius-lg);border-top-right-radius:var(--radius-lg)}.rounded-br-lg{border-bottom-right-radius:var(--radius-lg)}.rounded-bl-lg{border-bottom-left-radius:var(--radius-lg)}.border{border-style:var(--tw-border-style);border-width:1px}.border-\[\#19140035\]{border-color:#19140035}.border-\[\#e3e3e0\]{border-color:#e3e3e0}.border-black{border-color:var(--color-black)}.border-transparent{border-color:#0000}.bg-\[\#1b1b18\]{background-color:#1b1b18}.bg-\[\#FDFDFC\]{background-color:#fdfdfc}.bg-\[\#dbdbd7\]{background-color:#dbdbd7}.bg-\[\#fff2f2\]{background-color:#fff2f2}.bg-white{background-color:var(--color-white)}.p-6{padding:calc(var(--spacing)*6)}.px-5{padding-inline:calc(var(--spacing)*5)}.py-1{padding-block:calc(var(--spacing)*1)}.py-1\.5{padding-block:calc(var(--spacing)*1.5)}.py-2{padding-block:calc(var(--spacing)*2)}.pb-12{padding-bottom:calc(var(--spacing)*12)}.text-sm{font-size:var(--text-sm);line-height:var(--tw-leading,var(--text-sm--line-height))}.text-\[13px\]{font-size:13px}.leading-\[20px\]{--tw-leading:20px;line-height:20px}.leading-normal{--tw-leading:var(--leading-normal);line-height:var(--leading-normal)}.font-medium{--tw-font-weight:var(--font-weight-medium);font-weight:var(--font-weight-medium)}.text-\[\#1b1b18\]{color:#1b1b18}.text-\[\#706f6c\]{color:#706f6c}.text-\[\#F53003\],.text-\[\#f53003\]{color:#f53003}.text-white{color:var(--color-white)}.underline{text-decoration-line:underline}.underline-offset-4{text-underline-offset:4px}.opacity-100{opacity:1}.shadow-\[0px_0px_1px_0px_rgba\(0\,0\,0\,0\.03\)\,0px_1px_2px_0px_rgba\(0\,0\,0\,0\.06\)\]{--tw-shadow:0px 0px 1px 0px var(--tw-shadow-color,#00000008),0px 1px 2px 0px var(--tw-shadow-color,#0000000f);box-shadow:var(--tw-inset-shadow),var(--tw-inset-ring-shadow),var(--tw-ring-offset-shadow),var(--tw-ring-shadow),var(--tw-shadow)}.shadow-\[inset_0px_0px_0px_1px_rgba\(26\,26\,0\,0\.16\)\]{--tw-shadow:inset 0px 0px 0px 1px var(--tw-shadow-color,#1a1a0029);box-shadow:var(--tw-inset-shadow),var(--tw-inset-ring-shadow),var(--tw-ring-offset-shadow),var(--tw-ring-shadow),var(--tw-shadow)}.\!filter{filter:var(--tw-blur,)var(--tw-brightness,)var(--tw-contrast,)var(--tw-grayscale,)var(--tw-hue-rotate,)var(--tw-invert,)var(--tw-saturate,)var(--tw-sepia,)var(--tw-drop-shadow,)!important}.filter{filter:var(--tw-blur,)var(--tw-brightness,)var(--tw-contrast,)var(--tw-grayscale,)var(--tw-hue-rotate,)var(--tw-invert,)var(--tw-saturate,)var(--tw-sepia,)var(--tw-drop-shadow,)}.transition-all{transition-property:all;transition-timing-function:var(--tw-ease,var(--default-transition-timing-function));transition-duration:var(--tw-duration,var(--default-transition-duration))}.transition-opacity{transition-property:opacity;transition-timing-function:var(--tw-ease,var(--default-transition-timing-function));transition-duration:var(--tw-duration,var(--default-transition-duration))}.delay-300{transition-delay:.3s}.duration-750{--tw-duration:.75s;transition-duration:.75s}.not-has-\[nav\]\:hidden:not(:has(:is(nav))){display:none}.before\:absolute:before{content:var(--tw-content);position:absolute}.before\:top-0:before{content:var(--tw-content);top:calc(var(--spacing)*0)}.before\:top-1\/2:before{content:var(--tw-content);top:50%}.before\:bottom-0:before{content:var(--tw-content);bottom:calc(var(--spacing)*0)}.before\:bottom-1\/2:before{content:var(--tw-content);bottom:50%}.before\:left-\[0\.4rem\]:before{content:var(--tw-content);left:.4rem}.before\:border-l:before{content:var(--tw-content);border-left-style:var(--tw-border-style);border-left-width:1px}.before\:border-\[\#e3e3e0\]:before{content:var(--tw-content);border-color:#e3e3e0}@media (hover:hover){.hover\:border-\[\#1915014a\]:hover{border-color:#1915014a}.hover\:border-\[\#19140035\]:hover{border-color:#19140035}.hover\:border-black:hover{border-color:var(--color-black)}.hover\:bg-black:hover{background-color:var(--color-black)}}@media (width>=64rem){.lg\:-mt-\[6\.6rem\]{margin-top:-6.6rem}.lg\:mb-0{margin-bottom:calc(var(--spacing)*0)}.lg\:mb-6{margin-bottom:calc(var(--spacing)*6)}.lg\:-ml-px{margin-left:-1px}.lg\:ml-0{margin-left:calc(var(--spacing)*0)}.lg\:block{display:block}.lg\:aspect-auto{aspect-ratio:auto}.lg\:w-\[438px\]{width:438px}.lg\:max-w-4xl{max-width:var(--container-4xl)}.lg\:grow{flex-grow:1}.lg\:flex-row{flex-direction:row}.lg\:justify-center{justify-content:center}.lg\:rounded-t-none{border-top-left-radius:0;border-top-right-radius:0}.lg\:rounded-tl-lg{border-top-left-radius:var(--radius-lg)}.lg\:rounded-r-lg{border-top-right-radius:var(--radius-lg);border-bottom-right-radius:var(--radius-lg)}.lg\:rounded-br-none{border-bottom-right-radius:0}.lg\:p-8{padding:calc(var(--spacing)*8)}.lg\:p-20{padding:calc(var(--spacing)*20)}}@media (prefers-color-scheme:dark){.dark\:block{display:block}.dark\:hidden{display:none}.dark\:border-\[\#3E3E3A\]{border-color:#3e3e3a}.dark\:border-\[\#eeeeec\]{border-color:#eeeeec}.dark\:bg-\[\#0a0a0a\]{background-color:#0a0a0a}.dark\:bg-\[\#1D0002\]{background-color:#1d0002}.dark\:bg-\[\#3E3E3A\]{background-color:#3e3e3a}.dark\:bg-\[\#161615\]{background-color:#161615}.dark\:bg-\[\#eeeeec\]{background-color:#eeeeec}.dark\:text-\[\#1C1C1A\]{color:#1c1c1a}.dark\:text-\[\#A1A09A\]{color:#a1a09a}.dark\:text-\[\#EDEDEC\]{color:#ededec}.dark\:text-\[\#F61500\]{color:#f61500}.dark\:text-\[\#FF4433\]{color:#f43}.dark\:shadow-\[inset_0px_0px_0px_1px_\#fffaed2d\]{--tw-shadow:inset 0px 0px 0px 1px var(--tw-shadow-color,#fffaed2d);box-shadow:var(--tw-inset-shadow),var(--tw-inset-ring-shadow),var(--tw-ring-offset-shadow),var(--tw-ring-shadow),var(--tw-shadow)}.dark\:before\:border-\[\#3E3E3A\]:before{content:var(--tw-content);border-color:#3e3e3a}@media (hover:hover){.dark\:hover\:border-\[\#3E3E3A\]:hover{border-color:#3e3e3a}.dark\:hover\:border-\[\#62605b\]:hover{border-color:#62605b}.dark\:hover\:border-white:hover{border-color:var(--color-white)}.dark\:hover\:bg-white:hover{background-color:var(--color-white)}}}@starting-style{.starting\:translate-y-4{--tw-translate-y:calc(var(--spacing)*4);translate:var(--tw-translate-x)var(--tw-translate-y)}}@starting-style{.starting\:translate-y-6{--tw-translate-y:calc(var(--spacing)*6);translate:var(--tw-translate-x)var(--tw-translate-y)}}@starting-style{.starting\:opacity-0{opacity:0}}}@keyframes spin{to{transform:rotate(360deg)}}@keyframes ping{75%,to{opacity:0;transform:scale(2)}}@keyframes pulse{50%{opacity:.5}}@keyframes bounce{0%,to{animation-timing-function:cubic-bezier(.8,0,1,1);transform:translateY(-25%)}50%{animation-timing-function:cubic-bezier(0,0,.2,1);transform:none}}@property --tw-translate-x{syntax:"*";inherits:false;initial-value:0}@property --tw-translate-y{syntax:"*";inherits:false;initial-value:0}@property --tw-translate-z{syntax:"*";inherits:false;initial-value:0}@property --tw-rotate-x{syntax:"*";inherits:false;initial-value:rotateX(0)}@property --tw-rotate-y{syntax:"*";inherits:false;initial-value:rotateY(0)}@property --tw-rotate-z{syntax:"*";inherits:false;initial-value:rotateZ(0)}@property --tw-skew-x{syntax:"*";inherits:false;initial-value:skewX(0)}@property --tw-skew-y{syntax:"*";inherits:false;initial-value:skewY(0)}@property --tw-space-x-reverse{syntax:"*";inherits:false;initial-value:0}@property --tw-border-style{syntax:"*";inherits:false;initial-value:solid}@property --tw-leading{syntax:"*";inherits:false}@property --tw-font-weight{syntax:"*";inherits:false}@property --tw-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-shadow-color{syntax:"*";inherits:false}@property --tw-inset-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-inset-shadow-color{syntax:"*";inherits:false}@property --tw-ring-color{syntax:"*";inherits:false}@property --tw-ring-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-inset-ring-color{syntax:"*";inherits:false}@property --tw-inset-ring-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-ring-inset{syntax:"*";inherits:false}@property --tw-ring-offset-width{syntax:"<length>";inherits:false;initial-value:0}@property --tw-ring-offset-color{syntax:"*";inherits:false;initial-value:#fff}@property --tw-ring-offset-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-blur{syntax:"*";inherits:false}@property --tw-brightness{syntax:"*";inherits:false}@property --tw-contrast{syntax:"*";inherits:false}@property --tw-grayscale{syntax:"*";inherits:false}@property --tw-hue-rotate{syntax:"*";inherits:false}@property --tw-invert{syntax:"*";inherits:false}@property --tw-opacity{syntax:"*";inherits:false}@property --tw-saturate{syntax:"*";inherits:false}@property --tw-sepia{syntax:"*";inherits:false}@property --tw-drop-shadow{syntax:"*";inherits:false}@property --tw-duration{syntax:"*";inherits:false}@property --tw-content{syntax:"*";inherits:false;initial-value:""}
            </style>
        @endif
    </head>
    <body class="bg-[#fbf6f5]">
        <div id="mobile-menu"
            class="fixed z-9999 inset-0 w-full h-[100dvh] bg-[#fff] p-[1rem] flex flex-col">
            <div class="w-full flex items-center justify-between pb-[1rem] border-b border-gray-300 mb-8 shrink-0">
                <a href="/">
                    <img src="{{ asset('/images/deluxenailshop_transp_zwart_v1.png') }}" class="max-h-[4rem]">
                </a>
                <svg id="mobile-menu-close" class="-mr-2 cursor-pointer" width="40px" height="40px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g id="Menu / Close_SM">
                    <path id="Vector" d="M16 16L12 12M12 12L8 8M12 12L16 8M12 12L8 16" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>
                </svg>
            </div>
            <div class="grid grid-cols-2 gap-8 flex-1 overflow-y-auto min-h-0 md:min-h-0 [--tw-scroll:touch] scroll-area pr-1">
                <ul class="text-black">
                    <li><a href="/" class="font-medium py-2 text-sm">Home</a></li>
                </ul>
                <ul class="text-black">
                    <li><a href="/faq" class="font-medium py-2 text-sm">FAQ</a></li>
                </ul>
                <ul class="text-black"><li><a href="{{ $catUrl('Top Coat') }}" class="font-medium py-2 text-sm">Top Coat</a></li></ul>
                <ul class="text-black"><li><a href="{{ $catUrl('Acrylgel') }}" class="font-medium py-2 text-sm">Acrylgel</a></li></ul>
                <ul class="text-black"><li><a href="{{ $catUrl('Builder in a Bottle') }}" class="font-medium py-2 text-sm">Builder in a Bottle</a></li></ul>
                <ul></ul>
                <ul class="text-black">
                    <li class="font-bold text-lg mb-1">Gel</li>
                    <li><a href="{{ $subUrl('Builder Gel') }}" class="font-medium py-2 text-sm">Builder Gel</a></li>
                    <li><a href="{{ $subUrl('Builder Love Story Gel') }}" class="font-medium py-2 text-sm">Builder Love Story Gel</a></li>
                    <li><a href="{{ $subUrl('Builder Dream Gel') }}" class="font-medium py-2 text-sm">Builder Dream Gel</a></li>
                    <li><a href="{{ $subUrl('Jelly Gelly Gel') }}" class="font-medium py-2 text-sm">Jelly Gelly Gel</a></li>
                    <li><a href="{{ $subUrl('Liquid Gel') }}" class="font-medium py-2 text-sm">Liquid Gel</a></li>
                </ul>
                <ul class="text-black">
                    <li class="font-bold text-lg mb-1">Liquids</li>
                    <li><a href="{{ $subUrl('Prep') }}" class="font-medium py-2 text-sm">Prep</a></li>
                    <li><a href="{{ $subUrl('Cuticle Oil') }}" class="font-medium py-2 text-sm">Cuticle Oil</a></li>
                    <li><a href="{{ $subUrl('Cuticle Remover') }}" class="font-medium py-2 text-sm">Cuticle Remover</a></li>
                    <li><a href="{{ $subUrl('3 in 1 Nail Prep & Cleanser') }}" class="font-medium py-2 text-sm">3 in 1 Nail Prep &amp; Cleanser</a></li>
                    <li><a href="{{ $subUrl('Remover') }}" class="font-medium py-2 text-sm">Remover</a></li>
                </ul>
                <ul class="text-black">
                    <li class="font-bold text-lg mb-1">Base Coat</li>
                    <li><a href="{{ $subUrl('Rubber Base') }}" class="font-medium py-2 text-sm">Rubber Base</a></li>
                    <li><a href="{{ $subUrl('Cold Base') }}" class="font-medium py-2 text-sm">Cold Base</a></li>
                </ul>
                <ul class="text-black">
                    <li class="font-bold text-lg mb-1">Gelpolish</li>
                    <li><a href="{{ $subUrl('Flash') }}" class="font-medium py-2 text-sm">Flash</a></li>
                    <li><a href="{{ $subUrl('Color') }}" class="font-medium py-2 text-sm">Color</a></li>
                </ul>
                <ul class="text-black">
                    <li class="font-bold text-lg mb-1">Nail Art</li>
                    <li><a href="{{ $subUrl('Metalic Gel') }}" class="font-medium py-2 text-sm">Metalic Gel</a></li>
                    <li><a href="{{ $subUrl('Gypsum') }}" class="font-medium py-2 text-sm">Gypsum</a></li>
                    <li><a href="{{ $subUrl('Ombre Spray') }}" class="font-medium py-2 text-sm">Ombre Spray</a></li>
                </ul>
                <ul class="text-black">
                    <li class="font-bold text-lg mb-1">Werkmateriaal</li>
                    <li><a href="{{ $subUrl('Top Nail Forms') }}" class="font-medium py-2 text-sm">Top Nail Forms</a></li>
                    <li><a href="{{ $subUrl('Gel Tips') }}" class="font-medium py-2 text-sm">Gel Tips</a></li>
                    <li><a href="{{ $subUrl('Penselen') }}" class="font-medium py-2 text-sm">Penselen</a></li>
                    <li><a href="{{ $subUrl('Schort') }}" class="font-medium py-2 text-sm">Schort</a></li>
                </ul>
            </div>
        </div>
        <div class="fixed top-0 left-0 z-999 w-full">
            <div class="px-4 pt-4">
                <div class="w-full h-auto py-[0.5rem] bg-[#b38867]/75 backdrop-blur border border-[#a0795c] rounded-3xl">
                    <div class="max-w-[1100px] mx-auto h-full flex items-center justify-center">
                        <p class="text-white font-regular text-sm flex flex-col md:flex-row items-center">
                            Verzendkosten: Nederland €6,35 | België €12,35 <span class="text-xs p-[0.35rem] w-fit bg-[#947054] rounded-sm ml-4">Gratis verzending vanaf: NL 75,- / BE 100,-</span>
                        <p>
                    </div>
                </div>
            </div>
            <div class="w-full h-auto py-[1rem] px-[1rem]">
                <div id="menu-bar" class="w-full px-[1rem] md:px-[2rem] py-2 rounded-3xl mx-auto h-full flex items-center justify-between transition duration-300">
                    <div class="flex items-center gap-6">
                        <a href="/" class="relative">
                            <img src="{{ asset('/images/deluxenailshop_transp_wit_v1.png') }}" id="logo-white" class="max-h-[4rem] transition duration-300">
                            <img src="{{ asset('/images/deluxenailshop_transp_zwart_v1.png') }}" id="logo-black" class="max-h-[4rem] absolute top-0 -ml-[0.4px] transition duration-300 opacity-0">
                        </a>
                        <div id="mobile-menu-open" class="md:hidden cursor-pointer w-6 h-4 flex flex-col justify-between">
                            <div class="w-full h-[2px] bg-[#b38867]"></div>
                            <div class="w-full h-[2px] bg-[#b38867]"></div>
                            <div class="w-[80%] h-[2px] bg-[#b38867]"></div>
                        </div>
                        <ul class="md:flex items-center gap-[2rem] hidden">
                        <!-- Home -->
                        <li>
                            <a href="/" class="menu-item text-[15px] font-medium rounded-sm hover:text-[#ebe2db] text-white transition">Home</a>
                        </li>

                        <!-- Base Coat (submenu) -->
                        <li class="relative group">
                            <a href="{{ $catUrl('Base Coat') }}" class="menu-item text-[15px] font-medium rounded-sm text-white hover:text-[#ebe2db] group-hover:text-[#ebe2db] transition flex items-center gap-2">
                                Base Coat <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            </a>
                            <div class="invisible opacity-0 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100 transition absolute left-0 top-full -mt-2 pt-4 z-40">
                                <ul class="min-w-[220px] rounded-xl border border-gray-200 bg-white shadow-lg p-2">
                                <li><a href="{{ $subUrl('Rubber Base') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Rubber Base</a></li>
                                <li><a href="{{ $subUrl('Cold Base') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Cold Base</a></li>
                                </ul>
                            </div>
                        </li>

                        <!-- Top Coat (geen submenu) -->
                        <li>
                            <a href="{{ $catUrl('Top Coat') }}" class="menu-item text-[15px] font-medium rounded-sm hover:text-[#ebe2db] text-white transition">Top Coat</a>
                        </li>

                        <!-- Acrylgel (geen submenu) -->
                        <li>
                            <a href="{{ $catUrl('Acrylgel') }}" class="menu-item text-[15px] font-medium rounded-sm hover:text-[#ebe2db] text-white transition">Acrylgel</a>
                        </li>

                        <!-- Gel (submenu) -->
                        <li class="relative group">
                            <a href="{{ $catUrl('Gel') }}" class="menu-item text-[15px] font-medium rounded-sm text-white hover:text-[#ebe2db] group-hover:text-[#ebe2db] transition flex items-center gap-2">
                                Gel <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            </a>
                            <div class="invisible opacity-0 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100 transition absolute left-0 top-full -mt-2 pt-4 z-40">
                                <ul class="min-w-[260px] rounded-xl border border-gray-200 bg-white shadow-lg p-2">
                                <li><a href="{{ $subUrl('Builder Gel') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Builder Gel</a></li>
                                <li><a href="{{ $subUrl('Builder Love Story Gel') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Builder Love Story Gel</a></li>
                                <li><a href="{{ $subUrl('Builder Dream Gel') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Builder Dream Gel</a></li>
                                <li><a href="{{ $subUrl('Jelly Gelly Gel') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Jelly Gelly Gel</a></li>
                                <li><a href="{{ $subUrl('Liquid Gel') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Liquid Gel</a></li>
                                </ul>
                            </div>
                        </li>

                        <!-- Builder in a Bottle (geen submenu) -->
                        <li>
                            <a href="{{ $catUrl('Builder in a Bottle') }}" class="menu-item text-[15px] font-medium rounded-sm text-white hover:text-[#ebe2db] transition">Builder in a Bottle</a>
                        </li>

                        <!-- Gelpolish (submenu) -->
                        <li class="relative group">
                            <a href="{{ $catUrl('Gelpolish') }}" class="menu-item text-[15px] font-medium rounded-sm hover:text-[#ebe2db] group-hover:text-[#ebe2db] transition text-white flex items-center gap-2">
                                Gelpolish <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            </a>
                            <div class="invisible opacity-0 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100 transition absolute left-0 top-full -mt-2 pt-4 z-40">
                                <ul class="min-w-[220px] rounded-xl border border-gray-200 bg-white shadow-lg p-2">
                                <li><a href="{{ $subUrl('Flash') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Flash</a></li>
                                <li><a href="{{ $subUrl('Color') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Color</a></li>
                                </ul>
                            </div>
                        </li>

                        <!-- Liquids (submenu) -->
                        <li class="relative group">
                            <a href="{{ $catUrl('Liquids') }}" class="menu-item text-[15px] font-medium rounded-sm hover:text-[#ebe2db] group-hover:text-[#ebe2db] transition text-white flex items-center gap-2">
                                Liquids <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            </a>
                            <div class="invisible opacity-0 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100 transition absolute left-0 top-full -mt-2 pt-4 z-40">
                                <ul class="min-w-[280px] rounded-xl border border-gray-200 bg-white shadow-lg p-2">
                                <li><a href="{{ $subUrl('Prep') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Prep</a></li>
                                <li><a href="{{ $subUrl('Cuticle Oil') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Cuticle Oil</a></li>
                                <li><a href="{{ $subUrl('Cuticle Remover') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Cuticle Remover</a></li>
                                <li><a href="{{ $subUrl('3 in 1 Nail Prep & Cleanser') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">3 in 1 Nail Prep &amp; Cleanser</a></li>
                                <li><a href="{{ $subUrl('Remover') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Remover</a></li>
                                </ul>
                            </div>
                        </li>

                        <!-- Nail Art (submenu) -->
                        <li class="relative group">
                            <a href="{{ $catUrl('Nail Art') }}" class="menu-item text-[15px] font-medium rounded-sm hover:text-[#ebe2db] text-white group-hover:text-[#ebe2db] transition flex items-center gap-2">
                                Nail Art <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            </a>
                            <div class="invisible opacity-0 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100 transition absolute left-0 top-full -mt-2 pt-4 z-40">
                                <ul class="min-w-[220px] rounded-xl border border-gray-200 bg-white shadow-lg p-2">
                                <li><a href="{{ $subUrl('Metalic Gel') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Metalic Gel</a></li>
                                <li><a href="{{ $subUrl('Gypsum') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Gypsum</a></li>
                                <li><a href="{{ $subUrl('Ombre Spray') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Ombre Spray</a></li>
                                </ul>
                            </div>
                        </li>

                        <!-- Werkmateriaal (submenu) -->
                        <li class="relative group">
                            <a href="{{ $catUrl('Werkmateriaal') }}" class="menu-item text-[15px] font-medium rounded-sm hover:text-[#ebe2db] group-hover:text-[#ebe2db] transition flex items-center gap-2 text-white">
                                Werkmateriaal <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            </a>
                            <div class="invisible opacity-0 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100 transition absolute left-0 top-full -mt-2 pt-4 z-40">
                                <ul class="min-w-[240px] rounded-xl border border-gray-200 bg-white shadow-lg p-2">
                                <li><a href="{{ $subUrl('Top Nail Forms') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Top Nail Forms</a></li>
                                <li><a href="{{ $subUrl('Gel Tips') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Gel Tips</a></li>
                                <li><a href="{{ $subUrl('Penselen') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Penselen</a></li>
                                <li><a href="{{ $subUrl('Schort') }}" class="block px-3 py-2 text-[14px] hover:bg-gray-50 rounded-lg">Schort</a></li>
                                </ul>
                            </div>
                        </li>

                        <!-- FAQ -->
                        <li>
                            <a href="/faq" class="menu-item text-[15px] font-medium rounded-sm hover:text-[#ebe2db] text-white transition">FAQ</a>
                        </li>
                        </ul>
                    </div>
                    <div class="flex justify-end items-center gap-[1rem]">
                        <div class="flex items-center gap-[1rem]">
                            <div x-data="productSearch()" class="relative">
                                <button type="button"
                                        @click="toggle()"
                                        :aria-expanded="open"
                                        aria-controls="product-search-popover"
                                        class="flex items-center cursor-pointer"
                                        title="Zoeken">
                                    <i class="menu-item fa-solid fa-magnifying-glass fa-md text-white hover:text-[#ebe2db] transition"></i>
                                </button>
                                <!-- Popover -->
                                <div x-cloak x-show="open" x-transition
                                    @click.outside="close()" @keydown.escape.prevent.stop="close()"
                                    id="product-search-popover"
                                    class="fixed inset-0 w-full h-screen z-50
                                            md:absolute md:inset-auto md:right-0 md:top-auto md:mt-2 md:w-[420px] md:h-auto">

                                <div class="bg-white h-full rounded-none shadow-none border-0
                                            md:rounded-xl md:shadow-xl md:border md:border-gray-100
                                            flex flex-col">

                                    <!-- Mobile header (alleen op mobiel zichtbaar) -->
                                    <div class="md:hidden flex items-center justify-between border-b border-gray-200 h-12 px-4">
                                    <p class="text-sm font-medium text-gray-900">Zoek producten</p>
                                    <button type="button" @click="close()" class="p-2 -mr-2 text-gray-500 hover:text-gray-700">
                                        <i class="fa-solid fa-xmark text-lg"></i>
                                    </button>
                                    </div>

                                    <!-- Body -->
                                    <div class="p-3 md:p-3 flex flex-col h-full md:h-auto">

                                    <!-- Input -->
                                    <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3">
                                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                                        <input
                                        x-ref="input"
                                        x-model.debounce.200ms="q"
                                        @input="onInput()"
                                        @keydown.arrow-down.prevent="move(1)"
                                        @keydown.arrow-up.prevent="move(-1)"
                                        @keydown.enter.prevent="goHighlighted()"
                                        type="text"
                                        placeholder="Zoek producten..."
                                        class="w-full h-10 outline-none text-[14px]" />
                                        <button x-show="q.length" @click="clear()" type="button" class="text-gray-400 hover:text-gray-600">
                                        <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>

                                    <!-- Resultaten -->
                                    <div class="mt-2 overflow-y-auto flex-1 md:max-h-[320px] md:flex-none">
                                        <template x-if="loading">
                                        <div class="px-3 py-3 text-sm text-gray-500">Zoeken…</div>
                                        </template>

                                        <template x-if="!loading && q.length && results.length === 0">
                                        <div class="px-3 py-3 text-sm text-gray-500">Geen resultaten</div>
                                        </template>

                                        <ul role="listbox" aria-label="Zoekresultaten" class="divide-y divide-gray-50">
                                        <template x-for="(p, i) in results" :key="p.id">
                                            <li :id="`res-${i}`"
                                                :class="i === highlight ? 'bg-gray-50' : ''"
                                                class="cursor-pointer">
                                            <a @mouseenter="highlight = i"
                                                @click.prevent="go(p)"
                                                class="flex items-center gap-3 px-3 py-2">
                                                <img x-show="p.foto" :src="p.foto" alt="" class="w-10 h-10 object-cover rounded hidden md:block">
                                                <div class="min-w-0">
                                                <p class="text-[14px] font-medium text-gray-900" x-text="p.naam"></p>
                                                <p class="text-[12px] text-gray-500 truncate" x-show="p.categorie" x-text="p.categorie"></p>
                                                </div>
                                            </a>
                                            </li>
                                        </template>
                                        </ul>
                                    </div>

                                    <!-- Footer -->
                                    <div class="mt-2 flex items-center justify-end text-[11px] text-gray-500">
                                        <button class="hover:underline hidden md:inline" @click="close()">Sluiten (Esc)</button>
                                    </div>
                                    </div>
                                </div>
                                </div>

                            </div>
                            <a href="{{ route('winkelwagen.index') }}" class="relative group">
                                <i class="menu-item fa-solid fa-cart-shopping fa-md text-white hover:text-[#ebe2db] transition"></i>
                                @php $aantal = collect(session('cart'))->sum('aantal'); @endphp
                                @if($aantal > 0)
                                    <span class="absolute -top-2 -right-4 text-[#b38867] font-semibold text-[11px] rounded-full px-2 py-[1px]">{{ $aantal }}</span>
                                @endif
                            </a>
                            @auth
                                <a href="/beheer" class="text-sm text-[#fff] px-4 py-1 rounded-md bg-[#b38867] font-normal">
                                    Beheer
                                </a>
                            @else
                                <a href="/inloggen" class="flex items-center">
                                    <i class="menu-item fa-solid fa-user fa-md text-white hover:text-[#ebe2db] transition"></i>
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @yield('content')
        <div class="bg-[#b38867]/50 py-[5rem]">
                <!-- Elfsight Instagram Feed | Untitled Instagram Feed -->
                <script src="https://elfsightcdn.com/platform.js" async></script>
                <div class="elfsight-app-cea9b329-d2db-44f8-b738-104a7be55fce" data-elfsight-app-lazy></div>
        </div>
        <footer class="bg-[#b38867]">
            <div class="max-w-[1100px] px-[1rem] md:px-[3rem] mx-auto py-[5rem] flex flex-col md:flex-row gap-16">
                <div class="w-full md:w-1/3">
                    <img src="{{ asset('/images/deluxenailshop_transp_wit_v1.png') }}" class="max-h-[6rem] mb-4">
                    <p class="opacity-80 text-[#fff] text-sm">Ontdek hoogwaardige nagelproducten voor thuis of in de salon. Van gelpolish tot nail art. Alles wat je nodig hebt om jouw droomnagels te creëren, vind je hier.</p>
                </div>
                <div class="flex flex-col md:flex-row justify-between gap-8 w-full md:w-2/3">
                    <div>
                        <h4 class="text-[#fff] font-semibold text-xl mb-3">Navigatie</h4>
                        <ul class="space-y-2 text-sm text-[#fff]">
                            <li><a href="/" class="hover:underline">Home</a></li>
                            <li><a href="/producten" class="hover:underline">Producten</a></li>
                            <li><a href="/faq" class="hover:underline">FAQ</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-[#fff] font-semibold text-xl mb-3">Contact</h4>
                        <ul class="space-y-4 text-sm text-[#fff]">
                            <div class="space-y-2">
                                <li>Lentemorgen 5</li>
                                <li>6903CT Zevenaar</li>
                                <li>Kamer 5.36</li>
                            </div>
                            <div class="space-y-2">
                                <li>Email: <a href="mailto:info@deluxenailshop.nl" class="hover:underline">info@deluxenailshop.nl</a></li>
                                <li>KVK: 84373466</li>
                                <li>BTW: NL003954592B82</li>
                            </div>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="max-w-[1100px] mx-auto py-[1.5rem] flex flex-col md:flex-row items-center justify-between border-t-1 border-white/25">
                <div class="text-center text-xs text-white py-4">
                    © {{ date('Y') }} DeLuxe Nailshop – Alle rechten voorbehouden
                </div>
                <div class="text-center text-xs text-white py-4">
                    Gemaakt door <a href="https://www.halfmanmedia.nl" target="_blank" class="hover:underline">HalfmanMedia</a>
                </div>
            </div>
        </footer>
        <script>
            var mobileMenu = document.getElementById('mobile-menu');
            var mobileMenuOpen = document.getElementById('mobile-menu-open');
            var mobileMenuClose = document.getElementById('mobile-menu-close');

            mobileMenuOpen.addEventListener('click', function() {
                mobileMenu.classList.add('active');
                document.querySelector('body').classList.add('overflow-y-hidden');
            })
            mobileMenuClose.addEventListener('click', function() {
                mobileMenu.classList.remove('active');
                document.querySelector('body').classList.remove('overflow-y-hidden');
            })
        </script>
        <script>
            // Zorg dat Alpine v3 geladen is in je layout
            function productSearch() {
                return {
                open: false,
                q: '',
                results: [],
                loading: false,
                highlight: -1,
                toggle() {
                    this.open = !this.open;
                    if (this.open) this.$nextTick(() => this.$refs.input.focus());
                },
                close() { this.open = false; this.highlight = -1; },
                clear() { this.q = ''; this.results = []; this.highlight = -1; this.$refs.input.focus(); },
                onInput() {
                    if (this.q.trim().length < 1) { this.results = []; this.highlight = -1; return; }
                    this.search(this.q.trim());
                },
                async search(q) {
                    this.loading = true;
                    try {
                    const res = await fetch(`/zoek/producten?q=${encodeURIComponent(q)}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    // verwacht: [{id, naam, slug?, foto?, categorie?}]
                    this.results = Array.isArray(data) ? data : [];
                    this.highlight = this.results.length ? 0 : -1;
                    } catch (e) {
                    console.error('Zoekfout', e);
                    this.results = [];
                    this.highlight = -1;
                    } finally {
                    this.loading = false;
                    }
                },
                move(step) {
                    if (!this.results.length) return;
                    this.highlight = (this.highlight + step + this.results.length) % this.results.length;
                },
                goHighlighted() {
                    if (this.highlight < 0 || !this.results[this.highlight]) return;
                    this.go(this.results[this.highlight]);
                },
                go(p) {
                    // Bepaal je product URL (slug als je die hebt)
                    const url = p.slug ? `/producten/${p.slug}` : `/producten/${p.id}`;
                    window.location.href = url;
                }
                }
            }
        </script>
        <script>
            (function () {
                const menuBar   = document.getElementById('menu-bar');
                const menuItems = document.querySelectorAll('.menu-item');
                const logoWhite = document.getElementById('logo-white');
                const logoBlack = document.getElementById('logo-black');
                if (!menuBar) return;

                function setMenuScrolled(scrolled) {
                    menuBar.classList.toggle('bg-white', scrolled);
                    menuBar.classList.toggle('shadow-lg', scrolled);

                    // Wissel logo's (als ze bestaan)
                    if (logoWhite) logoWhite.classList.toggle('opacity-0', scrolled);
                    if (logoBlack) logoBlack.classList.toggle('opacity-0', !scrolled);

                    // Pas alle menu items aan
                    menuItems.forEach((el) => {
                    if (scrolled) {
                        el.classList.add('text-black', 'hover:text-gray-600');
                        el.classList.remove('text-white', 'hover:text-[#ebe2db]', 'group-hover:text-[#ebe2db]');
                    } else {
                        el.classList.add('text-white', 'hover:text-[#ebe2db]');
                        el.classList.remove('text-black', 'hover:text-gray-600');
                    }
                    });
                }

                function onScroll() {
                    const y = window.scrollY || window.pageYOffset || document.documentElement.scrollTop || 0;
                    setMenuScrolled(y > 50);
                }

                // init & listen
                onScroll();
                window.addEventListener('scroll', onScroll, { passive: true });
            })();
        </script>
    </body>
</html>
