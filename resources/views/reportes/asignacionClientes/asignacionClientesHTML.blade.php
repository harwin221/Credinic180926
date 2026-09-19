<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignación de Clientes - CrediNica</title>
    <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/fontawesome-free-6.4.0-web/css/all.min.css')}}" rel="stylesheet">
    @include('reportes._corporate_styles')
</head>
<body>
    <div class="screen-actions no-print">
        {{html()->form('GET', route('reportes.asignacionClientes.index'))->open()}}
            <input type="hidden" name="cliente" value="{{$cliente}}">
            <input type="hidden" name="cobrador" value="{{$cobrador}}">
            <button type="submit" name="excel" value="excel" class="btn-act success">
                <i class="fas fa-file-excel"></i> Excel
            </button>
            <button type="submit" name="pdf" value="pdf" class="btn-act primary" formtarget="_blank">
                <i class="fas fa-file-pdf"></i> PDF
            </button>
            <button type="button" class="btn-act primary" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir
            </button>
        {{html()->form()->close()}}
    </div>

    <div class="report-wrapper">
        <div class="rpt-header">
            <img src="{{asset('assets/img/LogoCrediNica.png')}}" alt="CrediNica">
            <div class="co-name">CrediNica</div>
            <div class="rpt-title">Reporte de Asignación de Clientes</div>
            <div class="rpt-meta">Generado: {{ date('d/m/Y') }} &nbsp;|&nbsp; {{ date('h:i A') }}</div>
        </div>

        @if($cliente || $cobrador)
        <div class="filters-bar">
            @if($cliente && $cliente != 0)<div>Cliente: <span>{{ $listaClientes[encode($cliente)] ?? 'N/A' }}</span></div>@endif
            @if($cobrador && $cobrador != 0)<div>Cobrador: <span>{{ $listaCobradores[encode($cobrador)] ?? 'N/A' }}</span></div>@endif
        </div>
        @endif

        @php $totalFinanciado = 0; @endphp
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>N° Préstamo</th>
                    <th>Cliente</th>
                    <th>Cédula</th>
                    <th>Teléfono</th>
                    <th>Cobrador Asignado</th>
                    <th class="text-center">Fecha Préstamo</th>
                    <th class="text-right">Monto Financiado</th>
                    <th class="text-center">Forma de Pago</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prestamos as $index => $prestamo)
                    @php $totalFinanciado += $prestamo->monto_financiado; @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="bold">#{{ $prestamo->consecutivo }}</td>
                        <td>{{ $prestamo->cliente->full_name ?? 'N/A' }}</td>
                        <td>{{ $prestamo->cliente->cedula ?? 'N/A' }}</td>
                        <td>{{ $prestamo->cliente->telefono ?? 'N/A' }}</td>
                        <td>{{ $prestamo->agente->full_name ?? 'N/A' }}</td>
                        <td class="text-center">{{ fecha_d_m_Y($prestamo->fecha_prestamo) }}</td>
                        <td class="text-right num">C$ {{ number_format($prestamo->monto_financiado, 2) }}</td>
                        <td class="text-center">{{ $prestamo->forma_pago }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center" style="padding:30px; color:#718096;">No se encontraron asignaciones con los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
            @if(count($prestamos) > 0)
            <tfoot>
                <tr class="row-grand-total">
                    <td colspan="7" class="text-right">TOTAL FINANCIADO &mdash; {{ count($prestamos) }} préstamo{{ count($prestamos) != 1 ? 's' : '' }}</td>
                    <td class="text-right num">C$ {{ number_format($totalFinanciado, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>

        <div class="rpt-footer">Documento generado por CrediNica &mdash; {{ date('d/m/Y h:i A') }} &mdash; Uso interno exclusivo</div>
    </div>

    <script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
</body>
</html>
