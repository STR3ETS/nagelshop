@extends('layouts.beheer')
@section('content')
<!-- Quill styles -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<!-- Quill script -->
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<div class="w-full h-auto">
    <div class="max-w-[1100px] mx-auto py-[1.5rem]">
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
                <button type="submit" class="px-[1.5rem] py-[0.4rem] bg-gray-200 hover:bg-gray-300 text-gray-500 transition rounded-md text-[15px] font-medium cursor-pointer">Uitloggen</button>
            </form>
        </div>

        <a href="/beheer/producten" class="text-[#191919] opacity-50 text-[12px] hover:underline">
            Terug naar het overzicht
        </a>

        <h1 class="text-[#191919] text-[38px] font-semibold leading-[1.15] my-2">
            Product <i class="instrument-serif-font text-[#b38867]">bewerken</i>
        </h1>
        <p class="text-[#191919] opacity-80 text-[15px] mb-8">
            Je bent momenteel <strong>{{ $product->naam }}</strong> aan het bewerken...
        </p>

        <div class="w-full bg-white p-[1.5rem] rounded-lg">
            <form action="{{ route('producten.bijwerken', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-5" id="form">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium mb-1">Huidige foto:</label>
                    @if ($product->foto)
                        <img src="{{ asset('storage/producten/' . $product->foto) }}" class="w-32 mb-2 rounded">
                    @else
                        <p class="text-sm text-gray-500">Geen foto beschikbaar</p>
                    @endif

                    <label class="block text-sm font-medium mb-2">Nieuwe foto uploaden:</label>
                    <label for="foto-upload"
                        class="inline-block px-[1.5rem] py-[0.4rem] bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md text-[15px] font-medium cursor-pointer transition">
                        Kies bestand
                    </label>
                    <input type="file" name="foto" id="foto-upload" accept="image/*" class="hidden">
                    <span id="file-name" class="ml-3 text-sm text-gray-600">Geen bestand gekozen</span>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Naam</label>
                    <input type="text" name="naam" required value="{{ old('naam', $product->naam) }}" class="w-full border border-gray-300 px-4 py-2 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Beschrijving</label>
                    <div id="quill-editor" class="bg-white border border-gray-300 rounded-md" style="min-height: 150px;"></div>
                    <input type="hidden" name="beschrijving" id="beschrijving" value="{{ old('beschrijving', $product->beschrijving) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Categorie</label>
                    <select name="category_id" id="category_id" required class="w-full border border-gray-300 px-4 py-2 rounded-md">
                        <option value="">Selecteer een categorie</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->naam }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Subcategorie (alleen tonen als categorie subcats heeft) --}}
                <div id="subcategory-wrap" class="hidden">
                    <label class="block text-sm font-medium mb-1 mt-4">Subcategorie</label>
                    <select name="subcategory_id" id="subcategory_id" class="w-full border border-gray-300 px-4 py-2 rounded-md">
                        <option value="">Selecteer een subcategorie</option>
                        {{-- We vullen via JS, maar tonen server-side bij terugkeer na fouten --}}
                        @php
                            $__oldCat = old('category_id', $product->category_id);
                            $__oldSub = old('subcategory_id', $product->subcategory_id);
                            $__prefill = collect();
                            if ($__oldCat) {
                                $__prefill = \App\Models\Subcategory::where('category_id', $__oldCat)->orderBy('naam')->get();
                            }
                        @endphp
                        @foreach($__prefill as $__s)
                            <option value="{{ $__s->id }}" {{ $__oldSub == $__s->id ? 'selected' : '' }}>
                                {{ $__s->naam }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Prijs (€)</label>
                    <input type="number" name="prijs" required step="0.01" min="0" value="{{ old('prijs', $product->prijs) }}" class="w-full border border-gray-300 px-4 py-2 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Voorraad</label>
                    <input type="number" name="voorraad" required min="0" value="{{ old('voorraad', $product->voorraad) }}" class="w-full border border-gray-300 px-4 py-2 rounded-md">
                </div>

                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        id="uitverkoop"
                        name="uitverkoop"
                        value="1"
                        class="h-4 w-4 border-gray-300 rounded"
                        {{ old('uitverkoop', $product->uitverkoop ?? false) ? 'checked' : '' }}
                    >
                    <label for="uitverkoop" class="text-sm font-medium">Uitverkoop</label>
                </div>

                <button type="submit" class="bg-[#b38867] text-white px-6 py-2 rounded-md hover:bg-[#e652a7] transition">
                    Wijzigingen opslaan
                </button>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
  // === Quill (HTML opslaan) ===
  const quill = new Quill('#quill-editor', { theme: 'snow' });
  const hiddenInput = document.getElementById('beschrijving');

  if (hiddenInput && hiddenInput.value) {
    quill.clipboard.dangerouslyPasteHTML(hiddenInput.value);
  }

  const form = document.getElementById('form');
  if (form) {
    form.addEventListener('submit', function () {
      // Stuur HTML i.p.v. plain text
      hiddenInput.value = quill.root.innerHTML.trim();
    });
  }

  // === Bestandslabel ===
  const fileInput = document.getElementById('foto-upload');
  const fileNameEl = document.getElementById('file-name');
  if (fileInput && fileNameEl) {
    fileInput.addEventListener('change', function () {
      fileNameEl.textContent = (fileInput.files && fileInput.files[0]) ? fileInput.files[0].name : 'Geen bestand gekozen';
    });
  }

  // === Subcategorie loader ===
  const SUBS_URL = '{{ route('beheer.api.subcategories') }}';
  const cat = document.getElementById('category_id');
  const wrap = document.getElementById('subcategory-wrap');
  const sub  = document.getElementById('subcategory_id');

  const preCat = @json(old('category_id', $product->category_id));
  const preSub = @json(old('subcategory_id', $product->subcategory_id));

  async function loadSubs(categoryId, selectedId = null) {
    if (!sub || !wrap) return;

    sub.innerHTML = '<option value="">Selecteer een subcategorie</option>';
    wrap.classList.add('hidden');
    if (!categoryId) return;

    try {
      const res = await fetch(SUBS_URL + '?category_id=' + encodeURIComponent(categoryId), {
        headers: { 'Accept': 'application/json' }
      });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();

      if (Array.isArray(data) && data.length) {
        for (const s of data) {
          const opt = document.createElement('option');
          opt.value = s.id;
          opt.textContent = s.naam;
          if (selectedId && String(selectedId) === String(s.id)) opt.selected = true;
          sub.appendChild(opt);
        }
        wrap.classList.remove('hidden');
      }
    } catch (err) {
      console.error('Kon subcategorieën niet laden:', err);
    }
  }

  // init – laad per huidige categorie (edit) en preselecteer
  if (cat) {
    if (preCat) {
      loadSubs(preCat, preSub);
    }
    cat.addEventListener('change', () => loadSubs(cat.value, null));
  }
})();
</script>
@endsection