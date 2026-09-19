<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Cartera Diaria - CrediNica</title>
    <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/fontawesome-free-6.4.0-web/css/all.min.css')}}" rel="stylesheet">
    @include('reportes._corporate_styles')
</head>
<body>
    <div class="screen-actions no-print">
        {{html()->form('GET', route('reportes.cartera_diaria'))->open()}}
            <input type="hidden" name="cobrador" value="{{$cobrador}}">
            <input type="hidden" name="fecha"    value="{{$fecha}}">
            <button type="submit" name="excel" value="excel" class="btn-act success"><i class="fas fa-file-excel"></i> Excel</button>
            <button type="submit" name="pdf"   value="pdf"   class="btn-act primary" formtarget="_blank"><i class="fas fa-file-pdf"></i> PDF</button>
            <button type="button" class="btn-act primary" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
        {{html()->form()->close()}}
    </div>

    @php
        use Carbon\Carbon;
        $fechaReporte = $fecha ?: date('Y-m-d');
        // Los datos ya vienen listos del controlador
    @endphp

    <div class="report-wrapper wide">
        <div class="rpt-header">
            <img src="{{asset('assets/img/LogoCrediNica.png')}}" alt="CrediNica">
            <div class="co-name">CrediNica</div>
            <div class="rpt-title">Reporte de Cartera Diaria</div>
            <div class="rpt-meta">Fecha del reporte: {{ fecha_d_m_Y($fechaReporte) }} &nbsp;|&nbsp; Generado: {{ date('d/m/Y h:i A') }}</div>
        </div>

        <style>
            .data-table tbody td { font-size: 10px; padding: 5px 8px; }
            .data-table thead th { font-size: 9px; padding: 6px 8px; }
            .num { font-size: 10px; white-space: nowrap; }
            .section-title { font-size: 9px; padding: 5px 16px; }
            .nowrap { white-space: nowrap; }
        </style>

        {{-- ─── SECCIÓN 1: CUOTAS DEL DÍA ─── --}}
        <div class="section-title">Cuotas del Día &mdash; {{ fecha_d_m_Y($fechaReporte) }}</div>
        @php $tCuota1=0; $tAtraso1=0; $tCuotaMasAtraso1=0; $tVenc1=0; $tPend1=0; @endphp
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th style="width: 15%;">Cliente</th>
                    <th style="width: 20%;">Dirección</th>
                    <th>Teléfono</th>
                    <th class="text-center nowrap">F. Desemb.</th>
                    <th class="text-center nowrap">F. Venc.</th>
                    <th class="text-right">Cuota</th>
                    <th class="text-right">Atraso</th>
                    <th class="text-right">Cuota+Atr.</th>
                    <th class="text-center">C.Venc</th>
                    <th class="text-center">Días Atr.</th>
                    <th class="text-right">Saldo Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cuotasDia as $index => $item)
                    @php
                        $p = $item['prestamo'];
                        $tCuota1 += $item['cuota_hoy']; 
                        $tAtraso1 += $item['atraso'];
                        $tCuotaMasAtraso1 += $item['atraso'] + $item['cuota_hoy'];
                        $tVenc1 += $item['cant_venc']; 
                        $tPend1 += $item['saldo_total'];
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="bold">{{ $p->cliente->full_name ?? 'N/A' }}</td>
                        <td style="font-size:9px;">{{ $p->cliente->direccion ?? '-' }}</td>
                        <td class="nowrap">{{ $p->cliente->telefono1 ?? '-' }}</td>
                        <td class="text-center nowrap">{{ fecha_d_m_Y($p->fecha_desembolso) }}</td>
                        <td class="text-center nowrap">{{ $item['fecha_venc'] ? fecha_d_m_Y($item['fecha_venc']) : '-' }}</td>
                        <td class="text-right num">{{ number_format($item['cuota_hoy'], 2) }}</td>
                        <td class="text-right num">{{ $item['atraso'] > 0 ? number_format($item['atraso'], 2) : '—' }}</td>
                        <td class="text-right num bold">{{ number_format($item['atraso'] + $item['cuota_hoy'], 2) }}</td>
                        <td class="text-center bold">{{ $item['cant_venc'] }}</td>
                        <td class="text-center bold num-red">{{ $item['dias_atraso'] }}</td>
                        <td class="text-right num">{{ number_format($item['saldo_total'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="12" class="text-center" style="padding:20px;color:#718096;">No hay cuotas programadas para este día.</td></tr>
                @endforelse
            </tbody>
            @if($cuotasDia->count() > 0)
            <tfoot>
                <tr class="row-subtotal">
                    <td colspan="6" class="text-right">Subtotal &mdash; {{ $cuotasDia->count() }} registros</td>
                    <td class="text-right num">{{ number_format($tCuota1, 2) }}</td>
                    <td class="text-right num">{{ number_format($tAtraso1, 2) }}</td>
                    <td class="text-right num">{{ number_format($tCuotaMasAtraso1, 2) }}</td>
                    <td class="text-center">{{ $tVenc1 }}</td>
                    <td></td>
                    <td class="text-right num">{{ number_format($tPend1, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>

        {{-- ─── SECCIÓN 2: MORA ─── --}}
        <div class="section-title warning" style="margin-top:8px;">Clientes en Mora &mdash; Sin cuota hoy, con atrasos</div>
        @php $tAtraso2=0; $tVenc2=0; $tPend2=0; @endphp
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th style="width: 15%;">Cliente</th>
                    <th style="width: 20%;">Dirección</th>
                    <th>Teléfono</th>
                    <th class="text-center nowrap">F. Desemb.</th>
                    <th class="text-center nowrap">F. Venc.</th>
                    <th class="text-right">Atraso Total</th>
                    <th class="text-center">C.Venc</th>
                    <th class="text-center">Días Atr.</th>
                    <th class="text-center nowrap">Último Abono</th>
                    <th class="text-right">Saldo Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cuotasMora as $index => $item)
                    @php
                        $p = $item['prestamo'];
                        $tAtraso2 += $item['atraso']; 
                        $tVenc2 += $item['cant_venc']; 
                        $tPend2 += $item['saldo_total'];
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="bold">{{ $p->cliente->full_name ?? 'N/A' }}</td>
                        <td style="font-size:9px;">{{ $p->cliente->direccion ?? '-' }}</td>
                        <td class="nowrap">{{ $p->cliente->telefono1 ?? '-' }}</td>
                        <td class="text-center nowrap">{{ fecha_d_m_Y($p->fecha_desembolso) }}</td>
                        <td class="text-center nowrap">{{ $item['fecha_venc'] ? fecha_d_m_Y($item['fecha_venc']) : '-' }}</td>
                        <td class="text-right num num-red bold">{{ number_format($item['atraso'], 2) }}</td>
                        <td class="text-center bold">{{ $item['cant_venc'] }}</td>
                        <td class="text-center bold num-red">{{ $item['dias_atraso'] }}</td>
                        <td class="text-center nowrap">{{ $item['ultimo_abono'] ? fecha_d_m_Y($item['ultimo_abono']) : '-' }}</td>
                        <td class="text-right num">{{ number_format($item['saldo_total'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="text-center" style="padding:20px;color:#718096;">No hay clientes en mora.</td></tr>
                @endforelse
            </tbody>
            @if($cuotasMora->count() > 0)
            <tfoot>
                <tr class="row-subtotal">
                    <td colspan="6" class="text-right">Subtotal &mdash; {{ $cuotasMora->count() }} registros</td>
                    <td class="text-right num num-red">{{ number_format($tAtraso2, 2) }}</td>
                    <td class="text-center">{{ $tVenc2 }}</td>
                    <td></td>
                    <td></td>
                    <td class="text-right num">{{ number_format($tPend2, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>

        {{-- ─── SECCIÓN 3: VENCIDOS ─── --}}
        <div class="section-title danger" style="margin-top:8px;">Préstamos Vencidos &mdash; Plazo cumplido con saldo pendiente</div>
        @php $tVenc3=0; $tPend3=0; @endphp
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th style="width: 15%;">Cliente</th>
                    <th style="width: 20%;">Dirección</th>
                    <th>Teléfono</th>
                    <th class="text-center nowrap">F. Desemb.</th>
                    <th class="text-center nowrap">F. Venc.</th>
                    <th class="text-center">C.Venc</th>
                    <th class="text-center">Días Atr.</th>
                    <th class="text-center nowrap">Último Abono</th>
                    <th class="text-right">Saldo Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cuotasVencidas as $index => $item)
                    @php
                        $p = $item['prestamo'];
                        $tVenc3 += $item['cant_venc']; 
                        $tPend3 += $item['saldo_total'];
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="bold">{{ $p->cliente->full_name ?? 'N/A' }}</td>
                        <td style="font-size:9px;">{{ $p->cliente->direccion ?? '-' }}</td>
                        <td class="nowrap">{{ $p->cliente->telefono1 ?? '-' }}</td>
                        <td class="text-center nowrap">{{ fecha_d_m_Y($p->fecha_desembolso) }}</td>
                        <td class="text-center nowrap num-red bold">{{ $item['fecha_venc'] ? fecha_d_m_Y($item['fecha_venc']) : '-' }}</td>
                        <td class="text-center bold">{{ $item['cant_venc'] }}</td>
                        <td class="text-center bold num-red">{{ $item['dias_atraso'] }}</td>
                        <td class="text-center nowrap">{{ $item['ultimo_abono'] ? fecha_d_m_Y($item['ultimo_abono']) : '-' }}</td>
                        <td class="text-right num num-red bold">{{ number_format($item['saldo_total'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center" style="padding:20px;color:#718096;">No hay préstamos vencidos.</td></tr>
                @endforelse
            </tbody>
            @if($cuotasVencidas->count() > 0)
            <tfoot>
                <tr class="row-subtotal">
                    <td colspan="6" class="text-right">Subtotal &mdash; {{ $cuotasVencidas->count() }} registros</td>
                    <td class="text-center">{{ $tVenc3 }}</td>
                    <td></td>
                    <td></td>
                    <td class="text-right num num-red">{{ number_format($tPend3, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>

        {{-- ─── RESUMEN GENERAL ─── --}}
        <table class="data-table" style="margin-top:8px;">
            <tbody>
                <tr class="row-grand-total">
                    <td class="text-right" style="width:50%;">
                        RESUMEN &mdash; Día: {{ $cuotasDia->count() }} &nbsp;|&nbsp; Mora: {{ $cuotasMora->count() }} &nbsp;|&nbsp; Vencidos: {{ $cuotasVencidas->count() }}
                    </td>
                    <td class="text-right num" style="width:25%;">
                        Saldo total cartera: C$ {{ number_format(($tPend1 ?? 0) + ($tPend2 ?? 0) + ($tPend3 ?? 0), 2) }}
                    </td>
                    <td class="text-right num" style="width:25%;">
                        Atraso (Día+Mora): C$ {{ number_format(($tAtraso1 ?? 0) + ($tAtraso2 ?? 0), 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="rpt-footer">Documento generado por CrediNica &mdash; {{ date('d/m/Y h:i A') }} &mdash; Uso interno exclusivo</div>
    </div>
    <script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
</body>
</html>
