@extends('layouts.pages')
@section('content')
<div class="w-full h-auto">
    <div class="max-w-[1100px] mx-auto py-16">
        <div class="w-full flex items-center justify-between mb-2">
            <a href="/producten" class="text-[#191919] opacity-50 text-[12px] hover:underline">
                Terug naar de shop
            </a>
        </div>

        <h1 class="text-[#191919] text-[38px] font-semibold leading-[1.15] mb-2">Jouw <i class="instrument-serif-font text-[#ff64ba]">winkelwagen</i></h1>

        @if(count($cart))
        <div class="w-full flex gap-[1rem]">
            <div class="w-2/3 bg-white p-[1.5rem] rounded-lg h-fit">
                <table class="w-full border border-pink-50 rounded-lg text-[15px]">
                    <tbody>
                        @foreach($cart as $id => $item)
                            <tr class="border-t border-gray-100">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-4">
                                        @if($item['foto'])
                                            <img src="{{ asset('storage/producten/' . $item['foto']) }}" class="w-18 h-18 object-cover rounded">
                                        @endif
                                        <div class="flex flex-col">
                                            <span class="text-[16px] font-medium max-w-[300px] leading-[1] mb-2">{{ $item['naam'] }}</span>
                                            <span class="font-light">&euro;{{ number_format($item['prijs'] * $item['aantal'], 2, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-2">
                                        <form action="{{ route('winkelwagen.aantal', $id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="aantal" value="{{ $item['aantal'] - 1 }}">
                                            <button type="submit" class="px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm cursor-pointer">−</button>
                                        </form>

                                        <span class="w-[20px] text-center">{{ $item['aantal'] }}</span>

                                        <form action="{{ route('winkelwagen.aantal', $id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="aantal" value="{{ $item['aantal'] + 1 }}">
                                            <button type="submit" class="px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm cursor-pointer">+</button>
                                        </form>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-right">
                                <form action="{{ route('winkelwagen.verwijderen', $id) }}" method="POST" class="flex items-center justify-center">
                                    @csrf
                                    <button type="submit" class="text-gray-400 hover:text-red-500 transition cursor-pointer" title="Verwijderen">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m5 0H6" />
                                        </svg>
                                    </button>
                                </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="w-1/3 bg-white p-[1.5rem] rounded-lg h-fit">
                <h2 class="text-[18px] font-semibold mb-4">Overzicht</h2>
                @php
                    $gratisVerzendingDrempel = 75;

                    $totaalIncl = collect($cart)->sum(fn($i) => $i['prijs'] * $i['aantal']); // bijv €120
                    $totaalExcl = $totaalIncl / 1.21;                                         // €99.17
                    $btw = $totaalIncl - $totaalExcl;  
                    
                    $subtotaal = $totaalIncl - $btw;

                    $verzendkosten = $totaalIncl >= $gratisVerzendingDrempel ? 0 : 4.90;
                    $totaalMetVerzending = $totaalIncl + $verzendkosten;

                    $nogTeGaan = max(0, $gratisVerzendingDrempel - $totaalIncl);
                    $voortgang = min(100, round(($totaalIncl / $gratisVerzendingDrempel) * 100));
                @endphp
                <div class="flex justify-between mb-2 text-[15px]">
                    <span>Subtotaal</span>
                    <span>&euro;{{ number_format($subtotaal, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between mb-2 text-[15px]">
                    <span>BTW 21%</span>
                    <span>&euro;{{ number_format($btw, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between mb-2 text-[15px]">
                    <span>Bezorging</span>
                    <span>&euro;{{ number_format($verzendkosten, 2, ',', '.') }}</span>
                </div>
                @if ($totaalIncl < $gratisVerzendingDrempel)
                    <div class="bg-pink-50 text-pink-600 text-sm px-3 py-2 rounded-md my-4">
                        Besteed nog <span class="font-medium">{{ number_format($nogTeGaan, 2, ',', '.') }}</span><br>om gratis verzending te krijgen!
                        <div class="flex flex-col justify-end gap-[0.5rem] mt-2">
                            <div class="w-full bg-pink-100 rounded-full h-2 mt-2">
                                <div class="bg-[#ff64ba] h-2 rounded-full transition-all duration-300" style="width: {{ $voortgang }}%"></div>
                            </div>
                            <span class="text-end text-xs">Gratis verzending</span>
                        </div>
                    </div>
                @else
                    <div class="bg-pink-50 text-pink-600 text-sm px-3 py-2 rounded-md my-4 relative">
                        Hoppa! Jouw bestelling word<br>volledig gratis geleverd.
                        <div class="w-full bg-pink-100 rounded-full h-2 mt-2">
                            <div class="bg-[#ff64ba] h-2 rounded-full transition-all duration-300" style="width: {{ $voortgang }}%"></div>
                        </div>
                        <lord-icon class="absolute z-1 right-3 top-3"
                            src="https://cdn.lordicon.com/lomfljuq.json"
                            trigger="hover"
                            colors="primary:#ff64ba"
                            style="width:30px;height:30px">
                        </lord-icon>
                    </div>
                @endif
                <div class="flex justify-between mt-4 pt-4 border-t border-[#eeeeee] font-semibold text-[16px]">
                    <span>Totaal</span>
                    <span>&euro;{{ number_format($totaalMetVerzending, 2, ',', '.') }}</span>
                </div>
                <form action="{{ route('winkelwagen.contact') }}" method="GET">
                    <button type="submit" class="cursor-pointer mt-5 w-full py-3 bg-black text-white rounded-md text-[15px] font-medium hover:bg-gray-900 transition">
                        Verder met bestellen
                    </button>
                </form>
            </div>
        </div>
        @else
            <p class="text-[15px] text-[#191919] opacity-80">Je winkelwagen is momenteel leeg.</p>
        @endif
    </div>
</div>

<script>
    function updateAantal(id, aantal) {
        if (aantal < 1) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(`/winkelwagen/${id}/aantal`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ aantal: aantal })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Herlaad de pagina om de bijgewerkte informatie te tonen
            }
        })
        .catch(error => console.error('Fout bij bijwerken:', error));
    }
</script>
@endsection
