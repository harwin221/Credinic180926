<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impacto de Feriados - CrediNica</title>
    <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/fontawesome-free-6.4.0-web/css/all.min.css')}}" rel="stylesheet">
    @include('reportes._corporate_styles')
    <style>
        .cuotas-detalle { display: none; }
        .cuotas-detalle.show { display: table-row-group; }
        .btn-toggle { cursor: pointer; background: none; border: none; padding: 0; color: #1a1a2e; font-size: 11px; }
        .btn-toggle:hover { color: #2563eb; }
        .sub-table { width: 100%; border-collapse: collapse; font-size: 10px; }
        .sub-table th { background: #e2e8f0; color: #4a5568; padding: 4px 8px; font-weight: 700; text-transform: uppercase; font-size: 9px; }
        .sub-table td { padding: 4px 8px; border-bottom: 1px solid #f0f2f5; }
        .sub-row td { background: #f7f8fa !important; padding: 0 !important; }
        .sub-row td > div { padding: 8px 16px 8px 40px; }
        @media print {
            .cuotas-detalle { display: table-row-group !important; }
            .btn-toggle { display: none; }
        }
    </style>
</head>
<body>
    <div class="screen-actions no-print">
        <button type="button" class="btn-act" onclick="window.close()">
            <i class="fas fa-times"></i> Cerrar
        </button>
        <button type="button" class="btn-act primary" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir
        </button>
        @if(isset($excelUrl))
        <a href="{{ $excelUrl }}" class="btn-act success">
            <i class="fas fa-file-excel"></i> Exportar Excel
        </a>
        @endif
    </div>

    <div class="report-wrapper wide">
        <div class="rpt-header">
            <img src="{{asset('assets/img/LogoCrediNica.png')}}" alt="CrediNica">
            <div class="co-name">CrediNica</div>
            <div class="rpt-title">Análisis de Impacto de Feriados</div>
            <div class="rpt-meta">Generado: {{ date('d/m/Y') }} &nbsp;|&nbsp; {{ date('h:i A') }}</div>
        </div>

        {{-- Barra de filtros aplicados --}}
        @if($cobradorSel || $clienteSel || $estadoSel || $frecuenciaSel)
        <div class="filters-bar">
            @if($estadoSel)
                <div>Estado: <span>{{ ['1'=>'Activo','2'=>'Cancelado','3'=>'Vencido'][$estadoSel] ?? 'Todos' }}</span></div>
            @endif
            @if($frecuenciaSel)
                <div>Frecuencia: <span>{{ ['1'=>'Diario','2'=>'Semanal','3'=>'Quincenal','4'=>'Mensual','7'=>'Catorcenal'][$frecuenciaSel] ?? 'Todas' }}</span></div>
            @endif
        </div>
        @endif

        @if(count($prestamosAfectados) > 0)

        {{-- Resumen --}}
        <div style="padding: 10px 20px; background:#fffbeb; border-bottom: 1px solid #fde68a; font-size:11px; display:flex; gap:30px; flex-wrap:wrap;">
            <div><i class="fas fa-exclamation-triangle" style="color:#b45309;"></i> &nbsp;
                <strong>Préstamos afectados:</strong> {{ count($prestamosAfectados) }}
            </div>
            <div><i class="fas fa-calendar-times" style="color:#b91c1c;"></i> &nbsp;
                <strong>Total cuotas afectadas:</strong> {{ array_sum(array_column($prestamosAfectados, 'total_afectadas')) }}
            </div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:30px;"></th>
                    <th>Consecutivo</th>
                    <th>Cliente</th>
                    <th>Cobrador</th>
                    <th class="text-center">Frecuencia</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Total Cuotas</th>
                    <th class="text-center">Afectadas</th>
                    <th class="text-center">% Afectado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prestamosAfectados as $index => $item)
                {{-- Fila principal --}}
                <tr>
                    <td class="text-center no-print">
                        <button class="btn-toggle" onclick="toggleDetalle({{ $index }})" title="Ver cuotas afectadas">
                            <i class="fas fa-chevron-down" id="icon-{{ $index }}"></i>
                        </button>
                    </td>
                    <td><strong>#{{ $item['prestamo']->consecutivo }}</strong></td>
                    <td>{{ $item['prestamo']->cliente->full_name ?? 'N/A' }}</td>
                    <td>{{ $item['prestamo']->agente->full_name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $item['prestamo']->forma_pago }}</td>
                    <td class="text-center">
                        @if($item['prestamo']->estado == 1)
                            <span class="badge-corp badge-activo">Activo</span>
                        @elseif($item['prestamo']->estado == 2)
                            <span class="badge-corp badge-cancelado">Cancelado</span>
                        @elseif($item['prestamo']->estado == 3)
                            <span class="badge-corp badge-vencido">Vencido</span>
                        @endif
                    </td>
                    <td class="text-center bold">{{ $item['total_cuotas'] }}</td>
                    <td class="text-center bold num-red">{{ $item['total_afectadas'] }}</td>
                    <td class="text-center">
                        <span class="badge-corp {{ $item['porcentaje_afectado'] > 50 ? 'badge-vencido' : 'badge-mora' }}">
                            {{ $item['porcentaje_afectado'] }}%
                        </span>
                    </td>
                </tr>
                {{-- Fila de detalle de cuotas (expandible) --}}
                <tr class="sub-row" id="detalle-{{ $index }}">
                    <td colspan="9">
                        <div>
                            <table class="sub-table">
                                <thead>
                                    <tr>
                                        <th>Cuota #</th>
                                        <th>Fecha</th>
                                        <th>Día</th>
                                        <th>Motivo</th>
                                        <th class="text-right">Monto</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item['cuotas_afectadas'] as $cuota)
                                    <tr>
                                        <td class="text-center">{{ $cuota['numero_cuota'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($cuota['fecha_actual'])->format('d/m/Y') }}</td>
                                        <td>{{ ucfirst($cuota['dia_semana']) }}</td>
                                        <td>
                                            @if($cuota['motivo'] == 'Feriado')
                                                <span class="badge-corp badge-vencido">Feriado</span>
                                            @elseif($cuota['motivo'] == 'Domingo')
                                                <span class="badge-corp badge-mora">Domingo</span>
                                            @else
                                                <span class="badge-corp badge-pendiente">{{ $cuota['motivo'] }}</span>
                                            @endif
                                        </td>
                                        <td class="text-right num">
                                            {{ $item['prestamo']->moneda }} {{ number_format($cuota['monto'], 2) }}
                                        </td>
                                        <td class="text-center">
                                            @if($cuota['estado'] == 'Pagada')
                                                <span class="badge-corp badge-pagada">Pagada</span>
                                            @else
                                                <span class="badge-corp badge-pendiente">{{ $cuota['estado'] }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="row-grand-total">
                    <td colspan="7" class="text-right"><strong>TOTALES</strong></td>
                    <td class="text-center bold">{{ array_sum(array_column($prestamosAfectados, 'total_afectadas')) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>



        @else
        <div style="text-align:center; padding: 40px; color: #718096;">
            <i class="fas fa-check-circle" style="font-size:32px; color:#15803d; margin-bottom:10px; display:block;"></i>
            No se encontraron préstamos con cuotas afectadas por feriados según los filtros aplicados.
        </div>
        @endif

        <div class="rpt-footer">Documento generado por CrediNica &mdash; {{ date('d/m/Y h:i A') }} &mdash; Uso interno exclusivo</div>
    </div>

    <script>
        function toggleDetalle(index) {
            var row = document.getElementById('detalle-' + index);
            var icon = document.getElementById('icon-' + index);
            if (row.style.display === 'none' || row.style.display === '') {
                row.style.display = 'table-row';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                row.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }
        // Ocultar todas las filas de detalle al cargar
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('[id^="detalle-"]').forEach(function(row) {
                row.style.display = 'none';
            });
        });
    </script>
</body>
</html>
