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
        <a href="{{ route('beheer.producten') }}" class="text-[#191919] opacity-50 text-[12px] hover:underline">
            Terug naar het overzicht
        </a>
        <h1 class="text-[#191919] text-[38px] font-semibold leading-[1.15] my-2">Nieuw <i class="instrument-serif-font text-[#b38867]">product</i> toevoegen</h1>
        <p class="text-[#191919] opacity-80 text-[15px] mb-8">
            Vul hieronder de gegevens in om een nieuw nagellakproduct toe te voegen aan je webshop.
        </p>
        <div class="w-full bg-white p-[1.5rem] rounded-lg">
            <form action="{{ route('producten.opslaan') }}" method="POST" enctype="multipart/form-data" class="space-y-5" id="form">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1 mb-2">Productfoto</label>
                    <label for="foto-upload"
                        class="inline-block px-[1.5rem] py-[0.4rem] bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md text-[15px] font-medium cursor-pointer transition">
                        Kies bestand
                    </label>
                    <input type="file" name="foto" id="foto-upload" accept="image/*" class="hidden">
                    <span id="file-name" class="ml-3 text-sm text-gray-600">Geen bestand gekozen</span>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Naam</label>
                    <input type="text" name="naam" required value="{{ old('naam') }}" class="w-full border border-gray-300 px-4 py-2 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Beschrijving</label>
                    <div id="quill-editor" class="bg-white border border-gray-300 rounded-md" style="min-height: 150px;"></div>
                    <input type="hidden" name="beschrijving" id="beschrijving" value="{{ old('beschrijving') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Categorie</label>
                    <select name="category_id" required class="w-full border border-gray-300 px-4 py-2 rounded-md">
                        <option value="">Selecteer een categorie</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->naam }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Prijs (€)</label>
                    <input type="number" name="prijs" required step="0.01" min="0" value="{{ old('prijs') }}" class="w-full border border-gray-300 px-4 py-2 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Voorraad</label>
                    <input type="number" name="voorraad" required min="0" value="{{ old('voorraad') }}" class="w-full border border-gray-300 px-4 py-2 rounded-md">
                </div>
                <button type="submit" class="bg-[#b38867] text-white px-6 py-2 rounded-md hover:bg-[#e652a7] transition">
                    Opslaan
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const quill = new Quill('#quill-editor', {
        theme: 'snow'
    });

    const hiddenInput = document.getElementById('beschrijving');

    if (hiddenInput.value) {
        quill.clipboard.dangerouslyPasteHTML(hiddenInput.value);
    }

    const form = document.getElementById('form');
    form.addEventListener('submit', function () {
        hiddenInput.value = quill.getText().trim()
    });

    const input = document.getElementById('foto-upload');
    const filename = document.getElementById('file-name');

    input.addEventListener('change', function () {
        filename.textContent = input.files[0]?.name || 'Geen bestand gekozen';
    });
</script>
@endsection