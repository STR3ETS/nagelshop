@extends('layouts.beheer')
@section('content')
<div class="w-full h-auto">
    <div class="py-[1.5rem] max-w-[1100px] mx-auto">
        <div class="w-full flex items-center justify-between mb-6">
            <ul class="flex items-center gap-[2rem]">
                <li><a href="/beheer" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Dashboard</a></li>
                <li><a href="/beheer/producten" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Producten</a></li>
                <li><a href="/beheer/bestellingen" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Bestellingen</a></li>
                <li><a href="/beheer/voorraad" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Voorraad</a></li>
                <li><a href="/beheer/facturen" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition">Facturen</a></li>
                <li><a href="/beheer/instellingen" class="hover:text-[#b38867] text-[15px] font-medium rounded-sm transition text-[#b38867]">Instellingen</a></li>
            </ul>
            <form method="POST" action="{{ route('uitloggen') }}">
                @csrf
                <button type="submit" class="px-[1.5rem] py-[0.4rem] bg-gray-200 hover:bg-gray-300 text-gray-500 transition rounded-md text-[15px] font-medium cursor-pointer">Uitloggen</button>
            </form>
        </div>
        <h1 class="text-[#191919] text-[38px] font-semibold leading-[1.15] mb-2">Beheer hier <i class="instrument-serif-font text-[#b38867]">je instellingen</i></h1>
        <p class="text-[#191919] opacity-80 text-[15px] mb-8">
            Werk contactgegevens, openingstijden en kortingscodes eenvoudig bij.
        </p>
        <div class="w-full bg-white p-[1.5rem] rounded-lg mb-10">
            <h2 class="text-[#191919] text-[24px] font-medium leading-[1.15] mb-2">Bedrijfsgegevens</h2>
            <form method="POST" action="{{ route('instellingen.algemeen') }}" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[1.5rem]">
                    <div>
                        <label class="block text-sm font-medium mb-1">E-mailadres</label>
                        <input type="email" name="email" value="{{ old('email', $instellingen->email) }}" class="w-full border border-gray-300 px-4 py-2 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Telefoonnummer</label>
                        <input type="text" name="telefoon" value="{{ old('telefoon', $instellingen->telefoon) }}" class="w-full border border-gray-300 px-4 py-2 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">BTW-nummer</label>
                        <input type="text" name="btw_nummer" value="{{ old('btw_nummer', $instellingen->btw_nummer) }}" class="w-full border border-gray-300 px-4 py-2 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">KVK-nummer</label>
                        <input type="text" name="kvk_nummer" value="{{ old('kvk_nummer', $instellingen->kvk_nummer) }}" class="w-full border border-gray-300 px-4 py-2 rounded-md">
                    </div>
                    <div class="md:col-span-2">
                        @php
                            $dagen = ['maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag', 'zondag'];
                            $openingstijden = json_decode($instellingen->openingstijden ?? '[]', true) ?? [];
                        @endphp
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2">Openingstijden</label>
                            <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
                                @foreach($dagen as $dag)
                                    <div>
                                        <label class="block text-xs text-gray-700 mb-1 capitalize">{{ $dag }}</label>
                                        <input type="text" name="openingstijden[{{ $dag }}]" value="{{ old('openingstijden.' . $dag, $openingstijden[$dag] ?? '') }}"
                                            class="w-full border border-gray-300 px-4 py-2 rounded-md">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-right -mt-[0.5rem]">
                    <button type="submit" class="bg-[#b38867] text-white px-6 py-2 rounded-md hover:bg-[#e652a7] transition">Opslaan</button>
                </div>
            </form>
        </div>
        {{-- Kortingscodes --}}
        <div class="w-full bg-white p-[1.5rem] rounded-lg">
            <h2 class="text-[#191919] text-[24px] font-medium leading-[1.15] mb-2">Kortingscodes</h2>
            <form action="{{ route('instellingen.kortingscode.aanmaken') }}" method="POST" class="mb-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-[1.5rem]">
                    <div>
                        <label class="block text-sm font-medium mb-1">Codenaam</label>
                        <input type="text" name="code" value="{{ old('code') }}" placeholder="bijv. ZOMER2025"
                            class="w-full border border-gray-300 px-4 py-2 rounded-md uppercase" required>
                        @error('code')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Type korting</label>
                        <select name="type" class="w-full border border-gray-300 px-4 py-2 rounded-md" required x-data @change="
                            const v = $event.target.value;
                            const ph = v === 'amount' ? 'Bijv. 10,00' : 'Bijv. 20';
                            const step = v === 'amount' ? '0.01' : '1';
                            const min  = v === 'amount' ? '0.01' : '1';
                            const max  = v === 'amount' ? '' : '100';
                            const input = $el.parentElement.nextElementSibling.querySelector('input[name=value]');
                            input.placeholder = ph; input.step = step; input.min = min; input.max = max;
                        ">
                            <option value="percent" {{ old('type') === 'percent' ? 'selected' : '' }}>% (percentage)</option>
                            <option value="amount"  {{ old('type') === 'amount'  ? 'selected' : '' }}>€ (vast bedrag)</option>
                        </select>
                        @error('type')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Waarde</label>
                        <div class="relative">
                            <input type="number" name="value" step="{{ old('type','percent') === 'amount' ? '0.01' : '1' }}"
                                min="{{ old('type','percent') === 'amount' ? '0.01' : '1' }}"
                                max="{{ old('type','percent') === 'amount' ? '' : '100' }}"
                                value="{{ old('value') }}" placeholder="{{ old('type','percent') === 'amount' ? 'Bijv. 10,00' : 'Bijv. 20' }}"
                                class="w-full border border-gray-300 px-4 py-2 rounded-md pr-10" required>
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
                                {{ old('type','percent') === 'amount' ? '€' : '%' }}
                            </span>
                        </div>
                        @error('value')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium mb-1">Vervalt op</label>
                        <input type="datetime-local" name="vervalt_op" value="{{ old('vervalt_op') }}" class="w-full border border-gray-300 px-4 py-2 rounded-md" required>
                        @error('vervalt_op')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="text-right mt-4">
                    <button type="submit" class="bg-[#b38867] text-white px-6 py-2 rounded-md hover:bg-[#e652a7] transition">
                        Aanmaken
                    </button>
                </div>
            </form>
            <table class="w-full border border-gray-200 rounded-lg text-[15px]">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="text-left px-4 py-4 font-normal">Code</th>
                        <th class="text-left px-4 py-4 font-normal">Korting</th>
                        <th class="text-left px-4 py-4 font-normal">Geldig tot</th>
                        <th class="text-right px-4 py-4 font-normal">Actie</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kortingscodes as $code)
                        @php $isExpired = $code->isExpired(); @endphp
                        <tr class="border-t border-gray-100 {{ $isExpired ? 'opacity-60' : '' }}">
                            <td class="px-4 py-4 font-mono">
                                {{ $code->code }}
                                @if($isExpired)
                                    <span class="ml-2 inline-block text-xs px-2 py-0.5 rounded-full bg-gray-200 text-gray-700 align-middle">
                                        Verlopen
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                {{ $code->display_value }}
                            </td>
                            <td class="px-4 py-4">
                                {{ $code->vervalt_op->format('d-m-Y H:i') }}
                            </td>
                            <td class="px-4 py-4 text-right">
                                <form action="{{ route('instellingen.kortingscode.verwijderen', $code) }}"
                                    method="POST"
                                    onsubmit="return confirm('Weet je zeker dat je deze kortingscode wilt verwijderen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline">Verwijder</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-4 text-gray-500" colspan="4">Nog geen kortingscodes aangemaakt.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
