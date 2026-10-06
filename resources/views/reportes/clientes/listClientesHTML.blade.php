<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Desembolsos - CrediNica</title>
    <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/fontawesome-free-6.4.0-web/css/all.min.css')}}" rel="stylesheet">
    @include('reportes._corporate_styles')
</head>
<body>
    <div class="screen-actions no-print">
        {{html()->form('GET', route('reportes.listaClientes.index'))->open()}}
            <input type="hidden" name="cliente"      value="{{$clienteSel}}">
            @foreach((array)$cobradorSel as $c)<input type="hidden" name="cobrador[]" value="{{$c}}">@endforeach
            <input type="hidden" name="vendedor"     value="{{$vendedorSel}}">
            <input type="hidden" name="estado"       value="{{$estadoSel}}">
            <input type="hidden" name="frecuencia"   value="{{$frecuenciaSel}}">
            <input type="hidden" name="fecha_inicio" value="{{$inicioSel}}">
            <input type="hidden" name="fecha_fin"    value="{{$finSel}}">
            <button type="submit" name="pdf" value="pdf" class="btn-act primary" formtarget="_blank">
                <i class="fas fa-file-pdf"></i> PDF
            </button>
            <button type="submit" name="excel" value="excel" class="btn-act success" style="background-color: #28a745; color: white;">
                <i class="fas fa-file-excel"></i> Excel
            </button>
            <button type="button" class="btn-act primary" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <a href="{{ route('reportes.listaClientes.index') }}" class="btn-act">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        {{html()->form()->close()}}
    </div>

    <div class="report-wrapper">
        <div class="rpt-header">
            <div class="logo-col">
                <img src="{{asset('assets/img/LogoCrediNica.png')}}" alt="CrediNica">
                <span class="tagline">Contigo cuando lo necesitas</span>
            </div>
            <div class="title-col">
                <div class="co-name">Credi<span>Nica</span></div>
                <div class="rpt-title">Lista de Desembolsos</div>
                <div class="rpt-meta">Generado: {{ date('d/m/Y') }} &nbsp;|&nbsp; {{ date('h:i A') }}</div>
            </div>
            <div class="info-col">
                @if($inicioSel && $finSel)
                    <strong>Período:</strong><br>
                    {{ fecha_d_m_Y($inicioSel) }}<br>
                    al {{ fecha_d_m_Y($finSel) }}
                @else
                    <strong>Fecha:</strong><br>{{ date('d/m/Y') }}
                @endif
            </div>
        </div>

        @php
            $prestamosAgrupados  = collect($prestamos)->groupBy('agente_id');
            $grandTotalMonto     = 0;
            $grandTotalInteres   = 0;
            $grandTotalGeneral   = 0;
            $grandTotalPrestamos = 0;
        @endphp

        @forelse($prestamosAgrupados as $agenteId => $prestamosGrupo)
            @php
                $cobradorNombre   = $prestamosGrupo->first()->agente_nombre ?? 'SIN COBRADOR';
                $subtotalMonto    = 0; $subtotalInteres = 0; $subtotalGeneral = 0;
                $cantidadPrestamos = $prestamosGrupo->count();
                foreach($prestamosGrupo as $p) {
                    $subtotalMonto   += $p->monto_prestamo;
                    $subtotalInteres += ($p->monto_financiado - $p->monto_prestamo);
                    $subtotalGeneral += $p->monto_financiado;
                }
                $grandTotalMonto     += $subtotalMonto;
                $grandTotalInteres   += $subtotalInteres;
                $grandTotalGeneral   += $subtotalGeneral;
                $grandTotalPrestamos += $cantidadPrestamos;
            @endphp

            {{-- Espacio entre grupos (excepto el primero) --}}
            @if(!$loop->first)
                <div style="height:16px; background:#f8fafc; border-top:1px solid #e2e8f0;"></div>
            @endif
                    $subtotalInteres += ($p->monto_financiado - $p->monto_prestamo);
                    $subtotalGeneral += $p->monto_financiado;
                }
                $grandTotalMonto     += $subtotalMonto;
                $grandTotalInteres   += $subtotalInteres;
                $grandTotalGeneral   += $subtotalGeneral;
                $grandTotalPrestamos += $cantidadPrestamos;
            @endphp

            <div class="section-title">Cobrador: {{ strtoupper($cobradorNombre) }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>N° Préstamo</th>
                        <th>Cliente</th>
                        <th class="text-right">Monto</th>
                        <th class="text-right">Interés</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Plazo</th>
                        <th class="text-center">Frecuencia</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center">Fecha</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prestamosGrupo as $prestamo)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="bold">#{{ $prestamo->consecutivo }}</td>
                        <td>{{ $prestamo->cliente_nombre ?? 'N/A' }}</td>
                        <td class="text-right num">{{ $prestamo->moneda }} {{ number_format($prestamo->monto_prestamo, 2) }}</td>
                        <td class="text-right num">{{ $prestamo->moneda }} {{ number_format($prestamo->monto_financiado - $prestamo->monto_prestamo, 2) }}</td>
                        <td class="text-right num bold">{{ $prestamo->moneda }} {{ number_format($prestamo->monto_financiado, 2) }}</td>
                        <td class="text-center">{{ $prestamo->plazo }}</td>
                        <td class="text-center">{{ $prestamo->forma_pago }}</td>
                        <td class="text-center">{{ $prestamo->tipo_prestamo_texto ?? 'N/A' }}</td>
                        <td class="text-center">{{ fecha_d_m_Y($prestamo->fecha_prestamo) }}</td>
                        <td class="text-center">
                            <span class="badge-corp {{ $prestamo->estado == 1 ? 'badge-activo' : 'badge-cancelado' }}">
                                {{ $prestamo->estado == 1 ? 'Activo' : 'Cancelado' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                    <tr class="row-subtotal">
                        <td colspan="3" class="text-right">Subtotal — {{ $cantidadPrestamos }} préstamo{{ $cantidadPrestamos != 1 ? 's' : '' }}</td>
                        <td class="text-right num">C$ {{ number_format($subtotalMonto, 2) }}</td>
                        <td class="text-right num">C$ {{ number_format($subtotalInteres, 2) }}</td>
                        <td class="text-right num">C$ {{ number_format($subtotalGeneral, 2) }}</td>
                        <td colspan="5"></td>
                    </tr>
                </tbody>
            </table>
        @empty
            <div style="text-align:center; padding:48px; color:#718096;">No se encontraron préstamos desembolsados.</div>
        @endforelse

        @if($prestamosAgrupados->count() > 0)
        <div class="grand-total-block">
            <div class="gt-title">Montos Totales de los Desembolsos</div>
            <div class="gt-grid">
                <div class="gt-item">
                    <div class="gt-label">Total Préstamos</div>
                    <div class="gt-value dark">{{ $grandTotalPrestamos }}</div>
                </div>
                <div class="gt-item">
                    <div class="gt-label">Total Monto</div>
                    <div class="gt-value">C$ {{ number_format($grandTotalMonto, 2) }}</div>
                </div>
                <div class="gt-item">
                    <div class="gt-label">Total Interés</div>
                    <div class="gt-value">C$ {{ number_format($grandTotalInteres, 2) }}</div>
                </div>
                <div class="gt-item">
                    <div class="gt-label">Total General (C+I)</div>
                    <div class="gt-value dark">C$ {{ number_format($grandTotalGeneral, 2) }}</div>
                </div>
            </div>
        </div>
        @endif

        <div class="rpt-footer">Documento generado por CrediNica &mdash; {{ date('d/m/Y h:i A') }} &mdash; Uso interno exclusivo</div>
    </div>
    <script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
</body>
</html>
