<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Recuperación - CrediNica</title>
    <style>
        @page { margin: 0.5cm; }
        body { font-family: sans-serif; font-size: 9px; color: #333; line-height: 1.2; }
        .report-header { width: 100%; margin-bottom: 20px; border-bottom: 2px solid #1f9cb5; padding-bottom: 10px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .company-logo { width: 120px; }
        .report-title { color: #1f9cb5; font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .report-datetime { color: #666; font-size: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background-color: #d1ecf1; color: #000; padding: 6px 4px; text-align: left; border: 0.5pt solid #a5d8dd; text-transform: uppercase; font-size: 8px; }
        td { padding: 5px 4px; border: 0.5pt solid #eee; }
        
        .cobrador-header {
            background-color: #1f9cb5;
            color: #ffffff;
            font-weight: bold;
            padding: 6px;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .subtotal-row { background-color: #f8f9fa; font-weight: bold; color: #1f9cb5; }
        .grand-total-row { background-color: #333; color: #ffffff; font-weight: bold; font-size: 11px; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 2px 4px; border-radius: 3px; font-weight: bold; font-size: 8px; }
        
        .cobrador-section { page-break-inside: auto; }
    </style>
</head>
<body>
    <div class="report-header">
        <table class="header-table">
            <tr>
                <td style="border:none; width: 33%;">
                    @if(file_exists(public_path('assets/img/LogoCrediNica.png')))
                        <img src="{{ public_path('assets/img/LogoCrediNica.png') }}" class="company-logo">
                    @endif
                </td>
                <td style="border:none; width: 34%; text-align: center;">
                    <div class="report-title">Reporte de Recuperación</div>
                    <div class="report-datetime">DEL {{ fecha_d_m_Y($desde) }} AL {{ fecha_d_m_Y($hasta) }}</div>
                </td>
                <td style="border:none; width: 33%; text-align: right;">
                    <div class="report-datetime"><b>Generado:</b> {{ date('d/m/Y h:i A') }}</div>
                    <div class="report-datetime"><b>Vista:</b> {{ strtoupper($tipo_vista ?? 'detallado') }}</div>
                </td>
            </tr>
        </table>
    </div>

    @php
        $grandTotalCapital = 0;
        $grandTotalInteres = 0;
        $grandTotalMora = 0;
        $grandTotalGeneral = 0;
    @endphp

    @if(($tipo_vista ?? 'detallado') == 'resumido')
        @php $totClientes = 0; $totAbonos = 0; @endphp
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 30%;">COBRADOR / AGENTE</th>
                    <th style="width: 10%;" class="text-right">CLIENTES</th>
                    <th style="width: 10%;" class="text-right">ABONOS</th>
                    <th style="width: 15%;" class="text-right">CAPITAL</th>
                    <th style="width: 15%;" class="text-right">INTERÉS</th>
                    <th style="width: 15%;" class="text-right">MORA</th>
                    <th style="width: 15%;" class="text-right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse($abonosDia as $row)
                    @php
                        $grandTotalCapital += $row->monto_capital;
                        $grandTotalInteres += $row->monto_interes;
                        $grandTotalMora += $row->monto_mora;
                        $grandTotalGeneral += $row->total_abono;
                        $totClientes += $row->clientes;
                        $totAbonos += $row->abonos_cant;
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ strtoupper($row->cobrador_nombre) }}</strong></td>
                        <td class="text-right">{{ $row->clientes }}</td>
                        <td class="text-right">{{ $row->abonos_cant }}</td>
                        <td class="text-right">{{ number_format($row->monto_capital, 2) }}</td>
                        <td class="text-right">{{ number_format($row->monto_interes, 2) }}</td>
                        <td class="text-right">{{ number_format($row->monto_mora, 2) }}</td>
                        <td class="text-right"><strong>{{ number_format($row->total_abono, 2) }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center">No se encontraron registros.</td></tr>
                @endforelse
            </tbody>
            @if(count($abonosDia) > 0)
            <tfoot>
                <tr class="grand-total-row">
                    <td colspan="2">TOTALES GENERALES:</td>
                    <td class="text-right">{{ $totClientes }}</td>
                    <td class="text-right">{{ $totAbonos }}</td>
                    <td class="text-right">{{ number_format($grandTotalCapital, 2) }}</td>
                    <td class="text-right">{{ number_format($grandTotalInteres, 2) }}</td>
                    <td class="text-right">{{ number_format($grandTotalMora, 2) }}</td>
                    <td class="text-right">{{ number_format($grandTotalGeneral, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    @else
        @php
            $abonosAgrupados = collect($abonosDia)->groupBy('created_user_id');
        @endphp

        @foreach($abonosAgrupados as $userId => $abonos)
            @php
                $cobradorNombre = $abonos->first()->creador_nombre ?? 'SIN COBRADOR';
                $subTotalCapital = $abonos->sum('total_abonado_capital');
                $subTotalInteres = $abonos->sum('total_abonado_interes');
                $subTotalMora = $abonos->sum('total_abonado_mora');
                $subTotalGeneral = $abonos->sum('total_abonado');

                $grandTotalCapital += $subTotalCapital;
                $grandTotalInteres += $subTotalInteres;
                $grandTotalMora += $subTotalMora;
                $grandTotalGeneral += $subTotalGeneral;
            @endphp
            
            <div class="cobrador-section">
                <table>
                    <thead>
                        <tr>
                            <th colspan="8" class="cobrador-header">COBRADOR: {{ $cobradorNombre }}</th>
                        </tr>
                        <tr>
                            <th style="width: 10%;">N° PRÉSTAMO</th>
                            <th style="width: 25%;">CLIENTE</th>
                            <th style="width: 12%;">FECHA ABONO</th>
                            <th style="width: 12%;" class="text-right">CAPITAL</th>
                            <th style="width: 12%;" class="text-right">INTERÉS</th>
                            <th style="width: 12%;" class="text-right">MORA</th>
                            <th style="width: 12%;" class="text-right">TOTAL</th>
                            <th style="width: 5%;" class="text-center">TIPO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($abonos as $abono)
                            <tr>
                                <td>#{{ $abono->consecutivo }}</td>
                                <td>{{ $abono->cliente_nombre }}</td>
                                <td>{{ fecha_d_m_Y($abono->fecha_abono) }}</td>
                                <td class="text-right">{{ number_format($abono->total_abonado_capital, 2) }}</td>
                                <td class="text-right">{{ number_format($abono->total_abonado_interes, 2) }}</td>
                                <td class="text-right">{{ number_format($abono->total_abonado_mora, 2) }}</td>
                                <td class="text-right"><strong>{{ number_format($abono->total_abonado, 2) }}</strong></td>
                                <td class="text-center">
                                    @if($abono->tipo_abono == 0) N
                                    @elseif($abono->tipo_abono == 1) A
                                    @else V @endif
                                </td>
                            </tr>
                        @endforeach
                        <tr class="subtotal-row">
                            <td colspan="3" class="text-right">SUBTOTAL {{ strtoupper($cobradorNombre) }}:</td>
                            <td class="text-right">{{ number_format($subTotalCapital, 2) }}</td>
                            <td class="text-right">{{ number_format($subTotalInteres, 2) }}</td>
                            <td class="text-right">{{ number_format($subTotalMora, 2) }}</td>
                            <td class="text-right">{{ number_format($subTotalGeneral, 2) }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endforeach

        @if($abonosAgrupados->count() > 0)
            <table>
                <tfoot>
                    <tr class="grand-total-row">
                        <td style="width: 47%;" class="text-right">TOTALES GENERALES:</td>
                        <td style="width: 12%;" class="text-right">{{ number_format($grandTotalCapital, 2) }}</td>
                        <td style="width: 12%;" class="text-right">{{ number_format($grandTotalInteres, 2) }}</td>
                        <td style="width: 12%;" class="text-right">{{ number_format($grandTotalMora, 2) }}</td>
                        <td style="width: 12%;" class="text-right">{{ number_format($grandTotalGeneral, 2) }}</td>
                        <td style="width: 5%;"></td>
                    </tr>
                </tfoot>
            </table>
        @endif
    @endif

</body>
</html>
