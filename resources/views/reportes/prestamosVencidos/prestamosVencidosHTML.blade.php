<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Préstamos Vencidos - CrediNica</title>
    <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/fontawesome-free-6.4.0-web/css/all.min.css')}}" rel="stylesheet">
    @include('reportes._corporate_styles')
</head>
<body>
    <div class="screen-actions no-print">
        {{html()->form('GET', route('reportes.prestamosVencidos.index'))->open()}}
            <input type="hidden" name="cobrador" value="{{$cobradorSel}}">
            <button type="submit" name="pdf" value="pdf" class="btn-act primary" formtarget="_blank"><i class="fas fa-file-pdf"></i> PDF</button>
            <button type="button" class="btn-act primary" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
            <a href="{{ route('reportes.prestamosVencidos.index') }}" class="btn-act"><i class="fas fa-arrow-left"></i> Volver</a>
        {{html()->form()->close()}}
    </div>
    <div class="report-wrapper">
        <div class="rpt-header">
            <img src="{{asset('assets/img/LogoCrediNica.png')}}" alt="CrediNica">
            <div class="co-name">CrediNica</div>
            <div class="rpt-title">Préstamos Vencidos</div>
            <div class="rpt-meta">Generado: {{ date('d/m/Y') }} &nbsp;|&nbsp; {{ date('h:i A') }}</div>
        </div>

        @php $totalMonto = 0; $totalFinanciado = 0; @endphp
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th><th>N° Préstamo</th><th>Cliente</th><th>Cobrador</th>
                    <th class="text-right">Monto Préstamo</th><th class="text-right">Total Financiado</th>
                    <th class="text-center">Plazo</th><th class="text-center">Frecuencia</th>
                    <th class="text-center">Fecha Préstamo</th><th class="text-center">Última Cuota</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prestamosVencidos as $index => $prestamo)
                    @php $totalMonto += $prestamo->monto_prestamo; $totalFinanciado += $prestamo->monto_financiado; @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="bold">#{{ $prestamo->consecutivo }}</td>
                        <td>{{ $prestamo->cliente->full_name ?? 'N/A' }}</td>
                        <td>{{ $prestamo->agente->full_name ?? 'N/A' }}</td>
                        <td class="text-right num">C$ {{ number_format($prestamo->monto_prestamo, 2) }}</td>
                        <td class="text-right num bold">C$ {{ number_format($prestamo->monto_financiado, 2) }}</td>
                        <td class="text-center">{{ $prestamo->plazo }}</td>
                        <td class="text-center">{{ $prestamo->forma_pago }}</td>
                        <td class="text-center">{{ fecha_d_m_Y($prestamo->fecha_prestamo) }}</td>
                        <td class="text-center num-red bold">{{ fecha_d_m_Y($prestamo->ultima_fecha_vencimiento) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center" style="padding:30px;color:#718096;">No se encontraron préstamos vencidos.</td></tr>
                @endforelse
            </tbody>
            @if(count($prestamosVencidos) > 0)
            <tfoot>
                <tr class="row-grand-total">
                    <td colspan="4" class="text-right">TOTAL GENERAL &mdash; {{ count($prestamosVencidos) }} préstamo{{ count($prestamosVencidos) != 1 ? 's' : '' }}</td>
                    <td class="text-right num">C$ {{ number_format($totalMonto, 2) }}</td>
                    <td class="text-right num">C$ {{ number_format($totalFinanciado, 2) }}</td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
            @endif
        </table>

        <div class="rpt-footer">Documento generado por CrediNica &mdash; {{ date('d/m/Y h:i A') }} &mdash; Uso interno exclusivo</div>
    </div>
    <script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
</body>
</html>
