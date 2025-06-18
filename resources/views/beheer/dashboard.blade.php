@extends('layouts.beheer')
@section('content')
<div class="w-full h-auto">
    <div class="py-[1.5rem] max-w-[1100px] mx-auto">
        <div class="w-full flex items-center justify-between mb-6">
            <ul class="flex items-center gap-[2rem]">
                <li><a href="/beheer" class="hover:text-[#ff64ba] text-[15px] font-medium rounded-sm transition text-[#ff64ba]">Dashboard</a></li>
                <li><a href="/beheer/producten" class="hover:text-[#ff64ba] text-[15px] font-medium rounded-sm transition">Producten</a></li>
                <li><a href="/beheer/bestellingen" class="hover:text-[#ff64ba] text-[15px] font-medium rounded-sm transition">Bestellingen</a></li>
                <li><a href="/beheer/voorraad" class="hover:text-[#ff64ba] text-[15px] font-medium rounded-sm transition">Voorraad</a></li>
                <li><a href="/beheer/instellingen" class="hover:text-[#ff64ba] text-[15px] font-medium rounded-sm transition">Instellingen</a></li>
            </ul>
            <form method="POST" action="{{ route('uitloggen') }}">
                @csrf
                <button type="submit" class="px-[1.5rem] py-[0.4rem] bg-gray-200 hover:bg-gray-300 text-gray-500 transition rounded-md text-[15px] font-medium cursor-pointer">Uitloggen</button>
            </form>
        </div>
        <h1 class="text-[#191919] text-[38px] font-semibold leading-[1.15] mb-2">Welkom in <i class="instrument-serif-font text-[#ff64ba]">jouw beheerpaneel</i></h1>
        <p class="text-[#191919] opacity-80 text-[15px] mb-8">
            💸 Je hebt deze maand <strong>{{ $productenVerkocht }}</strong> producten verkocht.<br>
            @if($openBestellingen === 1)
                📦 Er staat nog <strong>{{ $openBestellingen }}</strong> openstaande bestelling klaar.
            @elseif($openBestellingen === 0)
                📦 Er staan geen bestellingen meer open.
            @else
                📦 Er staan nog <strong>{{ $openBestellingen }}</strong> openstaande bestellingen klaar.
            @endif
        </p>
        <div class="w-full mb-4 flex gap-4">
            <div class="bg-white rounded-lg p-[1.5rem] w-1/2">
                <h2 class="text-[#191919] text-[24px] font-semibold leading-[1.15] mb-2">Omzet afgelopen 30 dagen</h2>
                <h3 class="text-[#ff64ba] text-[20px] font-medium leading-[1.15] mb-2">€ {{ number_format($omzet30Dagen, 2, ',', '.') }}</h3>
            </div>
            <div class="bg-white rounded-lg p-[1.5rem] w-1/2">
                <h2 class="text-[#191919] text-[24px] font-semibold leading-[1.15] mb-2">Omzet afgelopen 7 dagen</h2>
                <h3 class="text-[#ff64ba] text-[20px] font-medium leading-[1.15] mb-2">€ {{ number_format($omzet7Dagen, 2, ',', '.') }}</h3>
            </div>
        </div>
        <div class="w-full bg-white p-[1.5rem] rounded-lg">
            <h2 class="text-[#191919] text-[24px] font-semibold leading-[1.15] mb-2">Bestellingen afgelopen 7 dagen</h2>
            <canvas id="bestellingenChart" height="75"></canvas>
        </div>
    </div>
</div>
<script>
    const ctx = document.getElementById('bestellingenChart').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Bestellingen',
                data: @json($chartData),
                borderColor: '#ff64ba75',
                backgroundColor: '#ff64ba25', // Lichterblauw en doorzichtig
                tension: 0.4,
                fill: true, // <== Zorgt voor gebied onder de lijn
                pointBackgroundColor: '#ff64ba',
                pointRadius: 5,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    grid: {
                        color: '#f1f1f1' // horizontale lijnen (verticale as)
                    },
                    ticks: {
                        color: '#888888' // optioneel: tekstkleur onder de X-as
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f1f1f1' // verticale lijnen (horizontale as)
                    },
                    ticks: {
                        stepSize: 1,
                        color: '#888888' // optioneel: tekstkleur op de Y-as
                    }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
</script>
@endsection