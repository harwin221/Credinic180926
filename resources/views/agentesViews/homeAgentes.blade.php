@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    Home Agentes
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs" id="cobrosTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="cuotas-dia-tab" data-bs-toggle="tab" data-bs-target="#cuotasDia" type="button" role="tab" aria-controls="cuotasDia" aria-selected="true">
                        <i class="fa fa-calendar-check"></i> Cuotas del Día 
                        <span class="badge bg-primary">{{$cobrosDia->count()}}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="clientes-mora-tab" data-bs-toggle="tab" data-bs-target="#clientesMora" type="button" role="tab" aria-controls="clientesMora" aria-selected="false">
                        <i class="fa fa-exclamation-triangle"></i> Clientes en Mora 
                        <span class="badge bg-warning">{{$clientesEnMora->count()}}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="prestamos-vencidos-tab" data-bs-toggle="tab" data-bs-target="#prestamosVencidos" type="button" role="tab" aria-controls="prestamosVencidos" aria-selected="false">
                        <i class="fa fa-times-circle"></i> Préstamos Vencidos 
                        <span class="badge bg-danger">{{$prestamosVencidos->count()}}</span>
                    </button>
                </li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content" id="cobrosTabContent">
                <!-- PESTAÑA 1: CUOTAS DEL DÍA -->
                <div class="tab-pane fade show active" id="cuotasDia" role="tabpanel" aria-labelledby="cuotas-dia-tab">
                    <div class="table-responsive" style="height: 600px;overflow-y: scroll">
                        <h5 class="text-center mt-3 mb-3" style="background-color: #d4edda; padding: 10px; border-radius: 5px;">
                            <i class="fa fa-calendar-check"></i> Cuotas del Día
                        </h5>
                        <table class="table table-sm table-hover" style="font-size: 10px">
                            <thead>
                            <tr class="table-success">
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Dirección</th>
                                <th># Cuota</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($cobrosDia as $ind => $cobro)
                                <tr style="background-color: #f0f9ff;">
                                    <td>{{$loop->index+1}}</td>
                                    <td>{{$cobro->prestamo->cliente->full_name}}</td>
                                    <td>{{$cobro->prestamo->cliente->direccion}}</td>
                                    <td>{{$cobro->numero_cuota}}</td>
                                    <td>{{$cobro->prestamo->moneda." ".number_format($cobro->monto_pendiente_cuota, 2)}}</td>
                                    <td>
                                        <span class="badge bg-{{$cobro->estado == 1 ? 'primary' : 'warning'}} {{$cobro->estado == 1 ? 'text-white' : 'text-dark'}}" style="font-size: 10px;">
                                            {{$cobro->estado_cuota}}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{route('agentes.abonos.createAbono', ['prestamo' => encode($cobro->prestamo_id)])}}" class="btn btn-sm btn-success">
                                            <i class="fa fa-plus"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No hay cuotas programadas para hoy</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- PESTAÑA 2: CLIENTES EN MORA -->
                <div class="tab-pane fade" id="clientesMora" role="tabpanel" aria-labelledby="clientes-mora-tab">
                    <div class="table-responsive" style="height: 600px;overflow-y: scroll">
                        <h5 class="text-center mt-3 mb-3" style="background-color: #fff3cd; padding: 10px; border-radius: 5px;">
                            <i class="fa fa-exclamation-triangle"></i> Clientes en Mora
                        </h5>
                        <table class="table table-sm table-hover" style="font-size: 10px">
                            <thead>
                            <tr class="table-warning">
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Dirección</th>
                                <th>Días Mora</th>
                                <th>Acción</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($clientesEnMora as $ind => $prestamo)
                                @php
                                    $diasMora = $prestamo->dias_atraso['dias_atraso'] ?? 0;
                                @endphp
                                <tr style="background-color: #fffbf0;">
                                    <td>{{$loop->index+1}}</td>
                                    <td>{{$prestamo->cliente->full_name}}</td>
                                    <td>{{$prestamo->cliente->direccion}}</td>
                                    <td>
                                        <span class="badge bg-danger text-white" style="font-size: 11px;">
                                            {{$diasMora}} días
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{route('agentes.abonos.createAbono', ['prestamo' => encode($prestamo->id)])}}" class="btn btn-sm btn-success">
                                            <i class="fa fa-plus"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay clientes en mora</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- PESTAÑA 3: PRÉSTAMOS VENCIDOS -->
                <div class="tab-pane fade" id="prestamosVencidos" role="tabpanel" aria-labelledby="prestamos-vencidos-tab">
                    <div class="table-responsive" style="height: 600px;overflow-y: scroll">
                        <h5 class="text-center mt-3 mb-3" style="background-color: #f8d7da; padding: 10px; border-radius: 5px;">
                            <i class="fa fa-times-circle"></i> Préstamos Vencidos
                        </h5>
                        <table class="table table-sm table-hover" style="font-size: 10px">
                            <thead>
                            <tr class="table-danger">
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Dirección</th>
                                <th>Días Vencido</th>
                                <th>Acción</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($prestamosVencidos as $ind => $prestamo)
                                @php
                                    $ultimaCuota = $prestamo->cuotas->sortByDesc('fecha_cuota')->first();
                                    $diasVencido = 0;
                                    if ($ultimaCuota) {
                                        $fechaUltimaCuota = \Carbon\Carbon::parse($ultimaCuota->fecha_cuota);
                                        $fechaHoy = \Carbon\Carbon::now();
                                        $diasVencido = $fechaUltimaCuota->diffInDays($fechaHoy);
                                    }
                                @endphp
                                <tr style="background-color: #fff0f0;">
                                    <td>{{$loop->index+1}}</td>
                                    <td>{{$prestamo->cliente->full_name}}</td>
                                    <td>{{$prestamo->cliente->direccion}}</td>
                                    <td>
                                        <span class="badge bg-danger text-white" style="font-size: 11px;">
                                            {{$diasVencido}} días
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{route('agentes.abonos.createAbono', ['prestamo' => encode($prestamo->id)])}}" class="btn btn-sm btn-success">
                                            <i class="fa fa-plus"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No hay préstamos vencidos</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
