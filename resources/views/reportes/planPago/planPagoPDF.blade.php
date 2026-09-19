<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan de Pago</title>
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
        .totales-row { background-color: #d4edda; font-weight: bold; }
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
                <h1>PLAN DE PAGO</h1>
                <p>Cronograma Programado de Cuotas</p>
            </div>
        </div>
    </div>

    <!-- INFORMACIÓN DEL PRÉSTAMO -->
    <div class="info-section">
        <h3>INFORMACIÓN DEL CRÉDITO</h3>
        <table style="width: 100%; font-size: 10px; border: none;">
            <tr>
                <td style="width: 33%;"><strong>N° Préstamo:</strong> {{$prestamoSel->consecutivo}}</td>
                <td style="width: 33%;"><strong>Fecha Apertura:</strong> {{fecha_d_m_Y($prestamoSel->fecha_desembolso)}}</td>
                <td style="width: 33%;"><strong>Fecha Final:</strong> {{$prestamoSel->cuotas()->orderBy('id','desc')->first() ? fecha_d_m_Y($prestamoSel->cuotas()->orderBy('id','desc')->first()->fecha_cuota):"-"}}</td>
            </tr>
        </table>
    </div>

    <!-- DATOS DEL CLIENTE Y PRÉSTAMO -->
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
            <td colspan="3">{{$prestamoSel->estado_prestamo}}</td>
        </tr>
    </table>

    <!-- TABLA DE PLAN DE PAGO -->
    <h3 style="background: #1f9cb5; color: white; padding: 8px; margin-top: 15px; margin-bottom: 10px; font-size: 12px;">CRONOGRAMA DE PAGOS</h3>
    <table class="cuotas">
        <thead>
            <tr>
                <th style="width: 10%;">#</th>
                <th style="width: 20%;">Fecha Cuota</th>
                <th style="width: 20%;">Valor Cuota</th>
                <th style="width: 25%;">Saldo Anterior</th>
                <th style="width: 25%;">Nuevo Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php
                $saldoAnterior = $prestamoSel->monto_financiado;
            @endphp
            @foreach($prestamoSel->cuotas as $cuota)
                @php
                    $nuevoSaldo = $saldoAnterior - $cuota->monto_cuota;
                    if($nuevoSaldo < 0) $nuevoSaldo = 0;
                @endphp
                <tr>
                    <td>{{$cuota->numero_cuota}}</td>
                    <td>{{fecha_d_m_Y($cuota->fecha_cuota)}}</td>
                    <td>{{$prestamoSel->moneda}} {{number_format($cuota->monto_cuota, 2)}}</td>
                    <td>{{$prestamoSel->moneda}} {{number_format($saldoAnterior, 2)}}</td>
                    <td>{{$prestamoSel->moneda}} {{number_format($nuevoSaldo, 2)}}</td>
                </tr>
                @php
                    $saldoAnterior = $nuevoSaldo;
                @endphp
            @endforeach
            <tr class="totales-row">
                <td colspan="2" style="text-align: right;">TOTAL A PAGAR:</td>
                <td>{{$prestamoSel->moneda}} {{number_format($prestamoSel->cuotas->sum('monto_cuota'), 2)}}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>

</body>
</html>
