<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Cuotas Vencidas</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 8px; color: #333; padding: 10px; }
        .header { width: 100%; margin-bottom: 15px; border-bottom: 2px solid #1f9cb5; padding-bottom: 10px; }
        .header-content { display: table; width: 100%; }
        .logo-section { display: table-cell; width: 150px; vertical-align: middle; }
        .logo-section img { width: 140px; }
        .title-section { display: table-cell; vertical-align: middle; text-align: center; }
        .title-section h1 { font-size: 18px; color: #1f9cb5; margin-bottom: 5px; }
        .title-section p { font-size: 9px; color: #666; }
        .filters-section { background-color: #f8f9fa; padding: 8px; margin-bottom: 10px; border-left: 3px solid #1f9cb5; }
        .filters-section h3 { font-size: 10px; color: #1f9cb5; margin-bottom: 5px; }
        .filter-item { display: inline-block; margin-right: 15px; font-size: 9px; }
        .cobrador-section { margin-top: 15px; page-break-inside: auto; }
        .cobrador-header { background-color: #1f9cb5; color: white; padding: 6px 8px; font-size: 10px; font-weight: bold; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 7px; }
        table thead { background-color: #d1ecf1; }
        table thead th { padding: 5px 3px; text-align: left; font-weight: bold; border: 1px solid #dee2e6; }
        table tbody td { padding: 3px 2px; border: 1px solid #dee2e6; }
        table tbody tr:nth-child(even) { background-color: #f8f9fa; }
        .subtotal-row { background-color: #d1ecf1 !important; font-weight: bold; }
        .subtotal-row td { padding: 6px 3px; border-top: 2px solid #1f9cb5; }
        .gran-total-section { margin-top: 15px; background-color: #1f9cb5; color: white; padding: 10px; }
        .gran-total-section h2 { font-size: 14px; margin-bottom: 8px; text-align: center; }
        .totales-grid { display: table; width: 100%; font-size: 10px; }
        .total-item { display: table-cell; text-align: center; padding: 5px; }
        .total-item .label { font-size: 8px; opacity: 0.9; }
        .total-item .value { font-size: 12px; font-weight: bold; margin-top: 3px; }
        .footer { margin-top: 15px; padding-top: 8px; border-top: 1px solid #dee2e6; font-size: 8px; color: #666; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo-section">
                <img src="{{public_path('assets/img/LogoCrediNica.png')}}" alt="CrediNica">
            </div>
            <div class="title-section">
                <h1>LISTA DE CUOTAS VENCIDAS</h1>
                <p>Reporte de Cuotas con Saldo Pendiente</p>
            </div>
        </div>
    </div>

    <div class="filters-section">
        <h3>FILTROS APLICADOS:</h3>
        <div class="filter-item">
            <strong>Fecha de Corte:</strong> {{fecha_d_m_Y(!$fecha_corte?date('Y-m-d'):$fecha_corte)}}
        </div>
        <div class="filter-item">
            <strong>Cliente:</strong> {{request('cliente') ? \App\Models\User::find(decode(request('cliente')))->full_name : 'Todos'}}
        </div>
        <div class="filter-item">
            <strong>Cobrador:</strong> {{request('cobrador') ? \App\Models\User::find(decode(request('cobrador')))->full_name : 'Todos'}}
        </div>
    </div>

    @php
        $cuotasPorCobrador = collect($cuotasVencidas)->groupBy('agente_id');
        $granTotalCuota = 0;
        $granTotalPagado = 0;
        $granTotalPendiente = 0;
        $totalRegistros = 0;
    @endphp

    @foreach($cuotasPorCobrador as $cobradorId => $cuotasGrupo)
        @php
            $nombreCobrador = $cuotasGrupo->first()->agente_nombre ?? 'SIN ASIGNAR';
            $subtotalCuota = 0;
            $subtotalPagado = 0;
            $subtotalPendiente = 0;
        @endphp
        
        <div class="cobrador-section">
            <div class="cobrador-header">COBRADOR: {{strtoupper($nombreCobrador)}}</div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 6%;">Cuota</th>
                        <th style="width: 8%;">Préstamo</th>
                        <th style="width: 22%;">Cliente</th>
                        <th style="width: 10%;">Fecha Cuota</th>
                        <th style="width: 10%;">Días Venc.</th>
                        <th style="width: 13%;" class="text-right">Cuota</th>
                        <th style="width: 13%;" class="text-right">Pagado</th>
                        <th style="width: 13%;" class="text-right">Pendiente</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cuotasGrupo as $cuota)
                        @php
                            $pagado = $cuota->suma_abono ?? 0;
                            $pendiente = $cuota->monto_cuota - $pagado;
                            $diasVencidos = \Carbon\Carbon::parse($cuota->fecha_cuota)->diffInDays(now());
                            $subtotalCuota += $cuota->monto_cuota;
                            $subtotalPagado += $pagado;
                            $subtotalPendiente += $pendiente;
                            $totalRegistros++;
                        @endphp
                        <tr>
                            <td class="text-center">{{$cuota->numero_cuota}}</td>
                            <td>#{{$cuota->consecutivo}}</td>
                            <td>{{$cuota->cliente_nombre}}</td>
                            <td class="text-center">{{fecha_d_m_Y($cuota->fecha_cuota)}}</td>
                            <td class="text-center">{{$diasVencidos}} días</td>
                            <td class="text-right">C$ {{number_format($cuota->monto_cuota, 2)}}</td>
                            <td class="text-right">C$ {{number_format($pagado, 2)}}</td>
                            <td class="text-right">C$ {{number_format($pendiente, 2)}}</td>
                        </tr>
                    @endforeach
                    
                    <tr class="subtotal-row">
                        <td colspan="5" class="text-right"><strong>SUBTOTAL {{strtoupper($nombreCobrador)}} ({{count($cuotasGrupo)}} cuotas):</strong></td>
                        <td class="text-right"><strong>C$ {{number_format($subtotalCuota, 2)}}</strong></td>
                        <td class="text-right"><strong>C$ {{number_format($subtotalPagado, 2)}}</strong></td>
                        <td class="text-right"><strong>C$ {{number_format($subtotalPendiente, 2)}}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        @php
            $granTotalCuota += $subtotalCuota;
            $granTotalPagado += $subtotalPagado;
            $granTotalPendiente += $subtotalPendiente;
        @endphp
    @endforeach

    <table style="margin-top: 20px;">
        <thead>
            <tr style="background-color: #333; color: white;">
                <th colspan="5" style="background-color: #333; color: white; text-align: right; padding: 8px;">TOTALES GENERALES VENCIDOS:</th>
                <th style="background-color: #333; color: white; text-align: right; width: 13%;">C$ {{number_format($granTotalCuota, 2)}}</th>
                <th style="background-color: #333; color: white; text-align: right; width: 13%;">C$ {{number_format($granTotalPagado, 2)}}</th>
                <th style="background-color: #333; color: white; text-align: right; width: 13%;">C$ {{number_format($granTotalPendiente, 2)}}</th>
            </tr>
        </thead>
    </table>

    <div style="margin-top: 5px; font-size: 8px; color: #666; text-align: center;">
        Total de Cobradores: {{$cuotasPorCobrador->count()}} | Total de Registros: {{$totalRegistros}}
    </div>

    <div class="footer">
        <p><strong>Generado por:</strong> {{Auth::user()->full_name}} | <strong>Fecha:</strong> {{fecha_d_m_Y_h_i(now())}}</p>
    </div>
</body>
</html>
