<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Préstamos Vencidos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #333;
            padding: 10px;
        }
        
        .header {
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 2px solid #1f9cb5;
            padding-bottom: 10px;
        }
        
        .header-content {
            display: table;
            width: 100%;
        }
        
        .logo-section {
            display: table-cell;
            width: 150px;
            vertical-align: middle;
        }
        
        .logo-section img {
            width: 140px;
            height: auto;
        }
        
        .title-section {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
        
        .title-section h1 {
            font-size: 18px;
            color: #dc3545;
            margin-bottom: 5px;
        }
        
        .title-section p {
            font-size: 9px;
            color: #666;
        }
        
        .filters-section {
            background-color: #f8f9fa;
            padding: 8px;
            margin-bottom: 10px;
            border-left: 3px solid #dc3545;
        }
        
        .filters-section h3 {
            font-size: 10px;
            color: #dc3545;
            margin-bottom: 5px;
        }
        
        .filter-item {
            display: inline-block;
            margin-right: 15px;
            font-size: 9px;
        }
        
        .filter-item strong {
            color: #333;
        }
        
        .cobrador-section {
            margin-top: 15px;
            page-break-inside: auto;
        }
        
        .cobrador-header {
            background-color: #1f9cb5;
            color: white;
            padding: 6px 8px;
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8px;
        }
        
        table thead {
            background-color: #d1ecf1;
        }
        
        table thead th {
            padding: 5px 3px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #dee2e6;
            font-size: 8px;
        }
        
        table tbody td {
            padding: 4px 3px;
            border: 1px solid #dee2e6;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        table tbody tr {
            background-color: #f8d7da;
        }
        
        .subtotal-row {
            background-color: #f5c6cb !important;
            font-weight: bold;
        }
        
        .subtotal-row td {
            padding: 6px 3px;
            border-top: 2px solid #dc3545;
        }
        
        .gran-total-section {
            margin-top: 15px;
            background-color: #dc3545;
            color: white;
            padding: 10px;
            text-align: center;
        }
        
        .gran-total-section h2 {
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .gran-total-section .stats {
            font-size: 9px;
            margin-top: 5px;
        }
        
        .footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #dee2e6;
            font-size: 8px;
            color: #666;
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="header">
        <div class="header-content">
            <div class="logo-section">
                <img src="{{public_path('assets/img/LogoCrediNica.png')}}" alt="CrediNica">
            </div>
            <div class="title-section">
                <h1>PRÉSTAMOS VENCIDOS</h1>
                <p>Reporte de Préstamos con Todas las Cuotas Vencidas</p>
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="filters-section">
        <h3>FILTROS APLICADOS:</h3>
        <div class="filter-item">
            <strong>Cliente:</strong> {{request('cliente') ? \App\Models\User::find(decode(request('cliente')))->full_name : 'Todos'}}
        </div>
        <div class="filter-item">
            <strong>Cobrador:</strong> {{request('cobrador') ? \App\Models\User::find(decode(request('cobrador')))->full_name : 'Todos'}}
        </div>
        <div class="filter-item">
            <strong>Frecuencia:</strong> {{request('frecuencia') ? ['1'=>'Diario','2'=>'Semanal','3'=>'Quincenal','4'=>'Mensual','5'=>'Trimestral','6'=>'Bimestral','7'=>'Catorcenal'][request('frecuencia')] : 'Todas'}}
        </div>
    </div>

    @php
        // Agrupar por cobrador
        $prestamosPorCobrador = $prestamosVencidos->groupBy(function($prestamo) {
            return $prestamo->agente->id;
        });
        
        $granTotalMonto = 0;
        $granTotalFinanciado = 0;
        $granTotalAbonado = 0;
        $granTotalPendiente = 0;
        $totalRegistros = 0;
        $totalCobradores = $prestamosPorCobrador->count();
    @endphp

    <!-- DATOS AGRUPADOS POR COBRADOR -->
    @foreach($prestamosPorCobrador as $cobradorId => $prestamos)
        @php
            $cobrador = $prestamos->first()->agente;
            $subtotalMonto = 0;
            $subtotalFinanciado = 0;
            $subtotalAbonado = 0;
            $subtotalPendiente = 0;
        @endphp
        
        <div class="cobrador-section">
            <div class="cobrador-header">
                COBRADOR: {{strtoupper($cobrador->full_name)}}
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%;"># Préstamo</th>
                        <th style="width: 25%;">Cliente</th>
                        <th style="width: 12%;">Frecuencia</th>
                        <th style="width: 10%;" class="text-right">Monto Préstamo</th>
                        <th style="width: 10%;" class="text-right">Financiado</th>
                        <th style="width: 10%;" class="text-right">Abonado</th>
                        <th style="width: 10%;" class="text-right">Pendiente</th>
                        <th style="width: 10%;" class="text-center">Fecha Préstamo</th>
                        <th style="width: 13%;" class="text-center">Fecha Última Cuota</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prestamos as $prestamo)
                        @php
                            $pendiente = $prestamo->monto_financiado - $prestamo->suma_abonos;
                            $subtotalMonto += $prestamo->monto_prestamo;
                            $subtotalFinanciado += $prestamo->monto_financiado;
                            $subtotalAbonado += $prestamo->suma_abonos;
                            $subtotalPendiente += $pendiente;
                            $totalRegistros++;
                        @endphp
                        <tr>
                            <td>{{$prestamo->consecutivo}}</td>
                            <td>{{$prestamo->cliente->full_name}}</td>
                            <td>{{$prestamo->forma_pago}}</td>
                            <td class="text-right">{{$prestamo->moneda}} {{number_format($prestamo->monto_prestamo, 2)}}</td>
                            <td class="text-right">{{$prestamo->moneda}} {{number_format($prestamo->monto_financiado, 2)}}</td>
                            <td class="text-right">{{$prestamo->moneda}} {{number_format($prestamo->suma_abonos, 2)}}</td>
                            <td class="text-right">{{$prestamo->moneda}} {{number_format($pendiente, 2)}}</td>
                            <td class="text-center">{{fecha_d_m_Y($prestamo->fecha_prestamo)}}</td>
                            <td class="text-center">{{$prestamo->cuotas()->orderBy('id', 'desc')->first() ? fecha_d_m_Y($prestamo->cuotas()->orderBy('id', 'desc')->first()->fecha_cuota) : "-"}}</td>
                        </tr>
                    @endforeach
                    
                    <!-- SUBTOTAL POR COBRADOR -->
                    <tr class="subtotal-row">
                        <td colspan="3" class="text-right"><strong>SUBTOTAL {{strtoupper($cobrador->full_name)}}:</strong></td>
                        <td class="text-right"><strong>{{$prestamos->first()->moneda}} {{number_format($subtotalMonto, 2)}}</strong></td>
                        <td class="text-right"><strong>{{$prestamos->first()->moneda}} {{number_format($subtotalFinanciado, 2)}}</strong></td>
                        <td class="text-right"><strong>{{$prestamos->first()->moneda}} {{number_format($subtotalAbonado, 2)}}</strong></td>
                        <td class="text-right"><strong>{{$prestamos->first()->moneda}} {{number_format($subtotalPendiente, 2)}}</strong></td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        @php
            $granTotalMonto += $subtotalMonto;
            $granTotalFinanciado += $subtotalFinanciado;
            $granTotalAbonado += $subtotalAbonado;
            $granTotalPendiente += $subtotalPendiente;
        @endphp
    @endforeach

    <table style="margin-top: 20px;">
        <thead>
            <tr style="background-color: #333; color: white;">
                <th colspan="3" style="background-color: #333; color: white; text-align: right; padding: 8px;">TOTALES GENERALES VENCIDOS:</th>
                <th style="background-color: #333; color: white; text-align: right; width: 10%;">C$ {{number_format($granTotalMonto, 2)}}</th>
                <th style="background-color: #333; color: white; text-align: right; width: 10%;">C$ {{number_format($granTotalFinanciado, 2)}}</th>
                <th style="background-color: #333; color: white; text-align: right; width: 10%;">C$ {{number_format($granTotalAbonado, 2)}}</th>
                <th style="background-color: #333; color: white; text-align: right; width: 10%;">C$ {{number_format($granTotalPendiente, 2)}}</th>
                <th colspan="2" style="background-color: #333; color: white;"></th>
            </tr>
        </thead>
    </table>

    <div style="margin-top: 5px; font-size: 8px; color: #666; text-align: center;">
        Total de Cobradores: {{$totalCobradores}} | Total de Registros: {{$totalRegistros}}
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <p>
            <strong>Generado por:</strong> {{Auth::user()->full_name}} | 
            <strong>Fecha de Generación:</strong> {{fecha_d_m_Y_h_i(now())}}
        </p>
    </div>
</body>
</html>
