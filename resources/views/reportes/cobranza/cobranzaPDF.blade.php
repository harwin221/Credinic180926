<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cobros sin abono</title>
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
            padding: 4px 2px;
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
            padding: 5px 2px;
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
                <h1>COBROS SIN ABONO</h1>
                <p>Reporte de Cuotas por Frecuencia de Pago</p>
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="filters-section">
        <h3>FILTROS APLICADOS:</h3>
        <div class="filter-item">
            <strong>Fecha Inicio:</strong> {{request('fecha_inicio') ? fecha_d_m_Y(request('fecha_inicio')) : '-'}}
        </div>
        <div class="filter-item">
            <strong>Fecha Fin:</strong> {{request('fecha_fin') ? fecha_d_m_Y(request('fecha_fin')) : '-'}}
        </div>
        <div class="filter-item">
            <strong>Cobrador:</strong> {{request('cobrador') ? \App\Models\User::find(decode(request('cobrador')))->full_name : 'Todos'}}
        </div>
    </div>

    @php
        // Agrupar por cobrador
        $cuotasPorCobrador = $cuotas->groupBy(function($cuota) {
            return $cuota->prestamo->agente->id;
        });
        
        $granTotalDiario = 0;
        $granTotalSemanal = 0;
        $granTotalQuincenal = 0;
        $granTotalMensual = 0;
        $granTotalTrimestral = 0;
        $granTotalBimestral = 0;
        $granTotalCatorcenal = 0;
        $totalRegistros = 0;
        $totalCobradores = $cuotasPorCobrador->count();
    @endphp

    <!-- DATOS AGRUPADOS POR COBRADOR -->
    @foreach($cuotasPorCobrador as $cobradorId => $cuotasGrupo)
        @php
            $cobrador = $cuotasGrupo->first()->prestamo->agente;
            $subtotalDiario = 0;
            $subtotalSemanal = 0;
            $subtotalQuincenal = 0;
            $subtotalMensual = 0;
            $subtotalTrimestral = 0;
            $subtotalBimestral = 0;
            $subtotalCatorcenal = 0;
        @endphp
        
        <div class="cobrador-section">
            <div class="cobrador-header">
                COBRADOR: {{strtoupper($cobrador->full_name)}}
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;">Consecutivo</th>
                        <th style="width: 6%;">Tipo</th>
                        <th style="width: 8%;">Cédula</th>
                        <th style="width: 18%;">Cliente</th>
                        <th style="width: 8%;">F. Desemb.</th>
                        <th style="width: 8%;">F. Venc.</th>
                        <th style="width: 8%;">F. Cuota</th>
                        <th style="width: 6%;" class="text-right">Diario</th>
                        <th style="width: 6%;" class="text-right">Semanal</th>
                        <th style="width: 6%;" class="text-right">Quincenal</th>
                        <th style="width: 6%;" class="text-right">Mensual</th>
                        <th style="width: 6%;" class="text-right">Trimestral</th>
                        <th style="width: 6%;" class="text-right">Bimestral</th>
                        <th style="width: 6%;" class="text-right">Catorcenal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cuotasGrupo as $cuota)
                        @php
                            $totalRegistros++;
                            $diario = $cuota->prestamo->forma_pago_tipo == 1 ? $cuota->monto_pendiente_cuota : 0;
                            $semanal = $cuota->prestamo->forma_pago_tipo == 2 ? $cuota->monto_pendiente_cuota : 0;
                            $quincenal = $cuota->prestamo->forma_pago_tipo == 3 ? $cuota->monto_pendiente_cuota : 0;
                            $mensual = $cuota->prestamo->forma_pago_tipo == 4 ? $cuota->monto_pendiente_cuota : 0;
                            $trimestral = $cuota->prestamo->forma_pago_tipo == 5 ? $cuota->monto_pendiente_cuota : 0;
                            $bimestral = $cuota->prestamo->forma_pago_tipo == 6 ? $cuota->monto_pendiente_cuota : 0;
                            $catorcenal = $cuota->prestamo->forma_pago_tipo == 7 ? $cuota->monto_pendiente_cuota : 0;
                            
                            $subtotalDiario += $diario;
                            $subtotalSemanal += $semanal;
                            $subtotalQuincenal += $quincenal;
                            $subtotalMensual += $mensual;
                            $subtotalTrimestral += $trimestral;
                            $subtotalBimestral += $bimestral;
                            $subtotalCatorcenal += $catorcenal;
                        @endphp
                        <tr>
                            <td>{{$cuota->prestamo->consecutivo}}</td>
                            <td>{{$cuota->prestamo->tipo_prestamo}}</td>
                            <td>{{$cuota->prestamo->cliente->cedula}}</td>
                            <td>{{$cuota->prestamo->cliente->full_name}}</td>
                            <td class="text-center">{{fecha_d_m_Y($cuota->prestamo->fecha_desembolso)}}</td>
                            <td class="text-center">{{$cuota->prestamo->cuotas()->orderBy('id','desc')->first() ? fecha_d_m_Y($cuota->prestamo->cuotas()->orderBy('id','desc')->first()->fecha_cuota) : "-"}}</td>
                            <td class="text-center">{{fecha_d_m_Y($cuota->fecha_cuota)}}</td>
                            <td class="text-right">{{$diario > 0 ? number_format($diario, 2) : ''}}</td>
                            <td class="text-right">{{$semanal > 0 ? number_format($semanal, 2) : ''}}</td>
                            <td class="text-right">{{$quincenal > 0 ? number_format($quincenal, 2) : ''}}</td>
                            <td class="text-right">{{$mensual > 0 ? number_format($mensual, 2) : ''}}</td>
                            <td class="text-right">{{$trimestral > 0 ? number_format($trimestral, 2) : ''}}</td>
                            <td class="text-right">{{$bimestral > 0 ? number_format($bimestral, 2) : ''}}</td>
                            <td class="text-right">{{$catorcenal > 0 ? number_format($catorcenal, 2) : ''}}</td>
                        </tr>
                    @endforeach
                    
                    <!-- SUBTOTAL POR COBRADOR -->
                    <tr class="subtotal-row">
                        <td colspan="7" class="text-right"><strong>SUBTOTAL {{strtoupper($cobrador->full_name)}}:</strong></td>
                        <td class="text-right"><strong>{{number_format($subtotalDiario, 2)}}</strong></td>
                        <td class="text-right"><strong>{{number_format($subtotalSemanal, 2)}}</strong></td>
                        <td class="text-right"><strong>{{number_format($subtotalQuincenal, 2)}}</strong></td>
                        <td class="text-right"><strong>{{number_format($subtotalMensual, 2)}}</strong></td>
                        <td class="text-right"><strong>{{number_format($subtotalTrimestral, 2)}}</strong></td>
                        <td class="text-right"><strong>{{number_format($subtotalBimestral, 2)}}</strong></td>
                        <td class="text-right"><strong>{{number_format($subtotalCatorcenal, 2)}}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        @php
            $granTotalDiario += $subtotalDiario;
            $granTotalSemanal += $subtotalSemanal;
            $granTotalQuincenal += $subtotalQuincenal;
            $granTotalMensual += $subtotalMensual;
            $granTotalTrimestral += $subtotalTrimestral;
            $granTotalBimestral += $subtotalBimestral;
            $granTotalCatorcenal += $subtotalCatorcenal;
        @endphp
    @endforeach

    <!-- GRAN TOTAL -->
    <div class="gran-total-section">
        <h2>GRAN TOTAL</h2>
        <div class="stats">
            Diario: C$ {{number_format($granTotalDiario, 2)}} | 
            Semanal: C$ {{number_format($granTotalSemanal, 2)}} | 
            Quincenal: C$ {{number_format($granTotalQuincenal, 2)}} | 
            Mensual: C$ {{number_format($granTotalMensual, 2)}}
        </div>
        <div class="stats">
            Trimestral: C$ {{number_format($granTotalTrimestral, 2)}} | 
            Bimestral: C$ {{number_format($granTotalBimestral, 2)}} | 
            Catorcenal: C$ {{number_format($granTotalCatorcenal, 2)}}
        </div>
        <div class="stats">
            Total General: C$ {{number_format($granTotalDiario + $granTotalSemanal + $granTotalQuincenal + $granTotalMensual + $granTotalTrimestral + $granTotalBimestral + $granTotalCatorcenal, 2)}} | 
            Total de Cobradores: {{$totalCobradores}} | Total de Registros: {{$totalRegistros}}
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
