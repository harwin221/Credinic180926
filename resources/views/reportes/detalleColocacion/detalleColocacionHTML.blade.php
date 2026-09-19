<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Recuperación - CrediNica</title>
    <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/fontawesome-free-6.4.0-web/css/all.min.css')}}" rel="stylesheet">
    @include('reportes._corporate_styles')
</head>
<body>
    <div class="screen-actions no-print">
        <form method="GET" action="{{ route('reportes.detalleColocacion.index') }}">
            <input type="hidden" name="desde"   value="{{$desde}}">
            <input type="hidden" name="hasta"   value="{{$hasta}}">
            <input type="hidden" name="cobrador" value="{{$cobrador}}">
            <input type="hidden" name="tipo"    value="{{$tipo}}">
            <button type="submit" name="excel1" value="1" class="btn-act success"><i class="fas fa-file-excel"></i> Excel</button>
            <button type="submit" name="pdf"    value="1" class="btn-act primary" formtarget="_blank"><i class="fas fa-file-pdf"></i> PDF</button>
            <button type="button" class="btn-act primary" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
            <a href="{{ route('reportes.index') }}" class="btn-act"><i class="fas fa-arrow-left"></i> Volver</a>
        </form>
    </div>
    <div class="report-wrapper">
        <div class="rpt-header">
            <img src="{{asset('assets/img/LogoCrediNica.png')}}" alt="CrediNica">
            <div class="co-name">CrediNica</div>
            <div class="rpt-title">Reporte de Recuperación</div>
            <div class="rpt-meta">Del {{ fecha_d_m_Y($desde) }} al {{ fecha_d_m_Y($hasta) }} &nbsp;|&nbsp; Generado: {{ date('d/m/Y h:i A') }}</div>
        </div>

        @php $grandTotalCapital = 0; $grandTotalInteres = 0; $grandTotalGeneral = 0; @endphp

        @if($tipo_vista == 'resumido')
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th><th>Cobrador / Agente</th>
                        <th class="text-right">N° Clientes</th><th class="text-right">N° Abonos</th>
                        <th class="text-right">Capital</th><th class="text-right">Interés</th>
                        <th class="text-right">Total Recuperado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($resumen as $row)
                        @php
                            $grandTotalCapital += $row->monto_capital;
                            $grandTotalInteres += $row->monto_interes;
                            $grandTotalGeneral += $row->total_abono;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="bold">{{ strtoupper($row->cobrador_nombre) }}</td>
                            <td class="text-right">{{ $row->clientes }}</td>
                            <td class="text-right">{{ $row->abonos_cant }}</td>
                            <td class="text-right num">C$ {{ number_format($row->monto_capital, 2) }}</td>
                            <td class="text-right num">C$ {{ number_format($row->monto_interes, 2) }}</td>
                            <td class="text-right num num-green bold">C$ {{ number_format($row->total_abono, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center" style="padding:30px;color:#718096;">No se encontraron registros.</td></tr>
                    @endforelse
                </tbody>
                @if(count($resumen) > 0)
                <tfoot>
                    <tr class="row-grand-total">
                        <td colspan="4" class="text-right">TOTAL GENERAL</td>
                        <td class="text-right num">C$ {{ number_format($grandTotalCapital, 2) }}</td>
                        <td class="text-right num">C$ {{ number_format($grandTotalInteres, 2) }}</td>
                        <td class="text-right num">C$ {{ number_format($grandTotalGeneral, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        @else
            @php $abonosAgrupados = collect($abonosDia)->groupBy('created_user_id'); @endphp
            @forelse($abonosAgrupados as $userId => $abonos)
                @php
                    $cobradorNombre  = $abonos->first()->creador_nombre ?? 'SIN COBRADOR';
                    $subTotalCapital = $abonos->sum('total_abonado_capital');
                    $subTotalInteres = $abonos->sum('total_abonado_interes');
                    $subTotalGeneral = $abonos->sum('total_abonado');
                    $grandTotalCapital += $subTotalCapital;
                    $grandTotalInteres += $subTotalInteres;
                    $grandTotalGeneral += $subTotalGeneral;
                @endphp
                <div class="section-title">Cobrador: {{ strtoupper($cobradorNombre) }}</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>N° Préstamo</th><th>Cliente</th><th>Fecha Abono</th>
                            <th class="text-right">Capital</th><th class="text-right">Interés</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($abonos as $abono)
                        <tr>
                            <td class="bold">#{{ $abono->consecutivo }}</td>
                            <td>{{ $abono->cliente_nombre }}</td>
                            <td>{{ fecha_d_m_Y($abono->fecha_abono) }}</td>
                            <td class="text-right num">C$ {{ number_format($abono->total_abonado_capital, 2) }}</td>
                            <td class="text-right num">C$ {{ number_format($abono->total_abonado_interes, 2) }}</td>
                            <td class="text-right num num-green bold">C$ {{ number_format($abono->total_abonado, 2) }}</td>
                        </tr>
                        @endforeach
                        <tr class="row-subtotal">
                            <td colspan="3" class="text-right">Subtotal {{ strtoupper($cobradorNombre) }}</td>
                            <td class="text-right num">C$ {{ number_format($subTotalCapital, 2) }}</td>
                            <td class="text-right num">C$ {{ number_format($subTotalInteres, 2) }}</td>
                            <td class="text-right num">C$ {{ number_format($subTotalGeneral, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            @empty
                <div style="text-align:center;padding:48px;color:#718096;">No se encontraron registros.</div>
            @endforelse
            @if($abonosAgrupados->count() > 0)
            <table class="data-table" style="margin-top:4px;">
                <tbody>
                    <tr class="row-grand-total">
                        <td colspan="3" class="text-right">TOTAL GENERAL</td>
                        <td class="text-right num">C$ {{ number_format($grandTotalCapital, 2) }}</td>
                        <td class="text-right num">C$ {{ number_format($grandTotalInteres, 2) }}</td>
                        <td class="text-right num">C$ {{ number_format($grandTotalGeneral, 2) }}</td>
                    </tr>
                </tbody>
            </table>
            @endif
        @endif

        {{-- ═══════════════════════════════════════════════════════════════════
             CUADRO RESUMEN DE INFORMACIÓN DEL DÍA
        ═══════════════════════════════════════════════════════════════════ --}}
        @if(isset($resumenDia))
        <div class="resumen-dia-wrapper">
            <div class="resumen-dia-header">
                <i class="fas fa-chart-pie"></i>&nbsp; Información del Día
            </div>
            <div class="resumen-dia-grid">

                <div class="resumen-dia-card card-dia">
                    <div class="rd-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="rd-body">
                        <div class="rd-label">Día Recaudado</div>
                        <div class="rd-hint">Cuotas del día cobradas a tiempo</div>
                        <div class="rd-value">C$ {{ number_format($resumenDia['dia_recaudado'], 2) }}</div>
                    </div>
                </div>

                <div class="resumen-dia-card card-mora">
                    <div class="rd-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="rd-body">
                        <div class="rd-label">Mora Recaudada</div>
                        <div class="rd-hint">Saldos atrasados recuperados</div>
                        <div class="rd-value">C$ {{ number_format($resumenDia['mora_recaudada'], 2) }}</div>
                    </div>
                </div>

                <div class="resumen-dia-card card-proximo">
                    <div class="rd-icon"><i class="fas fa-forward"></i></div>
                    <div class="rd-body">
                        <div class="rd-label">Próximo Recaudado</div>
                        <div class="rd-hint">Pagos adelantados de cuotas futuras</div>
                        <div class="rd-value">C$ {{ number_format($resumenDia['proximo_recaudado'], 2) }}</div>
                    </div>
                </div>

                <div class="resumen-dia-card card-vencido">
                    <div class="rd-icon"><i class="fas fa-ban"></i></div>
                    <div class="rd-body">
                        <div class="rd-label">Vencido Recaudado</div>
                        <div class="rd-hint">Cobros de créditos con plazo terminado</div>
                        <div class="rd-value">C$ {{ number_format($resumenDia['vencido_recaudado'], 2) }}</div>
                    </div>
                </div>

                <div class="resumen-dia-card card-clientes">
                    <div class="rd-icon"><i class="fas fa-users"></i></div>
                    <div class="rd-body">
                        <div class="rd-label">Total Clientes Cobrados</div>
                        <div class="rd-hint">Clientes atendidos en el período</div>
                        <div class="rd-value rd-value-num">{{ $resumenDia['total_clientes'] }}</div>
                    </div>
                </div>

                <div class="resumen-dia-card card-total">
                    <div class="rd-icon"><i class="fas fa-coins"></i></div>
                    <div class="rd-body">
                        <div class="rd-label">Total Recuperado</div>
                        <div class="rd-hint">Suma total del período</div>
                        <div class="rd-value">C$ {{ number_format($resumenDia['total_general'], 2) }}</div>
                    </div>
                </div>

            </div>
        </div>
        @endif

        <div class="rpt-footer">Documento generado por CrediNica &mdash; {{ date('d/m/Y h:i A') }} &mdash; Uso interno exclusivo</div>
    </div>
    <script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
</body>
</html>
