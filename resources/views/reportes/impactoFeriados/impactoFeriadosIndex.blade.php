@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('reportes.index')])
    Análisis de Impacto de Feriados
@endsection

@section('content')

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter"></i> Filtros de Búsqueda</h5>
    </div>
    <div class="card-body">
        {{html()->form('GET', route('reportes.impactoFeriados'))->open()}}
        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="form-label">Cobrador</label>
                {{html()->select('cobrador', $listaCobradores, $cobradorSel)->class('form-control select2')->placeholder('Todos')}}
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Cliente</label>
                {{html()->select('cliente', $listaClientes, $clienteSel)->class('form-control select2')->placeholder('Todos')}}
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Estado del Préstamo</label>
                {{html()->select('estado', [
                    '1' => 'Activo',
                    '2' => 'Cancelado',
                    '3' => 'Vencido'
                ], $estadoSel)->class('form-control')->placeholder('Todos')}}
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Frecuencia</label>
                {{html()->select('frecuencia', [
                    '1' => 'Diario',
                    '2' => 'Semanal',
                    '3' => 'Quincenal',
                    '4' => 'Mensual',
                    '7' => 'Catorcenal'
                ], $frecuenciaSel)->class('form-control')->placeholder('Todas')}}
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-end">
                <button type="submit" name="generar" value="1" class="btn btn-primary">
                    <i class="fas fa-search"></i> Generar Reporte
                </button>
                @if(count($prestamosAfectados) > 0)
                    <button type="submit" name="excel" value="1" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Exportar a Excel
                    </button>
                @endif
            </div>
        </div>
        {{html()->form()->close()}}
    </div>
</div>

@if(count($prestamosAfectados) > 0)

    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                <i class="fas fa-exclamation-triangle"></i>
                Préstamos Afectados por Feriados
                <span class="badge bg-dark ms-2">{{ count($prestamosAfectados) }} préstamos</span>
            </h5>
        </div>
        <div class="card-body">

            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle"></i>
                <strong>Información:</strong> Este reporte muestra los préstamos que tienen cuotas programadas en días feriados, sábados o domingos.
                Puede ajustar manualmente cada préstamo desde su detalle.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm align-middle" style="font-size:13px; min-width:900px;">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Consecutivo</th>
                            <th>Cliente</th>
                            <th>Cobrador</th>
                            <th>Frecuencia</th>
                            <th>Estado</th>
                            <th>Total Cuotas</th>
                            <th>Cuotas Afectadas</th>
                            <th>% Afectado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prestamosAfectados as $index => $item)
                        <tr>
                            <td class="text-center"><strong>#{{ $item['prestamo']->consecutivo }}</strong></td>
                            <td>{{ $item['prestamo']->cliente->full_name ?? 'N/A' }}</td>
                            <td>{{ $item['prestamo']->agente->full_name ?? 'N/A' }}</td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark">{{ $item['prestamo']->forma_pago }}</span>
                            </td>
                            <td class="text-center">
                                @if($item['prestamo']->estado == 1)
                                    <span class="badge bg-success">Activo</span>
                                @elseif($item['prestamo']->estado == 2)
                                    <span class="badge bg-secondary">Cancelado</span>
                                @elseif($item['prestamo']->estado == 3)
                                    <span class="badge bg-danger">Vencido</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $item['total_cuotas'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-warning text-dark">{{ $item['total_afectadas'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $item['porcentaje_afectado'] > 50 ? 'danger' : 'warning' }} text-{{ $item['porcentaje_afectado'] > 50 ? 'white' : 'dark' }}">
                                    {{ $item['porcentaje_afectado'] }}%
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-info"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDetalle{{ $index }}"
                                        title="Ver cuotas afectadas">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="{{ route('prestamos.show', $item['prestamo']->id_enc) }}"
                                   class="btn btn-sm btn-primary"
                                   target="_blank"
                                   title="Ir al préstamo">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3 p-3 bg-light rounded">
                <strong>Resumen:</strong>
                Total de préstamos afectados: <strong>{{ count($prestamosAfectados) }}</strong> &nbsp;|&nbsp;
                Total de cuotas afectadas: <strong>{{ array_sum(array_column($prestamosAfectados, 'total_afectadas')) }}</strong>
            </div>

        </div>
    </div>

    {{-- Feriados registrados --}}
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="fas fa-calendar-alt"></i>
                Feriados Registrados en el Sistema
                <span class="badge bg-light text-dark ms-2">{{ count($feriados) }} feriados</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm" style="font-size:13px;">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Descripción</th>
                            <th>Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($feriados as $feriado)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($feriado->fecha)->format('d/m/Y') }}</td>
                            <td>{{ $feriado->descripcion }}</td>
                            <td>
                                @if($feriado->tipo == 1)
                                    <span class="badge bg-primary">Recurrente</span>
                                @else
                                    <span class="badge bg-secondary">No recurrente</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===================== MODALES FUERA DE LA TABLA ===================== --}}
    @foreach($prestamosAfectados as $index => $item)
    <div class="modal fade" id="modalDetalle{{ $index }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-times"></i>
                        Cuotas Afectadas — Préstamo #{{ $item['prestamo']->consecutivo }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Cliente:</strong> {{ $item['prestamo']->cliente->full_name ?? 'N/A' }}<br>
                                <strong>Cobrador:</strong> {{ $item['prestamo']->agente->full_name ?? 'N/A' }}
                            </div>
                            <div class="col-md-6">
                                <strong>Monto:</strong> {{ $item['prestamo']->moneda }} {{ number_format($item['prestamo']->monto_prestamo, 2) }}<br>
                                <strong>Frecuencia:</strong> {{ $item['prestamo']->forma_pago }}
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" style="font-size:13px;">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Cuota #</th>
                                    <th>Fecha</th>
                                    <th>Día</th>
                                    <th>Motivo</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($item['cuotas_afectadas'] as $cuota)
                                <tr>
                                    <td class="text-center">{{ $cuota['numero_cuota'] }}</td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($cuota['fecha_actual'])->format('d/m/Y') }}</td>
                                    <td>{{ ucfirst($cuota['dia_semana']) }}</td>
                                    <td class="text-center">
                                        @if($cuota['motivo'] == 'Feriado')
                                            <span class="badge bg-danger">Feriado</span>
                                        @elseif($cuota['motivo'] == 'Domingo')
                                            <span class="badge bg-warning text-dark">Domingo</span>
                                        @else
                                            <span class="badge bg-info text-dark">{{ $cuota['motivo'] }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ $item['prestamo']->moneda }} {{ number_format($cuota['monto'], 2) }}</td>
                                    <td class="text-center">
                                        @if($cuota['estado'] == 'Pagada')
                                            <span class="badge bg-success">Pagada</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $cuota['estado'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                    <a href="{{ route('prestamos.show', $item['prestamo']->id_enc) }}"
                       class="btn btn-primary" target="_blank">
                        <i class="fas fa-edit"></i> Ir a Ajustar Préstamo
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach

@elseif(request()->has('generar'))
    <div class="alert alert-success mt-3">
        <i class="fas fa-check-circle"></i>
        <strong>¡Excelente!</strong> No se encontraron préstamos con cuotas afectadas por feriados según los filtros aplicados.
    </div>
@endif

@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    });
</script>
@endsection
