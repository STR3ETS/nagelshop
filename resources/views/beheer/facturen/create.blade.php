{{-- resources/views/beheer/facturen/create.blade.php --}}
@extends('layouts.beheer')

@section('content')
<div class="w-full h-auto">
  <div class="max-w-[1100px] mx-auto py-[1.5rem]">

    {{-- Topnav --}}
    <div class="w-full flex items-center justify-between mb-6">
      <ul class="flex items-center gap-[2rem]">
        <li><a href="/beheer" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Dashboard</a></li>
        <li><a href="/beheer/producten" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Producten</a></li>
        <li><a href="/beheer/bestellingen" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Bestellingen</a></li>
        <li><a href="/beheer/voorraad" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Voorraad</a></li>
        <li><a href="/beheer/facturen" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition text-[#b38867]">Facturen</a></li>
        <li><a href="/beheer/instellingen" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Instellingen</a></li>
      </ul>

      <form method="POST" action="{{ route('uitloggen') }}">
        @csrf
        <button type="submit" class="px-[1.5rem] py-[0.4rem] bg-gray-200 hover:bg-gray-300 text-gray-500 transition rounded-md text-[15px] font-medium cursor-pointer">
          Uitloggen
        </button>
      </form>
    </div>

    <a href="{{ route('beheer.facturen') }}" class="text-[#191919] opacity-50 text-[12px] hover:underline">
      Terug naar het overzicht
    </a>

    <h1 class="text-[#191919] text-[38px] font-semibold leading-[1.15] my-2">
      Factuur <i class="instrument-serif-font text-[#b38867]">aanmaken</i>
    </h1>
    <p class="text-[#191919] opacity-80 text-[15px] mb-8">
      Factuurnummer wordt automatisch toegewezen bij opslaan (bijv. <strong>INV-000001</strong>).
    </p>

    <div class="w-full bg-white p-[1.5rem] rounded-lg">
      <form method="POST"
            action="{{ route('facturen.opslaan') }}"
            x-data='factuurForm(@json($producten))'
            class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium mb-1">Datum</label>
            <input name="datum" type="date" value="{{ old('datum', now()->toDateString()) }}"
                   class="w-full border border-gray-300 px-4 py-2 rounded-md">
            @error('datum') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">BTW %</label>
            <input name="btw_percentage" type="number" min="0" max="100" value="{{ old('btw_percentage', 21) }}"
                   class="w-full border border-gray-300 px-4 py-2 rounded-md">
            @error('btw_percentage') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Naam</label>
            <input name="naam" type="text" value="{{ old('naam') }}"
                   class="w-full border border-gray-300 px-4 py-2 rounded-md" required>
            @error('naam') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">E-mail</label>
            <input name="email" type="email" value="{{ old('email') }}"
                   class="w-full border border-gray-300 px-4 py-2 rounded-md">
            @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Adres</label>
            <input name="adres" type="text" value="{{ old('adres') }}"
                   class="w-full border border-gray-300 px-4 py-2 rounded-md">
            @error('adres') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium mb-1">Postcode</label>
              <input name="postcode" type="text" value="{{ old('postcode') }}"
                     class="w-full border border-gray-300 px-4 py-2 rounded-md">
              @error('postcode') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Plaats</label>
              <input name="plaats" type="text" value="{{ old('plaats') }}"
                     class="w-full border border-gray-300 px-4 py-2 rounded-md">
              @error('plaats') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
          </div>
        </div>

        <div class="pt-4 border-t border-gray-100">
          <div class="mb-2 flex items-center justify-between">
            <h2 class="text-[#191919] text-[24px] font-medium leading-[1.15]">Productregels</h2>

            <button type="button"
                    @click="addRegel()"
                    class="px-[0.9rem] py-[0.5rem] bg-[#b38867] hover:bg-[#e652a7] text-white rounded-md text-[15px] font-medium transition">
              +
            </button>
          </div>

          <table class="w-full border border-gray-200 rounded-lg text-[15px]">
            <thead>
              <tr class="bg-gray-100">
                <th class="text-left px-4 py-4 font-normal w-[220px]">Product</th>
                <th class="text-left px-4 py-4 font-normal">Artikel</th>
                <th class="text-right px-4 py-4 font-normal w-[110px]">Aantal</th>
                <th class="text-right px-4 py-4 font-normal w-[160px]">Prijs (incl)</th>
                <th class="text-right px-4 py-4 font-normal w-[170px]">Totaal (incl)</th>
                <th class="text-right px-4 py-4 font-normal w-[120px]">Actie</th>
              </tr>
            </thead>

            <tbody>
              <template x-for="(r, i) in regels" :key="r.key">
                <tr class="border-t border-gray-100">
                  <td class="px-4 py-4">
                    <select class="w-full border border-gray-300 px-3 py-2 rounded-md"
                            x-model="r.product_id"
                            @change="applyProduct(i)">
                      <option value="">Selecteer</option>
                      <template x-for="p in producten" :key="p.id">
                        <option :value="p.id" x-text="p.naam"></option>
                      </template>
                    </select>
                    <input type="hidden" :name="`regels[${i}][product_id]`" :value="r.product_id || ''">
                  </td>

                  <td class="px-4 py-4">
                    <input type="text"
                           class="w-full border border-gray-300 px-3 py-2 rounded-md"
                           x-model="r.artikel"
                           :name="`regels[${i}][artikel]`">
                  </td>

                  <td class="px-4 py-4">
                    <input type="number" min="1"
                           class="w-full border border-gray-300 px-3 py-2 rounded-md text-right"
                           x-model.number="r.aantal"
                           :name="`regels[${i}][aantal]`">
                  </td>

                  <td class="px-4 py-4">
                    <input type="number" min="0" step="0.01"
                           class="w-full border border-gray-300 px-3 py-2 rounded-md text-right"
                           x-model.number="r.prijs_incl"
                           :name="`regels[${i}][prijs_incl]`">
                  </td>

                  <td class="px-4 py-4 text-right">
                    <span class="font-medium" x-text="formatMoney(regelTotaal(r))"></span>
                  </td>

                  <td class="px-4 py-4 text-right">
                    <button type="button" @click="removeRegel(i)"
                            class="text-gray-500 hover:underline cursor-pointer">
                      Verwijderen
                    </button>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>

          <div class="w-full flex justify-end mt-4">
            <div class="min-w-[320px] bg-gray-50 border border-gray-200 rounded-lg p-4">
              <div class="flex justify-between text-[15px]">
                <span class="text-[#191919] opacity-80">Totaal (incl)</span>
                <span class="font-medium" x-text="formatMoney(totaalIncl())"></span>
              </div>
            </div>
          </div>

          @error('regels') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end">
          <button type="submit" class="bg-[#b38867] text-white px-6 py-2 rounded-md hover:bg-[#e652a7] transition">
            Opslaan + PDF downloaden
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

@verbatim
<script>
function factuurForm(producten) {
  const normalize = (p) => ({
    id: p.id,
    naam: p.naam,
    prijs: Number(p.prijs ?? 0)
  });

  return {
    producten: (producten || []).map(normalize),
    regels: [
      { key: Date.now() + '-' + Math.random(), product_id: '', artikel: '', aantal: 1, prijs_incl: 0 }
    ],
    addRegel() {
      this.regels.push({ key: Date.now() + '-' + Math.random(), product_id: '', artikel: '', aantal: 1, prijs_incl: 0 });
    },
    removeRegel(i) {
      this.regels.splice(i, 1);
      if (this.regels.length === 0) this.addRegel();
    },
    applyProduct(i) {
      const r = this.regels[i];
      const p = this.producten.find(x => String(x.id) === String(r.product_id));
      if (!p) return;
      if (!r.artikel) r.artikel = p.naam;
      r.prijs_incl = Number(p.prijs || 0);
    },
    regelTotaal(r) {
      return Number(r.prijs_incl || 0) * Number(r.aantal || 0);
    },
    totaalIncl() {
      return this.regels.reduce((sum, r) => sum + this.regelTotaal(r), 0);
    },
    formatMoney(v) {
      const n = Number(v || 0).toFixed(2).replace('.', ',');
      return '€' + n;
    }
  }
}
</script>
@endverbatim
@endsection
