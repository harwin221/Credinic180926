<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes Inactivos - CrediNica</title>
    <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/fontawesome-free-6.4.0-web/css/all.min.css')}}" rel="stylesheet">
    @include('reportes._corporate_styles')
</head>
<body>
    <div class="screen-actions no-print">
        <button type="button" class="btn-act" onclick="window.close()"><i class="fas fa-times"></i> Cerrar</button>
        <button type="button" class="btn-act primary" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
    </div>

    <div class="report-wrapper">
        <div class="rpt-header">
            <img src="{{asset('assets/img/LogoCrediNica.png')}}" alt="CrediNica">
            <div class="co-name">CrediNica</div>
            <div class="rpt-title">Clientes Inactivos</div>
            <div class="rpt-meta">Generado: {{ date('d/m/Y') }} &nbsp;|&nbsp; {{ date('h:i A') }}</div>
        </div>

        @if($estado || ($cliente && $cliente != 0) || ($cobrador && $cobrador != 0))
        <div class="filters-bar">
            @if($estado)
                <div>Estado: <span>{{ $estado == 1 ? 'Con Préstamos Activos' : 'Sin Préstamos Activos' }}</span></div>
            @endif
            @if($cobrador && $cobrador != 0)
                <div>Cobrador: <span>{{ $listaCobradores[encode(decode($cobrador))] ?? 'N/A' }}</span></div>
            @endif
            @if($cliente && $cliente != 0)
                <div>Cliente: <span>{{ $listaClientes[encode(decode($cliente))] ?? 'N/A' }}</span></div>
            @endif
        </div>
        @endif

        @if(isset($clientesAgrupados) && $estado == 2)
            @php $granTotalCreditos = 0; $granTotalClientes = 0; @endphp
            @forelse($clientesAgrupados as $agente => $clientesDeAgente)
                <div style="margin-top: 20px; margin-bottom: 5px; font-weight: bold; background-color: #f1f5f9; padding: 8px; border-left: 4px solid #0d6efd;">
                    Agente: {{ $agente }}
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre Completo</th>
                            <th>Dirección</th>
                            <th class="text-right">Monto Principal</th>
                            <th class="text-right">Interés</th>
                            <th class="text-center">Plazo</th>
                            <th class="text-center">Periodicidad</th>
                            <th class="text-center">F. Cancelación</th>
                            <th class="text-center">F. Vencimiento</th>
                            <th class="text-center">Prom. Días Atraso</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $subtotalCreditos = 0; @endphp
                        @foreach($clientesDeAgente as $index => $clienteItem)
                            @php
                                $ultimoPrestamo = $clienteItem->prestamos->first();
                                $montoUltimo   = $ultimoPrestamo ? (float) $ultimoPrestamo->monto_prestamo : 0;
                                $interesUltimo = $ultimoPrestamo ? (float) ($ultimoPrestamo->monto_financiado - $ultimoPrestamo->monto_prestamo) : 0;
                                $monedaUltimo  = ($ultimoPrestamo && $ultimoPrestamo->moneda_prestamo == 2) ? 'U$' : 'C$';
                                $formasPago = ['1'=>'Diario','2'=>'Semanal','3'=>'Quincenal','4'=>'Mensual','5'=>'Trimestral','6'=>'Bimestral','7'=>'Catorcenal'];
                                $subtotalCreditos += $montoUltimo;
                                $promedio = $clienteItem->promedio_dias_atraso ?? 0;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $clienteItem->full_name }}</td>
                                <td style="font-size:10px;">{{ $clienteItem->direccion }}</td>
                                <td class="text-right num">{{ $monedaUltimo }} {{ number_format($montoUltimo, 2) }}</td>
                                <td class="text-right num">{{ $monedaUltimo }} {{ number_format($interesUltimo, 2) }}</td>
                                <td class="text-center">{{ $ultimoPrestamo ? $ultimoPrestamo->plazo_pago : '-' }}</td>
                                <td class="text-center">{{ $ultimoPrestamo ? ($formasPago[$ultimoPrestamo->forma_pago_tipo] ?? '-') : '-' }}</td>
                                <td class="text-center">{{ $ultimoPrestamo && $ultimoPrestamo->fecha_cancelacion ? fecha_d_m_Y($ultimoPrestamo->fecha_cancelacion) : '-' }}</td>
                                <td class="text-center">{{ $ultimoPrestamo && $ultimoPrestamo->fecha_vencimiento ? fecha_d_m_Y($ultimoPrestamo->fecha_vencimiento) : '-' }}</td>
                                <td class="text-center" style="color: {{ $promedio > 3 ? '#dc3545' : ($promedio > 0 ? '#ffc107' : '#28a745') }}; font-weight: bold;">
                                    {{ number_format($promedio, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="row-grand-total">
                            <td colspan="3" class="text-right"><strong>Subtotal Agente ({{ count($clientesDeAgente) }} clientes)</strong></td>
                            <td class="text-right num"><strong>C$ {{ number_format($subtotalCreditos, 2) }}</strong></td>
                            <td colspan="6"></td>
                        </tr>
                    </tfoot>
                </table>
                @php 
                    $granTotalCreditos += $subtotalCreditos; 
                    $granTotalClientes += count($clientesDeAgente);
                @endphp
            @empty
                <table class="data-table">
                    <tr><td class="text-center" style="padding:30px;color:#718096;">No se encontraron registros.</td></tr>
                </table>
            @endforelse
            @if($granTotalClientes > 0)
                <table class="data-table" style="margin-top: 15px;">
                    <tfoot>
                        <tr class="row-grand-total">
                            <td class="text-right"><strong>GRAN TOTAL INACTIVOS ({{ $granTotalClientes }} clientes)</strong></td>
                            <td class="text-right num" style="width: 150px;"><strong>C$ {{ number_format($granTotalCreditos, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre Completo</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th class="text-right">Último Crédito</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalMontoCreditos = 0; @endphp
                    @forelse($clientes as $index => $clienteItem)
                        @php
                            $ultimoPrestamo = $clienteItem->prestamos->first();
                            $montoUltimo   = $ultimoPrestamo ? (float) $ultimoPrestamo->monto_prestamo : 0;
                            $monedaUltimo  = 'C$';
                            if ($ultimoPrestamo) {
                                $monedaUltimo = ($ultimoPrestamo->moneda_prestamo == 2) ? 'U$' : 'C$';
                            }
                            $telefono = trim($clienteItem->telefono1 . ($clienteItem->telefono2 ? ' / ' . $clienteItem->telefono2 : ''));
                            $totalMontoCreditos += $montoUltimo;
                            $prestamosActivos = $clienteItem->prestamos_activos_count ?? 0;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $clienteItem->full_name }}</td>
                            <td>{{ $telefono ?: 'N/A' }}</td>
                            <td style="font-size:10px;">{{ $clienteItem->direccion }}</td>
                            <td class="text-right num">{{ $monedaUltimo }} {{ number_format($montoUltimo, 2) }}</td>
                            <td class="text-center">
                                <span class="badge-corp {{ $prestamosActivos > 0 ? 'badge-activo' : 'badge-anulada' }}">
                                    {{ $prestamosActivos > 0 ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center" style="padding:30px;color:#718096;">No se encontraron registros.</td></tr>
                    @endforelse
                </tbody>
                @if(count($clientes) > 0)
                <tfoot>
                    <tr class="row-grand-total">
                        <td colspan="4" class="text-right"><strong>TOTAL ({{ count($clientes) }} clientes)</strong></td>
                        <td class="text-right num"><strong>C$ {{ number_format($totalMontoCreditos, 2) }}</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        @endif

        <div class="rpt-footer">Documento generado por CrediNica &mdash; {{ date('d/m/Y h:i A') }} &mdash; Uso interno exclusivo</div>
    </div>
    <script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
</body>
</html>
