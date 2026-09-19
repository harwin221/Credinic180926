<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Lista de Desembolsos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
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
            font-size: 7px;
        }
        
        table thead {
            background-color: #d1ecf1;
        }
        
        table thead th {
            padding: 5px 2px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #dee2e6;
            font-size: 7px;
        }
        
        table tbody td {
            padding: 3px 2px;
            border: 1px solid #dee2e6;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
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
        
        .badge {
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 7px;
        }
        
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        
        .badge-secondary {
            background-color: #6c757d;
            color: white;
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
                <h1>LISTA DE DESEMBOLSOS</h1>
                <p>Reporte de Préstamos Activos</p>
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="filters-section">
        <h3>FILTROS APLICADOS:</h3>
        <div class="filter-item">
            <strong>Fecha Desde:</strong> {{request('fecha_inicio') ? fecha_d_m_Y(request('fecha_inicio')) : 'N/A'}}
        </div>
        <div class="filter-item">
            <strong>Fecha Hasta:</strong> {{request('fecha_fin') ? fecha_d_m_Y(request('fecha_fin')) : 'N/A'}}
        </div>
        <div class="filter-item">
            <strong>Cobrador:</strong> {{request('cobrador') ? \App\Models\User::find(decode(request('cobrador')))->full_name : 'Todos'}}
        </div>
        <div class="filter-item">
            <strong>Estado:</strong> 
            @if(request('estado') == 1) Activo
            @elseif(request('estado') == 2) Cancelado
            @else Todos
            @endif
        </div>
    </div>

    @php
        // Agrupar por cobrador
        $prestamosPorCobrador = $prestamos->groupBy('agente_id');
        
        $granTotalMontoPrestamo = 0;
        $granTotalPendiente = 0;
        $totalRegistros = 0;
        $totalCobradores = $prestamosPorCobrador->count();
    @endphp

    <!-- DATOS AGRUPADOS POR COBRADOR -->
    @foreach($prestamosPorCobrador as $cobradorId => $prestamosGrupo)
        @php
            $nombreCobrador = $prestamosGrupo->first()->agente_nombre ?? 'SIN ASIGNAR';
            $subtotalMontoPrestamo = 0;
            $subtotalPendiente = 0;
        @endphp
        
        <div class="cobrador-section">
            <div class="cobrador-header">
                COBRADOR: {{strtoupper($nombreCobrador)}}
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 7%;"># Préstamo</th>
                        <th style="width: 6%;">Tipo</th>
                        <th style="width: 8%;">Destino</th>
                        <th style="width: 4%;"># Cuotas</th>
                        <th style="width: 18%;">Cliente</th>
                        <th style="width: 8%;">Frecuencia</th>
                        <th style="width: 8%;">F. Creado</th>
                        <th style="width: 8%;">F. Desemb.</th>
                        <th style="width: 8%;" class="text-right">Cuota</th>
                        <th style="width: 10%;">Vendedor</th>
                        <th style="width: 8%;" class="text-right">M. Préstamo</th>
                        <th style="width: 7%;" class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prestamosGrupo as $prestamo)
                        @php
                            $subtotalMontoPrestamo += $prestamo->monto_prestamo;
                            // Cálculo del pendiente usando los datos planos de la consulta
                            $pendiente = $prestamo->monto_financiado - ($prestamo->suma_abonos ?? 0);
                            $subtotalPendiente += $pendiente;
                            $totalRegistros++;
                        @endphp
                        <tr>
                            <td>{{$prestamo->consecutivo}}</td>
                            <td>{{$prestamo->tipo_prestamo_texto}}</td>
                            <td>{{$prestamo->tipo_destino_texto}}</td>
                            <td class="text-center">{{$prestamo->plazo}}</td>
                            <td>
                                {{$prestamo->cliente_nombre}}<br>
                                <small>Pend: {{$prestamo->moneda}} {{number_format($pendiente, 2)}}</small>
                            </td>
                            <td>{{$prestamo->forma_pago}}</td>
                            <td class="text-center">{{fecha_d_m_Y($prestamo->fecha_prestamo)}}</td>
                            <td class="text-center">{{fecha_d_m_Y($prestamo->fecha_desembolso)}}</td>
                            <td class="text-right">{{$prestamo->moneda}} {{number_format($prestamo->monto_cuota, 2)}}</td>
                            <td>{{$prestamo->vendedor_nombre}}</td>
                            <td class="text-right">{{$prestamo->moneda}} {{number_format($prestamo->monto_prestamo, 2)}}</td>
                            <td class="text-center">
                                @if($prestamo->estado==1)
                                    <span class="badge badge-success">Activo</span>
                                @elseif($prestamo->estado==2)
                                    <span class="badge badge-secondary">Cancelado</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    
                    <!-- SUBTOTAL POR COBRADOR -->
                    <tr class="subtotal-row">
                        <td colspan="10" class="text-right"><strong>SUBTOTAL {{strtoupper($nombreCobrador)}}:</strong></td>
                        <td class="text-right"><strong>{{$prestamosGrupo->first()->moneda}} {{number_format($subtotalMontoPrestamo, 2)}}</strong></td>
                        <td></td>
                    </tr>
                    <tr class="subtotal-row">
                        <td colspan="10" class="text-right"><strong>PENDIENTE {{strtoupper($nombreCobrador)}}:</strong></td>
                        <td class="text-right"><strong>{{$prestamosGrupo->first()->moneda}} {{number_format($subtotalPendiente, 2)}}</strong></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        @php
            $granTotalMontoPrestamo += $subtotalMontoPrestamo;
            $granTotalPendiente += $subtotalPendiente;
        @endphp
    @endforeach

    <!-- GRAN TOTAL -->
    <div class="gran-total-section">
        <h2>GRAN TOTAL DESEMBOLSADO: C$ {{number_format($granTotalMontoPrestamo, 2)}}</h2>
        <h2>GRAN TOTAL PENDIENTE: C$ {{number_format($granTotalPendiente, 2)}}</h2>
        <div class="stats">
            Total de Cobradores: {{$totalCobradores}} | Total de Préstamos: {{$totalRegistros}}
        </div>
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
