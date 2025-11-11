<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Factuur {{ $factuurnummer }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .clearfix::after { content: ""; display: table; clear: both; }
        .header, .footer { width: 100%; }
        .header-left { float: left; width: 55%; }
        .header-right { float: right; width: 40%; text-align: right; }
        h1 { font-size: 22px; margin-bottom: 5px; }
        h2 { font-size: 14px; margin: 0 0 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background: #f5f5f5; text-align: left; }
        .text-right { text-align: right; }
        .mt-2 { margin-top: 8px; }
        .mt-4 { margin-top: 16px; }
        .small { font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header clearfix">
        <div class="header-left">
            <h2>{{ $bedrijf['naam'] }}</h2>
            <div>{{ $bedrijf['adres'] }}</div>
            <div>{{ $bedrijf['postcode'] }} {{ $bedrijf['plaats'] }}</div>
            <div class="small mt-2">
                KVK: {{ $bedrijf['kvk'] }}<br>
                BTW: {{ $bedrijf['btw'] }}<br>
                IBAN: {{ $bedrijf['iban'] }}<br>
                E-mail: {{ $bedrijf['email'] }}
            </div>
        </div>
        <div class="header-right">
            <h1>Factuur</h1>
            <div>Factuurnummer: <strong>{{ $factuurnummer }}</strong></div>
            <div>Datum: {{ $bestelling->created_at?->format('d-m-Y') }}</div>
            <div class="mt-2">
                Transactie ID: {{ $bestelling->transactie_id }}
            </div>
        </div>
    </div>

    <div class="mt-4">
        <h2>Factuur aan</h2>
        <div>{{ $bestelling->naam }}</div>
        <div>{{ $bestelling->adres }}</div>
        <div>{{ $bestelling->postcode }} {{ $bestelling->plaats }}</div>
        <div>{{ $bestelling->email }}</div>
    </div>

    <table class="mt-4">
        <thead>
            <tr>
                <th>Artikel</th>
                <th class="text-right">Aantal</th>
                <th class="text-right">Prijs (incl. btw)</th>
                <th class="text-right">Totaal (incl. btw)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bestelling->producten as $item)
                @php
                    $regelTotaal = $item->prijs * $item->pivot->aantal;
                @endphp
                <tr>
                    <td>{{ $item->naam }}</td>
                    <td class="text-right">{{ $item->pivot->aantal }}</td>
                    <td class="text-right">&euro;{{ number_format($item->prijs, 2, ',', '.') }}</td>
                    <td class="text-right">&euro;{{ number_format($regelTotaal, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="mt-4" style="width: 50%; margin-left: auto;">
        <tr>
            <td>Subtotaal (excl. btw)</td>
            <td class="text-right">&euro;{{ number_format($subtotaalEx, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>BTW {{ $btwPercentage }}%</td>
            <td class="text-right">&euro;{{ number_format($btwBedrag, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Totaal (incl. btw)</th>
            <th class="text-right">&euro;{{ number_format($totaalIncl, 2, ',', '.') }}</th>
        </tr>
    </table>

    <div class="footer mt-4 small">
        Deze factuur is automatisch gegenereerd vanuit het besteloverzicht.
    </div>
</body>
</html>
