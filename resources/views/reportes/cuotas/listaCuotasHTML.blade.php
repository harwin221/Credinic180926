<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Abonos - CrediNica</title>
    <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/fontawesome-free-6.4.0-web/css/all.min.css')}}" rel="stylesheet">
    @include('reportes._corporate_styles')
</head>
<body>
    <div class="screen-actions no-print">
        {{html()->form('GET', route('reportes.listaCuotas.index'))->open()}}
            <input type="hidden" name="desde"    value="{{$desdeSel}}">
            <input type="hidden" name="hasta"    value="{{$hastaSel}}">
            <input type="hidden" name="cobrador" value="{{$cobradorSel}}">
            <button type="submit" name="pdf" value="pdf" class="btn-act primary" formtarget="_blank"><i class="fas fa-file-pdf"></i> PDF</button>
            <button type="button" class="btn-act primary" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
            <a href="{{ route('reportes.listaCuotas.index') }}" class="btn-act"><i class="fas fa-arrow-left"></i> Volver</a>
        {{html()->form()->close()}}
    </div>
    <div class="report-wrapper">
        <div class="rpt-header">
            <img src="{{asset('assets/img/LogoCrediNica.png')}}" alt="CrediNica">
            <div class="co-name">CrediNica</div>
            <div class="rpt-title">Lista de Abonos de Cuotas</div>
            <div class="rpt-meta">Generado: {{ date('d/m/Y') }} &nbsp;|&nbsp; {{ date('h:i A') }}</div>
        </div>
        @php $totalCapital = 0; $totalInteres = 0; $totalAbonado = 0; @endphp
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th><th>Fecha Abono</th><th>N° Préstamo</th>
                    <th>Cliente</th><th>Cobrador</th>
                    <th class="text-right">Capital</th><th class="text-right">Interés</th>
                    <th class="text-right">Total Abonado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($abonos as $index => $abono)
                    @php
                        $totalCapital += $abono->total_abonado_capital ?? 0;
                        $totalInteres += $abono->total_abonado_interes ?? 0;
                        $totalAbonado += $abono->total_abonado ?? 0;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ fecha_d_m_Y($abono->fecha_abono) }}</td>
                        <td class="bold">#{{ $abono->prestamo->consecutivo ?? 'N/A' }}</td>
                        <td>{{ $abono->prestamo->cliente->full_name ?? 'N/A' }}</td>
                        <td>{{ $abono->prestamo->agente->full_name ?? 'N/A' }}</td>
                        <td class="text-right num">C$ {{ number_format($abono->total_abonado_capital ?? 0, 2) }}</td>
                        <td class="text-right num">C$ {{ number_format($abono->total_abonado_interes ?? 0, 2) }}</td>
                        <td class="text-right num num-green bold">C$ {{ number_format($abono->total_abonado ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center" style="padding:30px;color:#718096;">No se encontraron abonos registrados.</td></tr>
                @endforelse
            </tbody>
            @if(count($abonos) > 0)
            <tfoot>
                <tr class="row-grand-total">
                    <td colspan="5" class="text-right">TOTAL GENERAL &mdash; {{ count($abonos) }} abono{{ count($abonos) != 1 ? 's' : '' }}</td>
                    <td class="text-right num">C$ {{ number_format($totalCapital, 2) }}</td>
                    <td class="text-right num">C$ {{ number_format($totalInteres, 2) }}</td>
                    <td class="text-right num">C$ {{ number_format($totalAbonado, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
        <div class="rpt-footer">Documento generado por CrediNica &mdash; {{ date('d/m/Y h:i A') }} &mdash; Uso interno exclusivo</div>
    </div>
    <script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
</body>
</html>
