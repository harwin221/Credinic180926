<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cartera Diaria</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 8px; color: #333; padding: 10px; }
        .header { width: 100%; margin-bottom: 15px; border-bottom: 2px solid #1f9cb5; padding-bottom: 10px; }
        .header-content { display: table; width: 100%; }
        .logo-section { display: table-cell; width: 150px; vertical-align: middle; }
        .logo-section img { width: 140px; height: auto; }
        .title-section { display: table-cell; vertical-align: middle; text-align: center; }
        .title-section h1 { font-size: 18px; color: #1f9cb5; margin-bottom: 5px; }
        .title-section p { font-size: 9px; color: #666; }
        .filters-section { background-color: #f8f9fa; padding: 8px; margin-bottom: 10px; border-left: 3px solid #1f9cb5; }
        .filters-section h3 { font-size: 10px; color: #1f9cb5; margin-bottom: 5px; }
        .filter-item { display: inline-block; margin-right: 15px; font-size: 9px; }
        .section-title { background-color: #28a745; color: white; padding: 8px; font-size: 10px; font-weight: bold; margin-top: 15px; margin-bottom: 8px; text-align: center; }
        .section-title.mora { background-color: #ffc107; color: #333; }
        .section-title.vencido { background-color: #dc3545; color: white; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 7px; }
        table thead { background-color: #e9ecef; }
        table thead th { padding: 5px 3px; text-align: left; font-weight: bold; border: 1px solid #dee2e6; text-transform: uppercase; }
        table tbody td { padding: 4px 3px; border: 1px solid #dee2e6; vertical-align: middle; }
        table tbody tr:nth-child(even) { background-color: #f8f9fa; }
        .nowrap { white-space: nowrap; }
        .subtotal-row { background-color: #d1ecf1 !important; font-weight: bold; }
        .gran-total-section { margin-top: 15px; background-color: #1f9cb5; color: white; padding: 10px; text-align: center; }
        .gran-total-section h2 { font-size: 13px; margin-bottom: 5px; }
        .stats { font-size: 9px; margin-top: 5px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .row-dia { background-color: #d4edda !important; }
        .row-mora { background-color: #fff3cd !important; }
        .row-vencido { background-color: #f8d7da !important; }
        .bold { font-weight: bold; }
        .num-red { color: #dc3545; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo-section"><img src="{{public_path('assets/img/LogoCrediNica.png')}}" alt="CrediNica"></div>
            <div class="title-section"><h1>CARTERA DIARIA</h1><p>Reporte de Cuotas del Día, Mora y Vencidos</p></div>
        </div>
    </div>

    <div class="filters-section">
        <h3>FILTROS APLICADOS:</h3>
        <div class="filter-item"><strong>Fecha:</strong> {{fecha_d_m_Y($fecha)}}</div>
        <div class="filter-item"><strong>Cobrador:</strong> {{request('cobrador') ? \App\Models\User::find(decode(request('cobrador')))->full_name : 'Todos'}}</div>
    </div>

    @php
        use Carbon\Carbon;
        $fechaReporte = $fecha ?: date('Y-m-d');
        $totalRegistros = $cuotasDia->count() + $cuotasMora->count() + $cuotasVencidas->count();
    @endphp

    {{-- SECCIÓN 1 --}}
    @php $sC1=0; $sA1=0; $sCA1=0; $sV1=0; $sP1=0; @endphp
    <div class="section-title">CUOTAS DEL DÍA - {{fecha_d_m_Y($fechaReporte)}}</div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Cliente</th><th style="width: 18%;">Dirección</th><th style="width: 9%;">Teléfono</th>
                <th style="width: 8%;">F. Desemb.</th><th style="width: 8%;">F. Venc.</th>
                <th class="text-right">Cuota</th><th class="text-right">Atraso</th><th class="text-right">Cuota+Atr</th>
                <th class="text-center">C.Venc</th><th class="text-center">Días Atr.</th>
                <th class="text-right" style="width: 8%;">Saldo Total</th><th class="text-center">Est</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cuotasDia as $item)
                @php $p = $item['prestamo']; $sC1+=$item['cuota_hoy']; $sA1+=$item['atraso']; $sCA1+=($item['atraso']+$item['cuota_hoy']); $sV1+=$item['cant_venc']; $sP1+=$item['saldo_total']; @endphp
                <tr class="row-dia">
                    <td class="bold">{{$p->cliente->full_name ?? 'N/A'}}</td>
                    <td style="font-size: 6.5px;">{{$p->cliente->direccion ?? '-'}}</td>
                    <td class="nowrap">{{$p->cliente->telefono1 ?? '-'}}</td>
                    <td class="text-center nowrap">{{fecha_d_m_Y($p->fecha_desembolso)}}</td>
                    <td class="text-center nowrap">{{$item['fecha_venc'] ? fecha_d_m_Y($item['fecha_venc']) : "-"}}</td>
                    <td class="text-right nowrap">{{number_format($item['cuota_hoy'], 2)}}</td>
                    <td class="text-right nowrap">{{$item['atraso'] > 0 ? number_format($item['atraso'], 2) : '-'}}</td>
                    <td class="text-right bold nowrap">{{number_format($item['atraso'] + $item['cuota_hoy'], 2)}}</td>
                    <td class="text-center bold">{{$item['cant_venc']}}</td>
                    <td class="text-center bold num-red">{{$item['dias_atraso']}}</td>
                    <td class="text-right nowrap">{{number_format($item['saldo_total'], 2)}}</td>
                    <td class="text-center"><strong>D</strong></td>
                </tr>
            @empty
                <tr><td colspan="12" class="text-center">No hay registros</td></tr>
            @endforelse
            @if($cuotasDia->count() > 0)
                <tr class="subtotal-row">
                    <td colspan="5" class="text-right">SUBTOTAL ({{$cuotasDia->count()}}):</td>
                    <td class="text-right">{{number_format($sC1, 2)}}</td><td class="text-right">{{number_format($sA1, 2)}}</td>
                    <td class="text-right">{{number_format($sCA1, 2)}}</td><td class="text-center">{{$sV1}}</td>
                    <td></td><td class="text-right">{{number_format($sP1, 2)}}</td><td></td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- SECCIÓN 2 --}}
    @php $sA2=0; $sV2=0; $sP2=0; @endphp
    <div class="section-title mora">CLIENTES EN MORA</div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Cliente</th><th style="width: 18%;">Dirección</th><th style="width: 9%;">Teléfono</th>
                <th style="width: 8%;">F. Desemb.</th><th style="width: 8%;">F. Venc.</th>
                <th class="text-right">Atraso Total</th><th class="text-center">C.Venc</th><th class="text-center">Días Atr.</th>
                <th class="text-center">Último Abono</th><th class="text-right" style="width: 8%;">Saldo Total</th><th class="text-center">Est</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cuotasMora as $item)
                @php $p = $item['prestamo']; $sA2+=$item['atraso']; $sV2+=$item['cant_venc']; $sP2+=$item['saldo_total']; @endphp
                <tr class="row-mora">
                    <td class="bold">{{$p->cliente->full_name ?? 'N/A'}}</td>
                    <td style="font-size: 6.5px;">{{$p->cliente->direccion ?? '-'}}</td>
                    <td class="nowrap">{{$p->cliente->telefono1 ?? '-'}}</td>
                    <td class="text-center nowrap">{{fecha_d_m_Y($p->fecha_desembolso)}}</td>
                    <td class="text-center nowrap">{{$item['fecha_venc'] ? fecha_d_m_Y($item['fecha_venc']) : "-"}}</td>
                    <td class="text-right">-</td>
                    <td class="text-right bold num-red nowrap">{{number_format($item['atraso'], 2)}}</td>
                    <td class="text-center">-</td>
                    <td class="text-center bold">{{$item['cant_venc']}}</td>
                    <td class="text-center bold num-red">{{$item['dias_atraso']}}</td>
                    <td class="text-center nowrap">{{$item['ultimo_abono'] ? fecha_d_m_Y($item['ultimo_abono']) : '-'}}</td>
                    <td class="text-right nowrap">{{number_format($item['saldo_total'], 2)}}</td>
                    <td class="text-center"><strong>M</strong></td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center">No hay registros</td></tr>
            @endforelse
            @if($cuotasMora->count() > 0)
                <tr class="subtotal-row">
                    <td colspan="5" class="text-right">SUBTOTAL ({{$cuotasMora->count()}}):</td>
                    <td class="text-right">{{number_format($sA2, 2)}}</td><td class="text-center">{{$sV2}}</td>
                    <td></td><td></td><td class="text-right">{{number_format($sP2, 2)}}</td><td></td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- SECCIÓN 3 --}}
    @php $sV3=0; $sP3=0; @endphp
    <div class="section-title vencido">PRÉSTAMOS VENCIDOS</div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Cliente</th><th style="width: 18%;">Dirección</th><th style="width: 9%;">Teléfono</th>
                <th style="width: 8%;">F. Desemb.</th><th style="width: 8%;">F. Venc.</th>
                <th class="text-center">C.Venc</th><th class="text-center">Días Atr.</th>
                <th class="text-center">Último Abono</th><th class="text-right" style="width: 8%;">Saldo Total</th><th class="text-center">Est</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cuotasVencidas as $item)
                @php $p = $item['prestamo']; $sV3+=$item['cant_venc']; $sP3+=$item['saldo_total']; @endphp
                <tr class="row-vencido">
                    <td class="bold">{{$p->cliente->full_name ?? 'N/A'}}</td>
                    <td style="font-size: 6.5px;">{{$p->cliente->direccion ?? '-'}}</td>
                    <td class="nowrap">{{$p->cliente->telefono1 ?? '-'}}</td>
                    <td class="text-center nowrap">{{fecha_d_m_Y($p->fecha_desembolso)}}</td>
                    <td class="text-center nowrap num-red bold">{{$item['fecha_venc'] ? fecha_d_m_Y($item['fecha_venc']) : "-"}}</td>
                    <td class="text-center bold">{{$item['cant_venc']}}</td>
                    <td class="text-center bold num-red">{{$item['dias_atraso']}}</td>
                    <td class="text-center nowrap">{{$item['ultimo_abono'] ? fecha_d_m_Y($item['ultimo_abono']) : '-'}}</td>
                    <td class="text-right bold num-red nowrap">{{number_format($item['saldo_total'], 2)}}</td>
                    <td class="text-center"><strong>V</strong></td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center">No hay registros</td></tr>
            @endforelse
            @if($cuotasVencidas->count() > 0)
                <tr class="subtotal-row">
                    <td colspan="5" class="text-right">SUBTOTAL ({{$cuotasVencidas->count()}}):</td>
                    <td class="text-center">{{$sV3}}</td><td></td><td></td><td class="text-right">{{number_format($sP3, 2)}}</td><td></td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="gran-total-section">
        <h2>RESUMEN GENERAL</h2>
        <div class="stats">Día: {{$cuotasDia->count()}} | Mora: {{$cuotasMora->count()}} | Vencidos: {{$cuotasVencidas->count()}}</div>
        <div class="stats">Saldo Total Cartera: C$ {{number_format($sP1+$sP2+$sP3, 2)}} | Atraso (Día+Mora): C$ {{number_format($sA1+$sA2, 2)}}</div>
    </div>
</body>
</html>
