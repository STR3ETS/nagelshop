@extends('layouts.beheer')
@section('content')
<div class="w-full h-auto">
    <div class="py-[1.5rem] max-w-[1100px] mx-auto">
        <div class="w-full flex items-center justify-between mb-6">
            <ul class="flex items-center gap-[2rem]">
                <li><a href="/beheer" class="hover:text-[#ff64ba] text-[15px] font-medium rounded-sm transition">Dashboard</a></li>
                <li><a href="/beheer/producten" class="hover:text-[#ff64ba] text-[15px] font-medium rounded-sm transition">Producten</a></li>
                <li><a href="/beheer/bestellingen" class="hover:text-[#ff64ba] text-[15px] font-medium rounded-sm transition">Bestellingen</a></li>
                <li><a href="/beheer/voorraad" class="hover:text-[#ff64ba] text-[15px] font-medium rounded-sm transition">Voorraad</a></li>
                <li><a href="/beheer/instellingen" class="hover:text-[#ff64ba] text-[15px] font-medium rounded-sm transition text-[#ff64ba]">Instellingen</a></li>
            </ul>
            <form method="POST" action="{{ route('uitloggen') }}">
                @csrf
                <button type="submit" class="px-[1.5rem] py-[0.4rem] bg-gray-200 hover:bg-gray-300 text-gray-500 transition rounded-md text-[15px] font-medium cursor-pointer">Uitloggen</button>
            </form>
        </div>
        <h1 class="text-[#191919] text-[38px] font-semibold leading-[1.15] mb-2">Beheer hier <i class="instrument-serif-font text-[#ff64ba]">je instellingen</i></h1>
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
                    <button type="submit" class="bg-[#ff64ba] text-white px-6 py-2 rounded-md hover:bg-[#e652a7] transition">Opslaan</button>
                </div>
            </form>
        </div>
        {{-- Kortingscodes --}}
        <div class="w-full bg-white p-[1.5rem] rounded-lg">
            <h2 class="text-[#191919] text-[24px] font-medium leading-[1.15] mb-2">Kortingscodes</h2>
            <form action="{{ route('instellingen.kortingscode.aanmaken') }}" method="POST" class="mb-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[1.5rem]">
                    <div>
                        <label class="block text-sm font-medium mb-1">Korting (%)</label>
                        <input type="number" name="korting" min="1" max="100" required class="w-full border border-gray-300 px-4 py-2 rounded-md" placeholder="Bijv. 20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Vervalt op</label>
                        <input type="datetime-local" name="vervalt_op" required class="w-full border border-gray-300 px-4 py-2 rounded-md">
                    </div>
                </div>
                <div class="text-right mt-4">
                    <button type="submit" class="bg-[#ff64ba] text-white px-6 py-2 rounded-md hover:bg-[#e652a7] transition">Aanmaken</button>
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
                        <tr class="border-t border-gray-100">
                            <td class="px-4 py-4 font-mono">{{ $code->code }}</td>
                            <td class="px-4 py-4">{{ $code->korting }}%</td>
                            <td class="px-4 py-4">{{ \Carbon\Carbon::parse($code->vervalt_op)->format('d-m-Y H:i') }}</td>
                            <td class="px-4 py-4 text-right">
                                <form action="{{ route('instellingen.kortingscode.verwijderen', $code) }}" method="POST" onsubmit="return confirm('Weet je zeker dat je deze kortingscode wilt verwijderen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline">Verwijder</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-4 text-gray-500" colspan="4">Nog geen kortingscodes aangemaakt.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
