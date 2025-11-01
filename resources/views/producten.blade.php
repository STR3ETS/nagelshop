@extends('layouts.pages')
@section('content')
<style>[x-cloak]{ display:none !important; }</style>
@php use Illuminate\Support\Str; @endphp
<div class="p-2 h-[350px] md:h-auto">
  <div class="w-full h-full md:h-[350px] overflow-y-hidden rounded-3xl flex items-end relative bg-cover bg-center bg-[url(https://i.imgur.com/99DomHP.jpeg)]">
      <div class="w-full h-full absolute z-[1] bg-[#00000050]"></div>
      <div class="absolute z-[3] max-w-[1100px] px-[1rem] md:px-[3rem] left-0 right-0 ml-auto mr-auto pb-8">
          <h1 class="text-white text-[34px] md:text-[50px] font-bold leading-[1.15] text-center md:text-start">Onze <i class="instrument-serif-font text-[#fff]">producten</i><br></h1>
      </div>
  </div>
</div>
<div class="w-full h-auto relative">
    <div class="max-w-[1100px] px-[1rem] md:px-[3rem] mx-auto py-[5rem] flex flex-col md:flex-row gap-8">
        <!-- Sidebar: categorieën (mobiel inklapbaar) -->
        <div id="categorieSidebar"
            x-data="{ open:false }"
            class="w-full md:w-1/4 bg-white rounded-lg p-[1.5rem] h-fit border-1 border-gray-100">

          <!-- Header + mobiele toggle -->
          <div class="flex items-center justify-between md:block">
            <h2 class="text-lg font-semibold text-[#b38867] mb-0 md:mb-4">Categorieën</h2>

            <!-- Alleen tonen op mobiel -->
            <button type="button"
                    class="md:hidden inline-flex items-center gap-2 text-sm text-[#343434] px-3 py-2 rounded-lg border border-gray-200"
                    @click="open = !open"
                    :aria-expanded="open.toString()"
                    aria-controls="categorie-filter-wrap">
              <span x-text="open ? 'Verberg filters' : 'Toon filters'"></span>
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 transition-transform"
                  :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 9l-7 7-7-7" />
              </svg>
            </button>
          </div>

          <!-- Wrapper om netjes te kunnen togglen op mobiel, altijd zichtbaar op >= md -->
          <div id="categorie-filter-wrap"
              x-cloak
              class="mt-4 md:mt-0 md:block"
              x-show="open"
              x-init="if (window.matchMedia('(min-width:768px)').matches) open = true"
              @resize.window="if (window.matchMedia('(min-width:768px)').matches) open = true"
              x-transition>

            <form method="GET"
                  action="{{ route('producten.index') }}"
                  class="space-y-2 max-h-[50vh] overflow-y-auto"
                  id="categorie-filter"
                  data-turbo="false">
              <div x-data class="space-y-2">
                @foreach($alleCategories as $categorie)
                  <div class="flex items-center gap-2">
                    <input
                      type="checkbox"
                      name="categorie[]"
                      value="{{ $categorie->id }}"
                      id="cat-{{ $categorie->id }}"
                      class="accent-[#b38867]"
                      {{ in_array($categorie->id, request()->get('categorie', [])) ? 'checked' : '' }}
                      @change="$nextTick(() => document.getElementById('categorie-filter').requestSubmit())"
                    >
                    <label for="cat-{{ $categorie->id }}" class="text-sm text-[#343434]">
                      {{ $categorie->naam }}
                    </label>
                  </div>
                @endforeach
              </div>

              <div class="pt-4 flex flex-col gap-2">
                <a href="{{ route('producten.index') }}" class="text-sm text-gray-500 hover:underline">
                  Reset alle filters
                </a>
              </div>
            </form>
          </div>
        </div>
        <!-- Cards: producten -->
        <div class="w-full md:w-3/4 grid grid-cols-2 md:grid-cols-3 gap-[1rem] h-fit">
            @foreach($producten as $product)
                <div class="bg-white p-[1.5rem] rounded-lg flex flex-col h-full border-1 border-gray-100 relative">
                    @if ($product->voorraad === 0)
                        <span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded">
                            Uitverkocht
                        </span>
                    @endif
                    @php $slug = Str::slug($product->naam); @endphp
                    <!-- Afbeelding -->
                    <a href="{{ route('producten.show', ['product' => $product->id, 'slug' => $slug]) }}"
                    class="w-full aspect-square overflow-hidden border border-gray-200 rounded-lg p-[1rem] block">
                        <img src="{{ asset('storage/producten/' . $product->foto) }}" alt="{{ $product->naam }}" class="w-full h-full object-cover">
                    </a>
                    <!-- Inhoud -->
                    <div class="flex flex-col justify-between flex-1 mt-4">
                        <div class="flex flex-col gap-[0.5rem]">
                            <h2 class="text-[16px] font-medium">
                                <a href="{{ route('producten.show', ['product' => $product->id, 'slug' => $slug]) }}" class="hover:underline">
                                    {{ $product->naam }}
                                </a>
                            </h2>
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

    <div id="overlay" class="fixed z-50 flex items-center justify-center bottom-4 right-4 hidden opacity-0 translate-y-4 transition-all duration-500">
        <div class="bg-white p-8 rounded-lg w-[350px] border-1 border-gray-200 shadow-lg">
            <h2 class="text-[#191919] text-[22px] font-semibold leading-[1.15] mb-4">
                Product is toegevoegd <br>aan <i class="instrument-serif-font text-[#b38867]">jouw winkelwagen</i>
            </h2>
            <div class="flex justify-between gap-4">
                <a href="/producten" class="flex-1 py-2 bg-gray-200 hover:bg-gray-300 text-center rounded text-sm">Verder winkelen</a>
                <a href="{{ route('winkelwagen.index') }}" class="flex-1 py-2 bg-[#b38867] hover:bg-[#947054] text-white text-center rounded text-sm">Afrekenen</a>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
  // GSAP alleen registreren; we activeren pinnen conditioneel
  if (window.gsap && window.ScrollTrigger) {
    gsap.registerPlugin(ScrollTrigger);
  }

  const sidebar = document.getElementById('categorieSidebar');
  let pinInstance = null;

  function enableSticky() {
    if (!sidebar || pinInstance) return;

    // Kies een veilig endTrigger (grid of fallback naar container)
    const endRef =
      document.querySelector('.grid') ||
      sidebar.parentElement ||
      document.body;

    pinInstance = ScrollTrigger.create({
      trigger: sidebar,
      start: 'top 180px',
      endTrigger: endRef,
      end: 'bottom bottom',
      pin: true,
      pinSpacing: false,
      markers: false
    });

    ScrollTrigger.refresh();
  }

  function disableSticky() {
    if (!pinInstance) return;

    pinInstance.kill();
    pinInstance = null;

    // Opruimen van inline styles (voor het geval)
    sidebar.style.removeProperty('position');
    sidebar.style.removeProperty('top');
    sidebar.style.removeProperty('left');
    sidebar.style.removeProperty('right');
    sidebar.style.removeProperty('bottom');
    sidebar.style.removeProperty('width');

    ScrollTrigger.refresh();
  }

  function applyStickyByBreakpoint() {
    if (window.matchMedia('(min-width: 768px)').matches) {
      enableSticky();   // desktop/tablet breed
    } else {
      disableSticky();  // mobiel: geen GSAP pin
    }
  }

  // Init + live reageren op resizes/orientation
  applyStickyByBreakpoint();
  window.addEventListener('resize', applyStickyByBreakpoint);
  window.addEventListener('orientationchange', applyStickyByBreakpoint);

  // --- jouw bestaande overlay-code blijft ongewijzigd ---
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
          overlay.classList.remove('hidden');
          overlay.classList.add('opacity-0', 'translate-y-4');
          setTimeout(() => overlay.classList.remove('opacity-0', 'translate-y-4'), 10);
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