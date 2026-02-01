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

    /* Header */
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
      color:#b38867;
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

    /* Address + total block */
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
      color:#b38867;
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
      color:#b38867;
      text-align:right;
      margin-top:2px;
    }

    /* Items table */
    .items{
      width:100%;
      border-collapse:collapse;
      margin-top:16px;
    }
    .items th{
      background:#b38867;
      color:#ffffff;
      font-weight:700;
      padding:9px 8px;
      font-size:10px;
      border:1px solid #b38867;
    }
    .items td{
      padding:10px 8px;
      border-bottom:1px solid #e6e6e6;
      vertical-align:top;
    }
    .items .desc{
      width:40%;
    }
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

    /* Summary (zoals screenshot) */
    .summary{
      width:45%;
      margin-left:auto;
      border-collapse:collapse;
      margin-top:14px;
    }
    .summary td{
      border:none;
      padding:3px 0;
      vertical-align:top;
    }
    .summary .label{ color:#2b2b2b; }
    .summary .sep{ width:14px; text-align:center; color:#b38867; }
    .summary .amount{ text-align:right; font-weight:700; color:#b38867; }
    .summary .note{ font-size:9px; color:#6b6b6b; margin-top:1px; }
    .summary .rowTotal td{
      padding-top:8px;
      border-top:1px solid #d9d9d9;
      font-weight:800;
    }

    /* Footer */
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
      color:#b38867;
      margin-bottom:6px;
    }

    /* Page break safety */
    tr, td, th { page-break-inside: avoid; }
  </style>
</head>
<body>
  @php
    $btwPct = (float) ($btwPercentage ?? ($factuur->btw_percentage ?? 21));

    // Logo
    $logoSrc = $bedrijf['logo'] ?? null;

    // helpers
    $money = function($v){
      $v = (float)($v ?? 0);
      return number_format($v, 2, ',', '.');
    };

    // ✅ PAK DE JUISTE VELDEN UIT FACTUUR
    // korting + verzending zoals we opslaan in jouw FacturenController/store:
    $kortingBedrag = (float) (
      $kortingBedrag
      ?? data_get($factuur, 'korting_bedrag')
      ?? 0
    );

    $kortingType = (string) (data_get($factuur, 'korting_type') ?? 'none');      // none|percent|amount
    $kortingWaarde = (float) (data_get($factuur, 'korting_waarde') ?? 0);

    $verzendKosten = (float) (
      $verzendKosten
      ?? data_get($factuur, 'verzendkosten_incl')
      ?? 0
    );

    // default: betaald = totaal
    $betaaldDoorKlant = (float)($betaaldDoorKlant ?? data_get($factuur, 'betaald_bedrag') ?? data_get($factuur, 'paid_amount') ?? 0);

    // totals uit regels
    $itemsTotaal = 0.0;
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
        <div class="metaLine muted small">
          Datum van publicatie:
          @if(!empty($factuur->datum))
            {{ \Carbon\Carbon::parse($factuur->datum)->format('d/m/Y') }}
          @else
            -
          @endif
        </div>
        <div class="metaLine muted small">Factuur#: <strong>#{{ $factuurnummer }}</strong></div>
      </td>
    </tr>
  </table>

  <div class="hr"></div>

  @php
    // items totaal berekenen
    foreach(($factuur->regels ?? []) as $r){
      $qty = (int)($r->aantal ?? 0);
      $unit = (float)($r->prijs_incl ?? 0);
      $lineTotal = (float)($r->totaal_incl ?? ($unit * $qty));
      $itemsTotaal += $lineTotal;
    }

    // ✅ Subtotaal = producten (incl)
    $subtotaal = max(0, $itemsTotaal);

    // ✅ Subtotaal na korting = producten - korting (we tonen korting hier, totaal klopt alsnog met eindtotaal)
    $subtotaalNaKorting = max(0, $subtotaal - $kortingBedrag);

    // ✅ Eindtotaal incl = producten + verzending - korting
    $totaalVoorKorting = max(0, $subtotaal + $verzendKosten);
    $totaalCalc = max(0, $totaalVoorKorting - $kortingBedrag);

    // Als controller 'totaalIncl' meegeeft, pak die (bron van waarheid)
    $totaal = (float)($totaalIncl ?? data_get($factuur,'totaal_incl') ?? $totaalCalc);

    // BTW inbegrepen over totaal (na korting)
    $btwIncl = (float)($btwBedrag ?? ($btwPct > 0 ? ($totaal - ($totaal / (1 + ($btwPct/100)))) : 0));

    // betaald/openstaand
    if($betaaldDoorKlant <= 0){
      $betaaldDoorKlant = $totaal;
    }
    $openstaand = max(0, $totaal - $betaaldDoorKlant);

    $kortingLabelNote = '';
    if($kortingType === 'percent' && $kortingWaarde > 0){
      $kortingLabelNote = rtrim(rtrim(number_format($kortingWaarde, 2, ',', '.'), '0'), ',') . '%';
    } elseif($kortingType === 'amount' && $kortingWaarde > 0){
      $kortingLabelNote = '€' . $money($kortingWaarde);
    }
  @endphp

  <!-- ADDRESSES + TOTAL -->
  <table class="addrWrap">
    <tr>
      <td style="width:36%; padding-right:18px;">
        <div class="addrBoxTitle">Factureringsgegevens</div>
        <div style="font-weight:700;">{{ $factuur->naam }}</div>
        @if($factuur->adres)<div>{{ $factuur->adres }}</div>@endif
        @if($factuur->postcode || $factuur->plaats)<div>{{ $factuur->postcode }} {{ $factuur->plaats }}</div>@endif
        <div>Netherlands</div>
      </td>

      <td style="width:36%; padding-right:18px;">
        <div class="addrBoxTitle">Verzendgegevens</div>
        <div style="font-weight:700;">{{ $factuur->naam }}</div>
        @if($factuur->adres)<div>{{ $factuur->adres }}</div>@endif
        @if($factuur->postcode || $factuur->plaats)<div>{{ $factuur->postcode }} {{ $factuur->plaats }}</div>@endif
        <div>Netherlands</div>
      </td>

      <td style="width:28%;">
        <div class="totalBig">&euro;{{ $money($totaal) }}</div>
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
      @foreach($factuur->regels as $r)
        @php
          $qty = (int) ($r->aantal ?? 0);
          $unit = (float) ($r->prijs_incl ?? 0);
          $lineTotal = (float) ($r->totaal_incl ?? ($unit * $qty));
          $lineEx = $btwPct > 0 ? ($lineTotal / (1 + ($btwPct/100))) : $lineTotal;
          $lineVat = $lineTotal - $lineEx;

          $sku = $r->sku ?? null;
          $oldUnit = $r->prijs_orig_incl ?? null;
          $discountUnit = $r->korting_per_stuk ?? null;
          $discountLine = $r->korting_totaal ?? null;
        @endphp
        <tr>
          <td class="desc">
            <div class="descTitle">{{ $r->artikel }}</div>
            @if(!empty($sku))
              <div class="tiny muted">SKU: {{ $sku }}</div>
            @endif
          </td>

          <td class="qty right nowrap">{{ $qty }}</td>

          <td class="unit right nowrap">
            @if(!empty($oldUnit))
              <div class="priceOld">&euro;{{ $money((float)$oldUnit) }}</div>
            @endif
            <div>&euro;{{ $money($unit) }}</div>
            @if(!empty($discountUnit))
              <div class="discountLine">(Korting &euro;{{ $money((float)$discountUnit) }})</div>
            @endif
          </td>

          <td class="sub right nowrap">
            <div>&euro;{{ $money($lineTotal) }}</div>
            @if(!empty($discountLine))
              <div class="discountLine">(Korting &euro;{{ $money((float)$discountLine) }})</div>
            @endif
          </td>

          <td class="vat right nowrap">&euro;{{ $money($lineVat) }}</td>
          <td class="tot right nowrap">&euro;{{ $money($lineTotal) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <!-- SUMMARY -->
  <table class="summary">
    <tr>
      <td class="label">Subtotaal</td>
      <td class="sep">:</td>
      <td class="amount">&euro;{{ $money($subtotaal) }}</td>
    </tr>

    <tr>
      <td class="label">
        Korting
        @if(!empty($kortingLabelNote))
          <div class="note">({{ $kortingLabelNote }})</div>
        @endif
      </td>
      <td class="sep">:</td>
      <td class="amount">
        @if($kortingBedrag > 0)
          -&euro;{{ $money($kortingBedrag) }}
        @else
          &euro;{{ $money(0) }}
        @endif
      </td>
    </tr>

    <tr>
      <td class="label">Subtotaal na korting</td>
      <td class="sep">:</td>
      <td class="amount">&euro;{{ $money($subtotaalNaKorting) }}</td>
    </tr>

    <tr>
      <td class="label">Verzending</td>
      <td class="sep">:</td>
      <td class="amount">&euro;{{ $money($verzendKosten) }}</td>
    </tr>

    <tr>
      <td class="label">
        BTW (Producten + Verzending)
        <div class="note">(Inbegrepen)</div>
      </td>
      <td class="sep">:</td>
      <td class="amount">&euro;{{ $money($btwIncl) }}</td>
    </tr>

    <tr class="rowTotal">
      <td class="label">Totaal</td>
      <td class="sep">:</td>
      <td class="amount">&euro;{{ $money($totaal) }}</td>
    </tr>

    <tr>
      <td class="label">Betaald door klant</td>
      <td class="sep">:</td>
      <td class="amount">&euro;{{ $money($betaaldDoorKlant) }}</td>
    </tr>

    <tr>
      <td class="label">Openstaand</td>
      <td class="sep">:</td>
      <td class="amount">&euro;{{ $money($openstaand) }}</td>
    </tr>
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