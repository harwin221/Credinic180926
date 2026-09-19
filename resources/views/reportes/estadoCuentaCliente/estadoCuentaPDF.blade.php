<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Cuenta</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; padding: 15px; }
        .header { width: 100%; margin-bottom: 20px; border-bottom: 3px solid #1f9cb5; padding-bottom: 15px; }
        .header-content { display: table; width: 100%; }
        .logo-section { display: table-cell; width: 140px; vertical-align: middle; }
        .logo-section img { width: 120px; }
        .title-section { display: table-cell; vertical-align: middle; text-align: center; }
        .title-section h1 { font-size: 22px; color: #1f9cb5; margin-bottom: 5px; }
        .title-section p { font-size: 11px; color: #666; }
        .info-section { background-color: #f8f9fa; padding: 12px; margin-bottom: 15px; border-left: 4px solid #1f9cb5; }
        .info-section h3 { font-size: 12px; color: #1f9cb5; margin-bottom: 8px; }
        .info-row { margin-bottom: 6px; font-size: 10px; }
        .info-row strong { color: #333; min-width: 120px; display: inline-block; }
        .datos-prestamo { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 10px; }
        .datos-prestamo td, .datos-prestamo th { padding: 8px; border: 1px solid #dee2e6; }
        .datos-prestamo th { background-color: #e9ecef; font-weight: bold; width: 25%; }
        .datos-prestamo td { background-color: #fff; }
        table.cuotas { width: 100%; border-collapse: collapse; font-size: 9px; }
        table.cuotas thead { background-color: #1f9cb5; color: white; }
        table.cuotas thead th { padding: 8px 5px; text-align: center; font-weight: bold; border: 1px solid #0d7a8f; }
        table.cuotas tbody td { padding: 6px 5px; border: 1px solid #dee2e6; text-align: center; }
        table.cuotas tbody tr:nth-child(even) { background-color: #f8f9fa; }
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
                <h1>ESTADO DE CUENTA</h1>
                <p>Cronograma de Cuotas del Préstamo</p>
            </div>
        </div>
    </div>

    <div class="info-section">
        <h3>INFORMACIÓN DEL PRÉSTAMO</h3>
        <table style="width: 100%; font-size: 10px; border: none;">
            <tr>
                <td style="width: 33%;"><strong>N° Préstamo:</strong> {{$prestamoSel->consecutivo}}</td>
                <td style="width: 33%;"><strong>Fecha Apertura:</strong> {{fecha_d_m_Y($prestamoSel->fecha_desembolso)}}</td>
                <td style="width: 33%;"><strong>Fecha Final:</strong> {{$prestamoSel->cuotas()->orderBy('id','desc')->first() ? fecha_d_m_Y($prestamoSel->cuotas()->orderBy('id','desc')->first()->fecha_cuota):"-"}}</td>
            </tr>
        </table>
    </div>

    <table class="datos-prestamo">
        <tr>
            <th>Cliente:</th>
            <td colspan="3">{{$prestamoSel->cliente->full_name}}</td>
        </tr>
        <tr>
            <th>Dirección:</th>
            <td colspan="3">{{$prestamoSel->cliente->direccion}}</td>
        </tr>
        <tr>
            <th>Cobrador:</th>
            <td colspan="3">{{$prestamoSel->agente->full_name}}</td>
        </tr>
        <tr>
            <th>Monto Prestado:</th>
            <td style="font-weight: bold;">{{$prestamoSel->moneda}} {{number_format($prestamoSel->monto_prestamo, 2)}}</td>
            <th>Total a Pagar C+I:</th>
            <td style="font-weight: bold; color: #1f9cb5;">{{$prestamoSel->moneda}} {{number_format($prestamoSel->monto_financiado, 2)}}</td>
        </tr>
        <tr>
            <th>Plazo (Meses):</th>
            <td>{{$prestamoSel->plazo_pago}}</td>
            <th>Frecuencia:</th>
            <td>{{$prestamoSel->forma_pago}}</td>
        </tr>
        <tr>
            <th>Estado:</th>
            <td>{{$prestamoSel->estado_prestamo}}</td>
            <th>Promedio Días Atraso:</th>
            <td style="font-weight: bold; color: {{ $promedioDiasAtraso > 3 ? '#dc3545' : ($promedioDiasAtraso > 0 ? '#ffc107' : '#28a745') }};">
                {{number_format($promedioDiasAtraso, 2)}} días
            </td>
        </tr>
    </table>

    <!-- CONTENEDOR LADO A LADO PARA CUOTAS Y ABONOS -->
    <table style="width: 100%; border-collapse: collapse; border: none; margin-top: 15px;">
        <tr>
            <!-- COLUMNA IZQUIERDA: CRONOGRAMA DE CUOTAS -->
            <td style="width: 49%; vertical-align: top; padding-right: 1%; border: none;">
                <h3 style="background: #1f9cb5; color: white; padding: 6px; margin-bottom: 8px; font-size: 11px; text-align: center;">CRONOGRAMA DE CUOTAS</h3>
                <table class="cuotas">
                    <thead>
                        <tr>
                            <th style="width: 15%;">#</th>
                            <th style="width: 30%;">Fecha Cuota</th>
                            <th style="width: 30%;">Monto Cuota</th>
                            <th style="width: 25%;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prestamoSel->cuotas as $cuota)
                            <tr>
                                <td>{{$cuota->numero_cuota}}</td>
                                <td>{{fecha_d_m_Y($cuota->fecha_cuota)}}</td>
                                <td>{{$prestamoSel->moneda}} {{number_format($cuota->monto_cuota, 2)}}</td>
                                <td>
                                    @if($cuota->estado == 3)
                                        <span style="color: green; font-weight: bold;">PAGADA</span>
                                    @elseif($cuota->estado == 2)
                                        <span style="color: orange; font-weight: bold;">PARCIAL</span>
                                    @else
                                        <span style="color: red;">PENDIENTE</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>

            <!-- COLUMNA DERECHA: ABONOS REALIZADOS -->
            <td style="width: 49%; vertical-align: top; padding-left: 1%; border: none;">
                @php
                    $abonosReales = \App\Models\abonosModel::where('prestamo_id', $prestamoSel->id)
                        ->where('estado', 1)
                        ->orderBy('fecha_abono', 'asc')
                        ->get();
                @endphp
                
                @if($abonosReales->count() > 0)
                <h3 style="background: #28a745; color: white; padding: 6px; margin-bottom: 8px; font-size: 11px; text-align: center;">ABONOS REALIZADOS</h3>
                <table class="cuotas">
                    <thead>
                        <tr>
                            <th style="width: 10%;">#</th>
                            <th style="width: 30%;">Fecha Pago</th>
                            <th style="width: 30%;">Monto Abonado</th>
                            <th style="width: 30%;">Recibido Por</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalAbonado = 0; @endphp
                        @foreach($abonosReales as $index => $abono)
                            @php
                                $montoAbono = $abono->total_efectivo + $abono->total_tarjeta + $abono->total_cheque + $abono->total_transferencia;
                                $totalAbonado += $montoAbono;
                            @endphp
                            <tr>
                                <td>{{$index + 1}}</td>
                                <td>{{fecha_d_m_Y($abono->fecha_abono)}}</td>
                                <td style="font-weight: bold;">{{$prestamoSel->moneda}} {{number_format($montoAbono, 2)}}</td>
                                <td style="font-size: 8px;">{{$abono->user_create->full_name ?? 'N/A'}}</td>
                            </tr>
                        @endforeach
                        <tr style="background-color: #d4edda; font-weight: bold;">
                            <td colspan="2" style="text-align: right;">TOTAL ABONADO:</td>
                            <td>{{$prestamoSel->moneda}} {{number_format($totalAbonado, 2)}}</td>
                            <td></td>
                        </tr>
                        <tr style="background-color: #fff3cd; font-weight: bold;">
                            <td colspan="2" style="text-align: right;">SALDO PENDIENTE:</td>
                            <td style="color: red;">{{$prestamoSel->moneda}} {{number_format($prestamoSel->pendiente_abono, 2)}}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
                @else
                <div style="background: #f8d7da; padding: 10px; margin-top: 20px; border-left: 4px solid #dc3545; text-align: center;">
                    <strong>No se han registrado abonos para este préstamo.</strong>
                </div>
                @endif
            </td>
        </tr>
    </table>

</body>
</html>
