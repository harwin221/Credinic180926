<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Cobranza - CrediNica</title>
    <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/fontawesome-free-6.4.0-web/css/all.min.css')}}" rel="stylesheet">
    @include('reportes._corporate_styles')
</head>
<body>
    <div class="screen-actions no-print">
        {{html()->form('GET', route('reportes.cobranza'))->open()}}
            <input type="hidden" name="cobrador"     value="{{$cobrador ?? ''}}">
            <input type="hidden" name="fecha_inicio" value="{{$inicio}}">
            <input type="hidden" name="fecha_fin"    value="{{$fin}}">
            <button type="submit" name="pdf" value="pdf" class="btn-act primary" formtarget="_blank"><i class="fas fa-file-pdf"></i> PDF</button>
            <button type="button" class="btn-act primary" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
            <a href="{{ route('reportes.cobranza') }}" class="btn-act"><i class="fas fa-arrow-left"></i> Volver</a>
        {{html()->form()->close()}}
    </div>
    <div class="report-wrapper">
        <div class="rpt-header">
            <img src="{{asset('assets/img/LogoCrediNica.png')}}" alt="CrediNica">
            <div class="co-name">CrediNica</div>
            <div class="rpt-title">Reporte de Cobros sin abono</div>
            <div class="rpt-meta">Generado: {{ date('d/m/Y') }} &nbsp;|&nbsp; {{ date('h:i A') }}</div>
        </div>
        @php 
            $totalCuota = 0; 
            $totalAbonado = 0; 
            $totalPendiente = 0; 
            $cuotasGrouped = $cuotas->groupBy(fn($c) => $c->prestamo->agente->full_name ?? 'SIN COBRADOR');
        @endphp

        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>N° Préstamo</th>
                    <th>Cliente</th>
                    <th class="text-center">N° Cuota</th>
                    <th class="text-center">Fecha Cuota</th>
                    <th class="text-right">Monto Cuota</th>
                    <th class="text-right">Abonado</th>
                    <th class="text-right">Pendiente</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cuotasGrouped as $cobradorNombre => $cuotasDelCobrador)
                    <tr class="row-group-header">
                        <td colspan="9" style="background: #1a1a2e; color: #fff; font-weight: bold; text-transform: uppercase; padding: 6px 12px; font-size: 10px;">
                            <i class="fas fa-user-tie mr-2"></i> Cobrador: {{ $cobradorNombre }}
                        </td>
                    </tr>
                    @foreach($cuotasDelCobrador as $index => $cuota)
                        @php
                            $abonado   = $cuota->abonos->sum('monto_abono');
                            $pendiente = $cuota->monto_cuota - $abonado;
                            $totalCuota += $cuota->monto_cuota;
                            $totalAbonado += $abonado;
                            $totalPendiente += $pendiente;
                        @endphp
                        <tr>
                            <td>{{ $loop->parent->index * 0 + $loop->iteration }}</td>
                            <td class="bold">#{{ $cuota->prestamo->consecutivo ?? 'N/A' }}</td>
                            <td>{{ $cuota->prestamo->cliente->full_name ?? 'N/A' }}</td>
                            <td class="text-center">{{ $cuota->numero_cuota }}</td>
                            <td class="text-center">{{ fecha_d_m_Y($cuota->fecha_cuota) }}</td>
                            <td class="text-right num">C$ {{ number_format($cuota->monto_cuota, 2) }}</td>
                            <td class="text-right num num-green">C$ {{ number_format($abonado, 2) }}</td>
                            <td class="text-right num num-red bold">C$ {{ number_format($pendiente, 2) }}</td>
                            <td class="text-center">
                                @if($cuota->estado == 1)     <span class="badge-corp badge-pendiente">Pendiente</span>
                                @elseif($cuota->estado == 2) <span class="badge-corp badge-pagada">Pagada</span>
                                @else                        <span class="badge-corp badge-anulada">Anulada</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr><td colspan="9" class="text-center" style="padding:30px;color:#718096;">No se encontraron cuotas.</td></tr>
                @endforelse
            </tbody>
            @if(count($cuotas) > 0)
            <tfoot>
                <tr class="row-grand-total">
                    <td colspan="5" class="text-right">TOTAL GENERAL &mdash; {{ count($cuotas) }} cuota{{ count($cuotas) != 1 ? 's' : '' }}</td>
                    <td class="text-right num">C$ {{ number_format($totalCuota, 2) }}</td>
                    <td class="text-right num">C$ {{ number_format($totalAbonado, 2) }}</td>
                    <td class="text-right num" style="color:#fca5a5;">C$ {{ number_format($totalPendiente, 2) }}</td>
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
