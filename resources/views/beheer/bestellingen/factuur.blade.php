<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <title>Factuur {{ $factuurnummer }}</title>
  <style>
    @page { margin: 28px 34px; }

    body{
      font-family: DejaVu Sans, sans-serif;
      font-size: 11px;
      color:#1b1b1b;
      margin:0;
      padding:0;
    }

    .muted{ color:#6b6b6b; }
    .small{ font-size:10px; }
    .tiny{ font-size:9px; }
    .right{ text-align:right; }
    .nowrap{ white-space:nowrap; }

    .hr{
      border-top:1px solid #d9d9d9;
      margin:14px 0;
    }

    .top{
      width:100%;
      border-collapse:collapse;
    }
    .top td{
      vertical-align:top;
    }
    .brand{
      font-weight:700;
      letter-spacing:0.2px;
    }
    .factuurTitle{
      font-weight:800;
      font-size:16px;
      letter-spacing:0.8px;
      color:#b08b2e;
    }
    .metaLine{
      margin-top:2px;
      line-height:1.35;
    }
    .logo{
      width:52px;
      height:auto;
      display:block;
      margin:0 0 8px 0;
    }

    .addrWrap{
      width:100%;
      border-collapse:collapse;
      margin-top:8px;
    }
    .addrWrap td{
      vertical-align:top;
      padding:0;
    }
    .addrBoxTitle{
      font-weight:700;
      color:#b08b2e;
      margin-bottom:6px;
    }
    .totalBig{
      font-size:18px;
      font-weight:800;
      text-align:right;
      margin-top:2px;
    }
    .totalLabel{
      font-size:9px;
      letter-spacing:1.2px;
      font-weight:800;
      color:#b08b2e;
      text-align:right;
      margin-top:2px;
    }

    .items{
      width:100%;
      border-collapse:collapse;
      margin-top:16px;
    }
    .items th{
      background:#b08b2e;
      color:#ffffff;
      font-weight:700;
      padding:9px 8px;
      font-size:10px;
      border:1px solid #b08b2e;
    }
    .items td{
      padding:10px 8px;
      border-bottom:1px solid #e6e6e6;
      vertical-align:top;
    }
    .items .desc{ width:40%; }
    .items .qty{ width:8%; }
    .items .unit{ width:14%; }
    .items .sub{ width:14%; }
    .items .vat{ width:12%; }
    .items .tot{ width:12%; }

    .descTitle{
      font-weight:700;
      margin-bottom:3px;
    }

    .priceOld{
      text-decoration: line-through;
      color:#8a8a8a;
      font-size:10px;
      margin-bottom:2px;
    }
    .discountLine{
      color:#6b6b6b;
      font-size:9px;
      margin-top:2px;
    }

    .footer{
      width:100%;
      border-collapse:collapse;
      margin-top:28px;
    }
    .footer td{
      vertical-align:top;
      padding-top:10px;
    }
    .footerTitle{
      font-weight:700;
      color:#b08b2e;
      margin-bottom:6px;
    }

    tr, td, th { page-break-inside: avoid; }
  </style>
</head>
<body>
  @php
    $btwPct = (float) ($btwPercentage ?? 21);
    $totaal = (float) ($totaalIncl ?? 0);

    $logoSrc = $bedrijf['logo'] ?? null;
  @endphp

  <!-- TOP HEADER -->
  <table class="top">
    <tr>
      <td style="width:60%;">
        @if(!empty($logoSrc))
          <img class="logo" src="{{ $logoSrc }}" alt="Logo">
        @endif
        <div class="brand">{{ $bedrijf['naam'] ?? '' }}</div>
      </td>
      <td class="right" style="width:40%;">
        <div class="factuurTitle">FACTUUR</div>
        <div class="metaLine muted small">Datum van publicatie: {{ $bestelling->created_at?->format('d/m/Y') }}</div>
        <div class="metaLine muted small">Factuur#: <strong>#{{ $factuurnummer }}</strong></div>
        @if(!empty($bestelling->transactie_id))
          <div class="metaLine muted small">Transactie ID: {{ $bestelling->transactie_id }}</div>
        @endif
      </td>
    </tr>
  </table>

  <div class="hr"></div>

  <!-- ADDRESSES + TOTAL -->
  <table class="addrWrap">
    <tr>
      <td style="width:36%; padding-right:18px;">
        <div class="addrBoxTitle">Factureringsgegevens</div>
        <div style="font-weight:700;">{{ $bestelling->naam }}</div>
        <div>{{ $bestelling->adres }}</div>
        <div>{{ $bestelling->postcode }} {{ $bestelling->plaats }}</div>
        <div>Netherlands</div>
      </td>

      <td style="width:36%; padding-right:18px;">
        <div class="addrBoxTitle">Verzendgegevens</div>
        <div style="font-weight:700;">{{ $bestelling->naam }}</div>
        <div>{{ $bestelling->adres }}</div>
        <div>{{ $bestelling->postcode }} {{ $bestelling->plaats }}</div>
        <div>Netherlands</div>
      </td>

      <td style="width:28%;">
        <div class="totalBig">&euro;{{ number_format($totaal, 2, ',', '.') }}</div>
        <div class="totalLabel">TOTAAL</div>
      </td>
    </tr>
  </table>

  <!-- ITEMS -->
  <table class="items">
    <thead>
      <tr>
        <th class="desc" style="text-align:left;">Beschrijving</th>
        <th class="qty right">Aantal<br>stuks</th>
        <th class="unit right">Eenheidsprijs</th>
        <th class="sub right">Subtotaal</th>
        <th class="vat right">BTW<br><span class="tiny">(Inbegrepen)</span></th>
        <th class="tot right">Totaal</th>
      </tr>
    </thead>
    <tbody>
      @foreach($bestelling->producten as $item)
        @php
          $qty = (int) ($item->pivot->aantal ?? 0);
          $unit = (float) ($item->prijs ?? 0);
          $lineTotal = (float) ($unit * $qty);

          $lineEx = $btwPct > 0 ? ($lineTotal / (1 + ($btwPct/100))) : $lineTotal;
          $lineVat = $lineTotal - $lineEx;

          $sku = $item->sku ?? null;

          // Optioneel:
          $oldUnit = $item->prijs_orig_incl ?? null;
          $discountUnit = $item->korting_per_stuk ?? null;
          $discountLine = $item->korting_totaal ?? null;
        @endphp
        <tr>
          <td class="desc">
            <div class="descTitle">{{ $item->naam }}</div>
            @if(!empty($sku))
              <div class="tiny muted">SKU: {{ $sku }}</div>
            @endif
          </td>

          <td class="qty right nowrap">{{ $qty }}</td>

          <td class="unit right nowrap">
            @if(!empty($oldUnit))
              <div class="priceOld">&euro;{{ number_format((float)$oldUnit, 2, ',', '.') }}</div>
            @endif
            <div>&euro;{{ number_format($unit, 2, ',', '.') }}</div>
            @if(!empty($discountUnit))
              <div class="discountLine">(Korting &euro;{{ number_format((float)$discountUnit, 2, ',', '.') }})</div>
            @endif
          </td>

          <td class="sub right nowrap">
            <div>&euro;{{ number_format($lineTotal, 2, ',', '.') }}</div>
            @if(!empty($discountLine))
              <div class="discountLine">(Korting &euro;{{ number_format((float)$discountLine, 2, ',', '.') }})</div>
            @endif
          </td>

          <td class="vat right nowrap">&euro;{{ number_format($lineVat, 2, ',', '.') }}</td>
          <td class="tot right nowrap">&euro;{{ number_format($lineTotal, 2, ',', '.') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <!-- FOOTER -->
  <table class="footer">
    <tr>
      <td style="width:60%;">
        <div class="footerTitle">Bedrijf</div>
        <div style="font-weight:700;">{{ $bedrijf['naam'] ?? '' }}</div>
        @if(!empty($bedrijf['adres']))<div>{{ $bedrijf['adres'] }}</div>@endif
        @if(!empty($bedrijf['postcode']) || !empty($bedrijf['plaats']))<div>{{ $bedrijf['postcode'] ?? '' }} {{ $bedrijf['plaats'] ?? '' }}</div>@endif
        @if(!empty($bedrijf['kvk']))<div class="small">KVK: {{ $bedrijf['kvk'] }}</div>@endif
        @if(!empty($bedrijf['btw']))<div class="small">BTW - {{ $bedrijf['btw'] }}</div>@endif
        @if(!empty($bedrijf['iban']))<div class="small">IBAN: {{ $bedrijf['iban'] }}</div>@endif
      </td>
      <td style="width:40%;">
        <div class="footerTitle">Contact</div>
        @if(!empty($bedrijf['email']))<div>{{ $bedrijf['email'] }}</div>@endif
        @if(!empty($bedrijf['website']))<div>{{ $bedrijf['website'] }}</div>@endif
        @if(!empty($bedrijf['tel']))<div>{{ $bedrijf['tel'] }}</div>@endif
      </td>
    </tr>
  </table>
</body>
</html>
