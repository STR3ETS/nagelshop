@extends('layouts.pages')
@section('content')
<div class="w-full h-[250px] overflow-y-hidden flex items-center relative bg-cover bg-center bg-[url(https://i.imgur.com/99DomHP.jpeg)]">
    <div class="w-full h-full absolute z-[1] bg-[#00000050]"></div>
    <div class="absolute z-[3] max-w-[1100px] left-0 right-0 ml-auto mr-auto">
        <h1 class="text-white text-[50px] font-bold leading-[1.15]">Onze <i class="instrument-serif-font text-[#ff64ba]">producten</i><br></h1>
    </div>
</div>
<div class="w-full h-auto relative">
    <div class="max-w-[1100px] mx-auto py-[5rem] flex gap-8">
        <!-- Sidebar: categorieën -->
        <div id="categorieSidebar" class="w-1/4 bg-white rounded-lg p-[1.5rem] h-fit border-1 border-gray-100">
            <h2 class="text-lg font-semibold text-[#ff64ba] mb-4">Categorieën</h2>
            <form method="GET" action="{{ route('producten.index') }}" class="space-y-2" id="categorie-filter" data-turbo="false">
                <div x-data class="space-y-2">
                    @foreach($alleCategories as $categorie)
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                name="categorie[]"
                                value="{{ $categorie->id }}"
                                id="cat-{{ $categorie->id }}"
                                class="accent-[#ff64ba]"
                                {{ in_array($categorie->id, request()->get('categorie', [])) ? 'checked' : '' }}
                                @change="$nextTick(() => document.getElementById('categorie-filter').requestSubmit())"
                            >
                            <label for="cat-{{ $categorie->id }}" class="text-sm text-[#343434]">{{ $categorie->naam }}</label>
                        </div>
                    @endforeach
                </div>
                <div class="pt-4 flex flex-col gap-2">
                    <a href="{{ route('producten.index') }}" class="text-sm text-gray-500 hover:underline">Reset alle filters</a>
                </div>
            </form>
        </div>
        <!-- Cards: producten -->
        <div class="w-3/4 grid grid-cols-3 gap-[1rem] h-fit">
            @foreach($producten as $product)
                <div class="bg-white p-[1.5rem] rounded-lg flex flex-col h-full border-1 border-gray-100">
                    <!-- Afbeelding -->
                    <div class="w-full aspect-square overflow-hidden border border-gray-200 rounded-lg p-[1rem]">
                        <img src="{{ asset('storage/producten/' . $product->foto) }}" alt="{{ $product->naam }}" class="w-full h-full object-cover">
                    </div>
                    <!-- Inhoud -->
                    <div class="flex flex-col justify-between flex-1 mt-4">
                        <div class="flex flex-col gap-[0.5rem]">
                            <h2 class="text-[16px] font-medium">{{ $product->naam }}</h2>

                            <div x-data="{ expanded: false }" class="text-[#191919] opacity-80 text-[15px]">
                                <p>
                                    <template x-if="!expanded">
                                        <span x-transition.opacity.duration.300ms>{{ Str::limit($product->beschrijving, 100, '...') }}</span>
                                    </template>
                                    <template x-if="expanded">
                                        <span x-transition.opacity.duration.300ms>{{ $product->beschrijving }}</span>
                                    </template>
                                </p>

                                @if(strlen($product->beschrijving) > 100)
                                    <button @click="expanded = !expanded" class="text-sm text-[#ff64ba] hover:underline mt-1 cursor-pointer">
                                        <span x-text="expanded ? 'Minder tonen' : 'Meer lezen'"></span>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <!-- Prijs en button onderaan -->
                        <div class="mt-4">
                            <p class="text-[#191919] opacity-80 text-[15px] mb-2">€{{ number_format($product->prijs, 2, ',', '.') }}</p>
                            <form action="{{ route('winkelwagen.toevoegen', $product) }}" method="POST" class="toevoegen-form" data-product-id="{{ $product->id }}">
                                @csrf
                                <button type="submit" class="cursor-pointer w-full py-[0.4rem] bg-[#ff64ba] hover:bg-[#96366c] transition rounded-md text-white text-[15px] font-medium flex items-center justify-center gap-2">
                                    <lord-icon
                                        src="https://cdn.lordicon.com/pbrgppbb.json"
                                        trigger="hover"
                                        colors="primary:#ffffff"
                                        style="width:20px;height:20px">
                                    </lord-icon>
                                    Toevoegen
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div id="overlay" class="fixed z-50 flex items-center justify-center bottom-4 right-4 hidden opacity-0 translate-y-4 transition-all duration-500">
        <div class="bg-white p-8 rounded-lg w-[350px] border-1 border-gray-200 shadow-lg">
            <h2 class="text-[#191919] text-[22px] font-semibold leading-[1.15] mb-4">
                Product is toegevoegd <br>aan <i class="instrument-serif-font text-[#ff64ba]">jouw winkelwagen</i>
            </h2>
            <div class="flex justify-between gap-4">
                <a href="/producten" class="flex-1 py-2 bg-gray-200 hover:bg-gray-300 text-center rounded text-sm">Verder winkelen</a>
                <a href="{{ route('winkelwagen.index') }}" class="flex-1 py-2 bg-[#ff64ba] hover:bg-[#e652a7] text-white text-center rounded text-sm">Afrekenen</a>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        gsap.registerPlugin(ScrollTrigger);

        const sidebar = document.getElementById('categorieSidebar');

        ScrollTrigger.create({
            trigger: sidebar,
            start: 'top 26px', // begint sticky na 100px scroll
            endTrigger: '.grid', // einde is bij product-grid (pas aan indien nodig)
            end: 'bottom bottom',
            pin: true,
            pinSpacing: false,
            markers: false // zet op true voor debugging
        });



        const overlay = document.getElementById('overlay');

        document.querySelectorAll('.toevoegen-form').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(this);
                const action = this.getAttribute('action');

                fetch(action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: formData
                })
                .then(response => {
                    if (response.ok) {
                        // Forceer startpositie en toon popup
                        overlay.classList.remove('hidden');
                        overlay.classList.add('opacity-0', 'translate-y-4');

                        // Animatie forceren na micro-delay
                        setTimeout(() => {
                            overlay.classList.remove('opacity-0', 'translate-y-4');
                        }, 10);

                        // Verberg popup na 3s met fade-out
                        setTimeout(() => {
                            overlay.classList.add('opacity-0', 'translate-y-4');
                            setTimeout(() => overlay.classList.add('hidden'), 500);
                        }, 3000);
                    } else {
                        alert('Er ging iets mis bij het toevoegen.');
                    }
                });
            });
        });
    });
</script>
@endsection