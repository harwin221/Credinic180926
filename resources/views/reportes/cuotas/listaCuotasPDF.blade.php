<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Lista de Abonos</title>
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
        .filter-item strong { color: #333; }
        .cobrador-section { margin-top: 15px; page-break-inside: auto; }
        .cobrador-header { background-color: #1f9cb5; color: white; padding: 6px 8px; font-size: 10px; font-weight: bold; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 7px; }
        table thead { background-color: #d1ecf1; }
        table thead th { padding: 5px 3px; text-align: left; font-weight: bold; border: 1px solid #dee2e6; font-size: 7px; }
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
        .badge { padding: 2px 4px; border-radius: 3px; font-size: 6px; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-info { background-color: #17a2b8; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo-section">
                <img src="{{public_path('assets/img/LogoCrediNica.png')}}" alt="CrediNica">
            </div>
            <div class="title-section">
                <h1>LISTA DE ABONOS</h1>
                <p>Reporte Detallado de Abonos a Cuotas</p>
            </div>
        </div>
    </div>

    <div class="filters-section">
        <h3>FILTROS APLICADOS:</h3>
        <div class="filter-item">
            <strong>Fecha Desde:</strong> {{request('desde') ? fecha_d_m_Y(request('desde')) : 'N/A'}}
        </div>
        <div class="filter-item">
            <strong>Fecha Hasta:</strong> {{request('hasta') ? fecha_d_m_Y(request('hasta')) : 'N/A'}}
        </div>
        <div class="filter-item">
            <strong>Cobrador:</strong> {{request('cobrador') ? \App\Models\User::find(decode(request('cobrador')))->full_name : 'Todos'}}
        </div>
    </div>

    @php
        $abonosPorCobrador = $abonos->groupBy(function($abono) {
            return $abono->prestamo_cuota->prestamo->agente->id;
        });
        $granTotalCuota = 0;
        $granTotalPagado = 0;
        $granTotalPendiente = 0;
        $totalRegistros = 0;
        $totalCobradores = $abonosPorCobrador->count();
    @endphp

    @foreach($abonosPorCobrador as $cobradorId => $abonosGrupo)
        @php
            $cobrador = $abonosGrupo->first()->prestamo_cuota->prestamo->agente;
            $subtotalCuota = 0;
            $subtotalPagado = 0;
            $subtotalPendiente = 0;
        @endphp
        
        <div class="cobrador-section">
            <div class="cobrador-header">
                COBRADOR: {{strtoupper($cobrador->full_name)}}
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 6%;"># Cuota</th>
                        <th style="width: 8%;"># Préstamo</th>
                        <th style="width: 20%;">Cliente</th>
                        <th style="width: 12%;">Fecha Abono</th>
                        <th style="width: 10%;">Estado Abono</th>
                        <th style="width: 10%;">Estado Cuota</th>
                        <th style="width: 11%;" class="text-right">Cuota</th>
                        <th style="width: 11%;" class="text-right">Pagado</th>
                        <th style="width: 12%;" class="text-right">Pendiente</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($abonosGrupo as $abono)
                        @php
                            $subtotalCuota += $abono->prestamo_cuota->monto_cuota;
                            $subtotalPagado += $abono->monto_abono;
                            $subtotalPendiente += $abono->prestamo_cuota->monto_pendiente_cuota;
                            $totalRegistros++;
                        @endphp
                        <tr>
                            <td class="text-center">{{$abono->prestamo_cuota->numero_cuota}}</td>
                            <td>{{$abono->prestamo_cuota->prestamo->consecutivo}}</td>
                            <td>{{$abono->prestamo_cuota->prestamo->cliente->full_name}}</td>
                            <td class="text-center">{{fecha_d_m_Y_h_i($abono->fecha_abono)}}</td>
                            <td class="text-center">
                                @if(isset($abono->abono))
                                    @if($abono->abono->estado == 2)
                                        <span class="badge badge-danger">Anulado</span>
                                    @else
                                        <span class="badge badge-info">{{$abono->abono->estado_abono}}</span>
                                    @endif
                                @endif
                            </td>
                            <td class="text-center">{{$abono->prestamo_cuota->estado_cuota}}</td>
                            <td class="text-right">{{$abono->prestamo_cuota->prestamo->moneda}} {{number_format($abono->prestamo_cuota->monto_cuota, 2)}}</td>
                            <td class="text-right">{{$abono->prestamo_cuota->prestamo->moneda}} {{number_format($abono->monto_abono, 2)}}</td>
                            <td class="text-right">{{$abono->prestamo_cuota->prestamo->moneda}} {{number_format($abono->prestamo_cuota->monto_pendiente_cuota, 2)}}</td>
                        </tr>
                    @endforeach
                    
                    <tr class="subtotal-row">
                        <td colspan="6" class="text-right"><strong>SUBTOTAL {{strtoupper($cobrador->full_name)}}:</strong></td>
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
                <th colspan="6" style="background-color: #333; color: white; text-align: right; padding: 8px;">TOTALES GENERALES DE CARTERA:</th>
                <th style="background-color: #333; color: white; text-align: right; width: 11%;">C$ {{number_format($granTotalCuota, 2)}}</th>
                <th style="background-color: #333; color: white; text-align: right; width: 11%;">C$ {{number_format($granTotalPagado, 2)}}</th>
                <th style="background-color: #333; color: white; text-align: right; width: 12%;">C$ {{number_format($granTotalPendiente, 2)}}</th>
            </tr>
        </thead>
    </table>

    <div style="margin-top: 5px; font-size: 8px; color: #666; text-align: center;">
        Total de Cobradores: {{$totalCobradores}} | Total de Registros: {{$totalRegistros}}
    </div>

    <div class="footer">
        <p>
            <strong>Generado por:</strong> {{Auth::user()->full_name}} | 
            <strong>Fecha de Generación:</strong> {{fecha_d_m_Y_h_i(now())}}
        </p>
    </div>
</body>
</html>
