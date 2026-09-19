<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cobros del Día</title>
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
            color: #1f9cb5;
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
            border-left: 3px solid #1f9cb5;
        }
        
        .filters-section h3 {
            font-size: 10px;
            color: #1f9cb5;
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
            page-break-inside: avoid;
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
        
        table thead { background-color: #d1ecf1; }
        
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
        
        table tbody tr.vencida {
            background-color: #f8d7da;
        }
        
        .subtotal-row {
            background-color: #d1ecf1 !important;
            font-weight: bold;
        }
        
        .subtotal-row td {
            padding: 6px 3px;
            border-top: 2px solid #1f9cb5;
        }
        
        .gran-total-section {
            margin-top: 15px;
            background-color: #1f9cb5;
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
                <h1>COBROS DEL DÍA</h1>
                <p>Reporte de Cuotas por Cobrar</p>
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="filters-section">
        <h3>FILTROS APLICADOS:</h3>
        <div class="filter-item">
            <strong>Fecha Desde:</strong> {{request('fecha') ? fecha_d_m_Y(request('fecha')) : fecha_d_m_Y(date('Y-m-d'))}}
        </div>
        <div class="filter-item">
            <strong>Fecha Hasta:</strong> {{request('fecha2') ? fecha_d_m_Y(request('fecha2')) : fecha_d_m_Y(date('Y-m-d'))}}
        </div>
        <div class="filter-item">
            <strong>Cobrador:</strong> {{request('cobrador') ? \App\Models\User::find(decode(request('cobrador')))->full_name : 'Todos'}}
        </div>
    </div>

    @php
        // Agrupar por cobrador
        $cuotasPorCobrador = $prestamoCuotas->groupBy(function($cuota) {
            return $cuota->prestamo->agente->id;
        });
        
        $granTotal = 0;
        $totalRegistros = 0;
        $totalCobradores = $cuotasPorCobrador->count();
    @endphp

    <!-- DATOS AGRUPADOS POR COBRADOR -->
    @foreach($cuotasPorCobrador as $cobradorId => $cuotas)
        @php
            $cobrador = $cuotas->first()->prestamo->agente;
            $subtotal = 0;
        @endphp
        
        <div class="cobrador-section">
            <div class="cobrador-header">
                COBRADOR: {{strtoupper($cobrador->full_name)}}
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;"># Préstamo</th>
                        <th style="width: 8%;"># Cuota</th>
                        <th style="width: 20%;">Cliente</th>
                        <th style="width: 22%;">Dirección</th>
                        <th style="width: 15%;">Dep / Mun</th>
                        <th style="width: 10%;">Fecha Cuota</th>
                        <th style="width: 8%;" class="text-right">Monto Cuota</th>
                        <th style="width: 9%;" class="text-right">Pendiente</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cuotas as $prestamo)
                        @php
                            $subtotal += $prestamo->monto_pendiente_cuota;
                            $totalRegistros++;
                        @endphp
                        <tr @if($prestamo->fecha_cuota < \Carbon\Carbon::now()->toDateString()) class="vencida" @endif>
                            <td>{{$prestamo->prestamo->consecutivo}}</td>
                            <td class="text-center">{{$prestamo->numero_cuota}} / {{count($prestamo->prestamo->cuotas)}}</td>
                            <td>{{$prestamo->prestamo->cliente->full_name}}</td>
                            <td>{{$prestamo->prestamo->cliente->direccion}}</td>
                            <td>{{$prestamo->prestamo->cliente->departamento_municipio->departamento->nombre}} / {{$prestamo->prestamo->cliente->departamento_municipio->nombre}}</td>
                            <td class="text-center">{{fecha_d_m_Y($prestamo->fecha_cuota)}}</td>
                            <td class="text-right">{{$prestamo->prestamo->moneda}} {{number_format($prestamo->monto_cuota, 2)}}</td>
                            <td class="text-right">{{$prestamo->prestamo->moneda}} {{number_format($prestamo->monto_pendiente_cuota, 2)}}</td>
                        </tr>
                    @endforeach
                    
                    <!-- SUBTOTAL POR COBRADOR -->
                    <tr class="subtotal-row">
                        <td colspan="7" class="text-right"><strong>SUBTOTAL {{strtoupper($cobrador->full_name)}}:</strong></td>
                        <td class="text-right"><strong>{{$cuotas->first()->prestamo->moneda}} {{number_format($subtotal, 2)}}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        @php
            $granTotal += $subtotal;
        @endphp
    @endforeach

    <table style="margin-top: 20px;">
        <thead>
            <tr style="background-color: #333; color: white;">
                <th colspan="7" style="background-color: #333; color: white; text-align: right; padding: 8px;">GRAN TOTAL GENERAL A COBRAR:</th>
                <th style="background-color: #333; color: white; text-align: right; width: 9%;">C$ {{number_format($granTotal, 2)}}</th>
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
