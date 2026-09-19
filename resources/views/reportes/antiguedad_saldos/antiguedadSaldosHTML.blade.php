<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Clasificación CONAMI</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
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
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #dee2e6;
            font-size: 11px;
        }
        
        table tbody td {
            padding: 5px 4px;
            border: 1px solid #dee2e6;
            font-size: 10px;
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
            margin-top: 20px;
            border: 1px solid #1f9cb5;
            background-color: #f8f9fa;
            color: #333;
            padding: 10px;
            text-align: center;
        }
        
        .gran-total-section h2 {
            font-size: 12px;
            margin-bottom: 8px;
            color: #1f9cb5;
            text-decoration: underline;
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
        
        .corriente { background-color: #d4edda; }
        .dias-30 { background-color: #fff3cd; }
        .dias-60 { background-color: #ffe5b4; }
        .dias-90 { background-color: #f8d7da; }
        .dias-mas { background-color: #f5c6cb; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
    <link href="{{asset('assets/vendor/fontawesome-free-6.4.0-web/css/all.min.css')}}" rel="stylesheet">
</head>
<body>
    <div class="screen-actions no-print" style="margin-bottom: 20px;">
        {{html()->form('GET', route('reportes.antiguedad_saldos'))->open()}}
            <input type="hidden" name="cliente" value="{{request('cliente')}}">
            <input type="hidden" name="cobrador" value="{{request('cobrador')}}">
            <input type="hidden" name="frecuencia" value="{{request('frecuencia')}}">
            <button type="submit" name="pdf" value="pdf" class="btn btn-primary" formtarget="_blank" style="padding: 8px 15px; background: #1f9cb5; color: white; border: none; border-radius: 4px; cursor: pointer;">
                <i class="fas fa-file-pdf"></i> Generar PDF
            </button>
            <button type="submit" name="excel" value="excel" class="btn btn-success" style="padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
                <i class="fas fa-file-excel"></i> Exportar a EXCEL
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.print()" style="padding: 8px 15px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <a href="{{ route('reportes.antiguedad_saldos') }}" style="padding: 8px 15px; background: #e2e8f0; color: #4a5568; border-radius: 4px; text-decoration: none; margin-left: 10px;">
                <i class="fas fa-arrow-left"></i> Volver a Filtros
            </a>
        {{html()->form()->close()}}
    </div>
    <!-- HEADER -->
    <div class="header">
        <div class="header-content">
            <div class="logo-section">
                <img src="{{asset('assets/img/LogoCrediNica.png')}}" alt="CrediNica">
            </div>
            <div class="title-section">
                <a href="{{ route('reportes.antiguedad_saldos') }}" class="no-print" style="float: left; background: #e2e8f0; padding: 5px 10px; border-radius: 4px; text-decoration: none; color: #333; font-size: 10px; margin-top: 5px;">
                    <i class="fas fa-arrow-left"></i> Atrás
                </a>
                <h1>CLASIFICACIÓN CONAMI</h1>
                <p>Análisis de Cartera por Días de Vencimiento</p>
            </div>
        </div>
    </div>


    @php
        use Carbon\Carbon;
        // Agrupar por cobrador
        $prestamosPorCobrador = $prestamos->groupBy(function($prestamo) {
            return $prestamo->agente->id;
        });
        
        $granTotalCorriente = 0;
        $granTotal30 = 0;
        $granTotal60 = 0;
        $granTotal90 = 0;
        $granTotalMas = 0;
        $totalRegistros = 0;
        $totalCobradores = $prestamosPorCobrador->count();

        // OPTIMIZACIÓN EXTREMA: Pre-calcular los saldos y las fechas para todos los préstamos de una sola vez
        $prestamosIds = $prestamos->pluck('id')->toArray();
        
        $abonosMasivos = \DB::table('abonos')
            ->whereIn('prestamo_id', $prestamosIds)
            ->where('estado', 1)
            ->select('prestamo_id', \DB::raw('SUM(total_efectivo + total_transferencia + total_tarjeta + total_cheque) as suma_abonos'))
            ->groupBy('prestamo_id')
            ->get()
            ->keyBy('prestamo_id');
            
        $ultimasFechasMasivas = \DB::table('prestamo_coutas')
            ->whereIn('prestamo_id', $prestamosIds)
            ->select('prestamo_id', \DB::raw('MAX(fecha_cuota) as ultima_fecha'))
            ->groupBy('prestamo_id')
            ->get()
            ->keyBy('prestamo_id');
    @endphp

    <!-- DATOS AGRUPADOS POR COBRADOR -->
    @foreach($prestamosPorCobrador as $cobradorId => $prestamosGrupo)
        @php
            $cobrador = $prestamosGrupo->first()->agente;
            $subtotalCorriente = 0;
            $subtotal30 = 0;
            $subtotal60 = 0;
            $subtotal90 = 0;
            $subtotalMas = 0;
        @endphp
        
        <div class="cobrador-section">
            <div class="cobrador-header">
                COBRADOR: {{strtoupper($cobrador->full_name)}}
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 25%;">Cliente</th>
                        <th style="width: 10%;">Monto</th>
                        <th style="width: 10%;">Financ.</th>
                        <th style="width: 5%;">Días</th>
                        <th style="width: 10%;">Corriente</th>
                        <th style="width: 10%;">0-30</th>
                        <th style="width: 10%;">31-60</th>
                        <th style="width: 10%;">61-90</th>
                        <th style="width: 10%;">90+</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prestamosGrupo as $dt)
                        @php
                            $abonos = $abonosMasivos->get($dt->id);
                            $sumaAbonos = $abonos ? $abonos->suma_abonos : 0;
                            $saldoActual = $dt->monto_financiado - $sumaAbonos;
                            
                            $diasVencidos = 0;
                            $fechaVenc = '-';
                            $corriente = 0;
                            $dias30 = 0;
                            $dias60 = 0;
                            $dias90 = 0;
                            $diasMas = 0;
                            
                            $ultimaFechaReg = $ultimasFechasMasivas->get($dt->id);
                            
                            if($ultimaFechaReg && $ultimaFechaReg->ultima_fecha){
                                $ultimaFechaStr = $ultimaFechaReg->ultima_fecha; // Formato Y-m-d
                                $diasVencidos = Carbon::parse($ultimaFechaStr)->diffInDays(Carbon::now(), false);
                                
                                if ($diasVencidos < 0) {
                                    $corriente = $saldoActual;
                                } elseif ($diasVencidos <= 30) {
                                    $dias30 = $saldoActual;
                                } elseif ($diasVencidos <= 60) {
                                    $dias60 = $saldoActual;
                                } elseif ($diasVencidos <= 90) {
                                    $dias90 = $saldoActual;
                                } else {
                                    $diasMas = $saldoActual;
                                }
                            }
                            
                            $subtotalCorriente += $corriente;
                            $subtotal30 += $dias30;
                            $subtotal60 += $dias60;
                            $subtotal90 += $dias90;
                            $subtotalMas += $diasMas;
                            $totalRegistros++;
                        @endphp
                        <tr>
                            <td>{{$dt->cliente->full_name}}</td>
                            <td class="text-right">{{number_format($dt->monto_prestamo, 2)}}</td>
                            <td class="text-right">{{number_format($dt->monto_financiado, 2)}}</td>
                            <td class="text-center">{{$diasVencidos >= 0 ? $diasVencidos : '-'}}</td>
                            <td class="text-right corriente">{{$corriente > 0 ? number_format($corriente, 2) : ''}}</td>
                            <td class="text-right dias-30">{{$dias30 > 0 ? number_format($dias30, 2) : ''}}</td>
                            <td class="text-right dias-60">{{$dias60 > 0 ? number_format($dias60, 2) : ''}}</td>
                            <td class="text-right dias-90">{{$dias90 > 0 ? number_format($dias90, 2) : ''}}</td>
                            <td class="text-right dias-mas">{{$diasMas > 0 ? number_format($diasMas, 2) : ''}}</td>
                        </tr>
                    @endforeach
                    
                    <!-- SUBTOTAL POR COBRADOR -->
                    <tr class="subtotal-row">
                        <td colspan="4" class="text-right"><strong>SUBTOTAL {{strtoupper($cobrador->full_name)}}:</strong></td>
                        <td class="text-right"><strong>{{number_format($subtotalCorriente, 2)}}</strong></td>
                        <td class="text-right"><strong>{{number_format($subtotal30, 2)}}</strong></td>
                        <td class="text-right"><strong>{{number_format($subtotal60, 2)}}</strong></td>
                        <td class="text-right"><strong>{{number_format($subtotal90, 2)}}</strong></td>
                        <td class="text-right"><strong>{{number_format($subtotalMas, 2)}}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        @php
            $granTotalCorriente += $subtotalCorriente;
            $granTotal30 += $subtotal30;
            $granTotal60 += $subtotal60;
            $granTotal90 += $subtotal90;
            $granTotalMas += $subtotalMas;
        @endphp
    @endforeach

    <!-- GRAN TOTAL -->
    <div class="gran-total-section">
        <h2>GRAN TOTAL</h2>
        <div class="stats">
            Corriente: C$ {{number_format($granTotalCorriente, 2)}} | 
            0-30 días: C$ {{number_format($granTotal30, 2)}} | 
            31-60 días: C$ {{number_format($granTotal60, 2)}} | 
            61-90 días: C$ {{number_format($granTotal90, 2)}} | 
            90+ días: C$ {{number_format($granTotalMas, 2)}}
        </div>
        <div class="stats">
            Total General: C$ {{number_format($granTotalCorriente + $granTotal30 + $granTotal60 + $granTotal90 + $granTotalMas, 2)}} | 
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
