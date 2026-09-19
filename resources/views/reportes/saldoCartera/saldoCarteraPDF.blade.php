<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Saldo de Cartera</title>
    <style>
        @page { size: landscape; margin: 1cm; }
        body { font-family: Arial, sans-serif; font-size: 8px; color: #333; }
        .header { width: 100%; border-bottom: 2px solid #1f9cb5; padding-bottom: 10px; margin-bottom: 10px; }
        .header table { width: 100%; border: none; }
        .logo { width: 120px; }
        .title { text-align: center; color: #1f9cb5; }
        .title h1 { font-size: 16px; margin: 0; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th { background-color: #d1ecf1; color: #000; font-weight: bold; padding: 4px 2px; border: 1px solid #ddd; text-transform: uppercase; font-size: 7px; }
        td { padding: 3px 2px; border: 1px solid #eee; font-size: 7.5px; }
        
        .cobrador-header { background-color: #1f9cb5; color: white; font-weight: bold; padding: 6px; font-size: 10px; }
        .subtotal-row { background-color: #f9f9f9; font-weight: bold; color: #1f9cb5; }
        .gran-total { background-color: #333; color: white; font-weight: bold; padding: 10px; text-align: center; margin-top: 10px; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td style="border:none;" class="logo">
                    <img src="{{ public_path('assets/img/LogoCrediNica.png') }}" style="width:100px;">
                </td>
                <td style="border:none;" class="title">
                    <h1>SALDO DE CARTERA</h1>
                    <p>Reporte Generado el: {{ date('d/m/Y h:i A') }}</p>
                </td>
                <td style="border:none; text-align:right; font-size:7px;">
                    Corte al: {{ request('fin') ? fecha_d_m_Y(request('fin')) : date('d/m/Y') }}
                </td>
            </tr>
        </table>
    </div>

    @php
        $granTotalCapital = 0; 
        $granTotalInteres = 0; 
        $granTotalCapPend = 0; 
        $granTotalIntPend = 0;
        $granTotalTotPend = 0;
        $totalClientes = 0;
        
        // Agrupar por cobrador usando los datos optimizados
        $porCobrador = $prestamos->groupBy('agente_id');
    @endphp

    @foreach($porCobrador as $cobradorId => $grupo)
        @php
            $nombreCobrador = $grupo->first()->agente_nombre ?? 'SIN ASIGNAR';
            $subCap = 0; $subInt = 0; $subCapPend = 0; $subIntPend = 0; $subTotPend = 0; $subCli = 0;
        @endphp
        
        <table>
            <thead>
                <tr>
                    <th colspan="14" class="cobrador-header">COBRADOR: {{ strtoupper($nombreCobrador) }}</th>
                </tr>
                <tr>
                    <th style="width: 3%;">#</th>
                    <th style="width: 12%;">CLIENTE</th>
                    <th style="width: 6%;">F. COLOC.</th>
                    <th style="width: 6%;">F. VENC.</th>
                    <th style="width: 6%;">FORMA PAGO</th>
                    <th style="width: 4%;">PLAZO</th>
                    <th style="width: 7%;" class="text-right">CAPITAL</th>
                    <th style="width: 7%;" class="text-right">INTERÉS</th>
                    <th style="width: 7%;" class="text-right">CAP. PEND.</th>
                    <th style="width: 7%;" class="text-right">INT. PEND.</th>
                    <th style="width: 8%;" class="text-right">TOTAL PEND.</th>
                    <th style="width: 8%;">TIPO</th>
                    <th style="width: 8%;">ESTADO</th>
                    <th style="width: 6%;">CLASIF.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grupo as $dt)
                    @php
                        $capPend = $dt->monto_prestamo - $dt->suma_abonos_capital;
                        $intPend = ($dt->monto_financiado - $dt->monto_prestamo) - $dt->suma_abonos_interes;
                        $totPend = $capPend + $intPend;
                        
                        // Obtener última cuota
                        $ultimaCuota = \DB::table('prestamo_coutas')
                            ->where('prestamo_id', $dt->id)
                            ->orderBy('fecha_cuota', 'desc')
                            ->first();
                        
                        $subCap += $dt->monto_prestamo;
                        $subInt += ($dt->monto_financiado - $dt->monto_prestamo);
                        $subCapPend += $capPend;
                        $subIntPend += $intPend;
                        $subTotPend += $totPend;
                        $subCli++;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ strtoupper($dt->cliente_nombre) }}</td>
                        <td class="text-center">{{ fecha_d_m_Y($dt->fecha_desembolso) }}</td>
                        <td class="text-center">{{ $ultimaCuota ? fecha_d_m_Y($ultimaCuota->fecha_cuota) : '-' }}</td>
                        <td class="text-center">{{ strtoupper($dt->forma_pago) }}</td>
                        <td class="text-center">{{ $dt->plazo }}</td>
                        <td class="text-right">{{ number_format($dt->monto_prestamo, 2) }}</td>
                        <td class="text-right">{{ number_format($dt->monto_financiado - $dt->monto_prestamo, 2) }}</td>
                        <td class="text-right">{{ number_format($capPend, 2) }}</td>
                        <td class="text-right">{{ number_format($intPend, 2) }}</td>
                        <td class="text-right"><strong>{{ number_format($totPend, 2) }}</strong></td>
                        <td class="text-center">{{ $dt->tipo_prestamo }}</td>
                        <td class="text-center">{{ $dt->estado_prestamo }}</td>
                        <td class="text-center">{{ $dt->clasificacion ?? '-' }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td colspan="6" class="text-right">SUBTOTAL ({{ $subCli }} cliente{{ $subCli != 1 ? 's' : '' }}):</td>
                    <td class="text-right">{{ number_format($subCap, 2) }}</td>
                    <td class="text-right">{{ number_format($subInt, 2) }}</td>
                    <td class="text-right">{{ number_format($subCapPend, 2) }}</td>
                    <td class="text-right">{{ number_format($subIntPend, 2) }}</td>
                    <td class="text-right">{{ number_format($subTotPend, 2) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tbody>
        </table>
        
        @php
            $granTotalCapital += $subCap;
            $granTotalInteres += $subInt;
            $granTotalCapPend += $subCapPend;
            $granTotalIntPend += $subIntPend;
            $granTotalTotPend += $subTotPend;
            $totalClientes += $subCli;
        @endphp
    @endforeach

    <table style="margin-top: 20px;">
        <thead>
            <tr class="gran-total" style="background-color: #333; color: white;">
                <th colspan="6" style="background-color: #333; color: white; text-align: right; padding: 8px;">GRAN TOTAL ({{ $totalClientes }} cliente{{ $totalClientes != 1 ? 's' : '' }}):</th>
                <th style="background-color: #333; color: white; text-align: right; width: 7%;">{{ number_format($granTotalCapital, 2) }}</th>
                <th style="background-color: #333; color: white; text-align: right; width: 7%;">{{ number_format($granTotalInteres, 2) }}</th>
                <th style="background-color: #333; color: white; text-align: right; width: 7%;">{{ number_format($granTotalCapPend, 2) }}</th>
                <th style="background-color: #333; color: white; text-align: right; width: 7%;">{{ number_format($granTotalIntPend, 2) }}</th>
                <th style="background-color: #333; color: white; text-align: right; width: 8%;">{{ number_format($granTotalTotPend, 2) }}</th>
                <th colspan="3" style="background-color: #333; color: white;"></th>
            </tr>
        </thead>
    </table>

</body>
</html>
