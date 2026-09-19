<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuotas Vencidas - CrediNica</title>
    <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/fontawesome-free-6.4.0-web/css/all.min.css')}}" rel="stylesheet">
    @include('reportes._corporate_styles')
</head>
<body>
    <div class="screen-actions no-print">
        <form method="GET" action="{{ route('reportes.cuotasVencidas.index') }}">
            <input type="hidden" name="desde"    value="{{$desde}}">
            <input type="hidden" name="hasta"    value="{{$hasta}}">
            <input type="hidden" name="cobrador" value="{{$cobradorSel}}">
            <input type="hidden" name="cliente"  value="{{$clienteSel}}">
            <button type="submit" name="excel" value="1" class="btn-act success"><i class="fas fa-file-excel"></i> Excel</button>
            <button type="submit" name="pdf"    value="1" class="btn-act primary" formtarget="_blank"><i class="fas fa-file-pdf"></i> PDF</button>
            <button type="button" class="btn-act primary" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
            <a href="{{ route('reportes.cuotasVencidas.index') }}" class="btn-act"><i class="fas fa-arrow-left"></i> Volver</a>
        </form>
    </div>
    <div class="report-wrapper">
        <div class="rpt-header">
            <img src="{{asset('assets/img/LogoCrediNica.png')}}" alt="CrediNica">
            <div class="co-name">CrediNica</div>
            <div class="rpt-title">Lista de Cuotas Vencidas</div>
            <div class="rpt-meta">Del {{ fecha_d_m_Y($desde) }} al {{ fecha_d_m_Y($hasta) }} &nbsp;|&nbsp; Generado: {{ date('d/m/Y h:i A') }}</div>
        </div>
        @php 
            $totalCuota = 0; $totalAbonado = 0; $totalPendiente = 0; 
            $cuotasAgrupadas = collect($cuotasVencidas)->groupBy('agente_id');
        @endphp

        @foreach($cuotasAgrupadas as $agenteId => $grupo)
            @php 
                $nombreCobrador = $grupo->first()->agente_nombre ?? 'SIN ASIGNAR';
                $subtotalCuota = 0;
                $subtotalAbonado = 0;
                $subtotalPendiente = 0;
            @endphp
            <div class="section-title">Cobrador: {{ strtoupper($nombreCobrador) }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th><th>N° Préstamo</th><th>Cliente</th>
                        <th class="text-center">N° Cuota</th><th class="text-center">Vencimiento</th>
                        <th class="text-right">Monto Cuota</th><th class="text-right">Abonado</th>
                        <th class="text-right">Pendiente</th><th class="text-center">Días Venc.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grupo as $index => $cuota)
                        @php
                            $abonado     = $cuota->suma_abono ?? 0;
                            $pendiente   = $cuota->monto_cuota - $abonado;
                            $diasVencidos = \Carbon\Carbon::parse($cuota->fecha_cuota)->diffInDays(now());
                            
                            $subtotalCuota    += $cuota->monto_cuota;
                            $subtotalAbonado  += $abonado;
                            $subtotalPendiente += $pendiente;
                            
                            $totalCuota    += $cuota->monto_cuota;
                            $totalAbonado  += $abonado;
                            $totalPendiente += $pendiente;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="bold">#{{ $cuota->consecutivo }}</td>
                            <td>{{ $cuota->cliente_nombre }}</td>
                            <td class="text-center">{{ $cuota->numero_cuota }}</td>
                            <td class="text-center">{{ fecha_d_m_Y($cuota->fecha_cuota) }}</td>
                            <td class="text-right num">C$ {{ number_format($cuota->monto_cuota, 2) }}</td>
                            <td class="text-right num num-green">C$ {{ number_format($abonado, 2) }}</td>
                            <td class="text-right num num-red bold">C$ {{ number_format($pendiente, 2) }}</td>
                            <td class="text-center num-red bold">{{ $diasVencidos }} días</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f3f4f6; font-weight: 600;">
                        <td colspan="5" class="text-right">SUBTOTAL {{ strtoupper($nombreCobrador) }} &mdash; {{ count($grupo) }} cuota{{ count($grupo) != 1 ? 's' : '' }}</td>
                        <td class="text-right num">C$ {{ number_format($subtotalCuota, 2) }}</td>
                        <td class="text-right num">C$ {{ number_format($subtotalAbonado, 2) }}</td>
                        <td class="text-right num" style="color:#dc2626;">C$ {{ number_format($subtotalPendiente, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <br>
        @endforeach

        @if(count($cuotasVencidas) > 0)
        <table class="data-table" style="margin-top:10px;">
            <tfoot>
                <tr class="row-grand-total">
                    <td colspan="6" class="text-right">TOTAL GENERAL &mdash; {{ count($cuotasVencidas) }} cuota{{ count($cuotasVencidas) != 1 ? 's' : '' }}</td>
                    <td class="text-right num">C$ {{ number_format($totalCuota, 2) }}</td>
                    <td class="text-right num">C$ {{ number_format($totalAbonado, 2) }}</td>
                    <td class="text-right num" style="color:#fca5a5;">C$ {{ number_format($totalPendiente, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        @endif
        <div class="rpt-footer">Documento generado por CrediNica &mdash; {{ date('d/m/Y h:i A') }} &mdash; Uso interno exclusivo</div>
    </div>
    <script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
</body>
</html>
