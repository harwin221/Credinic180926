@extends('layouts.app')
@section('tituloPagina')
    Lista de Solicitudes
@endsection
@section('content')
    {{html()->form('GET',route('prestamos.solicitudes.indexSolicitudes'))->id('frmSolicitud')->open()}}

    <div class="row">
        <div class="col-sm-12 col-md-6">
            <label for=""><b>Buscar</b></label>
            <div class="input-group mb-3">
                <input type="text" name="buscar" value="{{request('buscar')}}" class="form-control" placeholder="Buscar"
                       aria-label="Recipient's username" aria-describedby="button-addon2">
                <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Buscar</button>
            </div>
        </div>
        <div class="col-sm-12 col-md-6">
            <div class="form-group">
                <label for="estado"><b>Filtrar por Estado</b></label>
                {{html()->select('estado',['1'=>'Pendiente','3'=>'Rechazado','2'=>'Aprobado','4'=>'Todos'],request('estado'))->class('form-control')->id('buscar')}}
            </div>
        </div>
    </div>
    {{html()->form()->close()}}

<div class="d-flex justify-content-center flex-row flex-wrap">
        <button type="button" class="btn btn-md m-2 btn-warning" data-bs-target="#modalPendientes" data-bs-toggle="modal">Solicitudes Pendientes <span class="badge bg-secondary">{{$solicitudesPendientes->count()}}</span></button>

        <!-- Modal Solicitudes Pendientes -->
        <div class="modal fade" id="modalPendientes" tabindex="-1" role="dialog" aria-labelledby="modalPendientesTitleId"
             aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Solicitudes Pendientes</h5>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped" style="font-size: 12px">
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>Monto</th>
                                    <th>Fecha</th>
                                    <th>Observaciones</th>
                                    <th>Dirección</th>
                                    <th>Acción</th>
                                </tr>
                                @foreach($solicitudesPendientes as $solicitud)
                                    <tr>
                                        <td>{{$loop->index+1}}</td>
                                        <td>{{$solicitud->cliente->full_name ?? 'N/A'}}</td>
                                        <td>{{$solicitud->moneda." ". number_format($solicitud->monto_prestamo,2)}}</td>
                                        <td>{{fecha_d_m_Y($solicitud->created_at)}}</td>
                                        <td>{{$solicitud->observaciones}}</td>
                                        <td>{{$solicitud->cliente->direccion ?? 'N/A'}}</td>
                                        <td>
                                            @if($solicitud->cliente)
                                                <a href="{{route('user.clientes.edit',$solicitud->cliente->id_enc)}}" class="btn btn-sm btn-primary">Ver</a>
                                            @else
                                                <span class="badge bg-secondary">Sin cliente</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" class="btn btn-md m-2 btn-success" data-bs-target="#modalAprobadas" data-bs-toggle="modal">Solicitudes Aprobadas (hoy) <span class="badge bg-primary">{{$solicitudesAprobadas}}</span></button>

        <!-- Modal Solicitudes Aprobadas -->
        <div class="modal fade" id="modalAprobadas" tabindex="-1" role="dialog" aria-labelledby="modalAprobadasTitleId"
             aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Solicitudes Aprobadas (hoy)</h5>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped" style="font-size: 12px">
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>Monto</th>
                                    <th>Fecha</th>
                                    <th>Observaciones</th>
                                    <th>Dirección</th>
                                    <th>Acción</th>
                                </tr>
                                @foreach($solicitudesAprobadasHoy as $solicitud)
                                    <tr>
                                        <td>{{$loop->index+1}}</td>
                                        <td>{{$solicitud->cliente->full_name ?? 'N/A'}}</td>
                                        <td>{{$solicitud->moneda." ". number_format($solicitud->monto_prestamo,2)}}</td>
                                        <td>{{fecha_d_m_Y($solicitud->created_at)}}</td>
                                        <td>{{$solicitud->observaciones}}</td>
                                        <td>{{$solicitud->cliente->direccion ?? 'N/A'}}</td>
                                        <td>
                                            @if($solicitud->cliente)
                                                <a href="{{route('user.clientes.edit',$solicitud->cliente->id_enc)}}" class="btn btn-sm btn-primary">Ver</a>
                                            @else
                                                <span class="badge bg-secondary">Sin cliente</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" class="btn btn-md m-2 btn-danger" data-bs-target="#modalRechazadas" data-bs-toggle="modal">Solicitudes Rechazadas (hoy) <span class="badge bg-primary">{{$solicitudesRechazadas}}</span></button>

        <!-- Modal Solicitudes Rechazadas -->
        <div class="modal fade" id="modalRechazadas" tabindex="-1" role="dialog" aria-labelledby="modalRechazadasTitleId"
             aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Solicitudes Rechazadas (hoy)</h5>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped" style="font-size: 12px">
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>Monto</th>
                                    <th>Fecha</th>
                                    <th>Observaciones</th>
                                    <th>Dirección</th>
                                    <th>Acción</th>
                                </tr>
                                @foreach($solicitudesRechazadasHoy as $solicitud)
                                    <tr>
                                        <td>{{$loop->index+1}}</td>
                                        <td>{{$solicitud->cliente->full_name ?? 'N/A'}}</td>
                                        <td>{{$solicitud->moneda." ". number_format($solicitud->monto_prestamo,2)}}</td>
                                        <td>{{fecha_d_m_Y($solicitud->created_at)}}</td>
                                        <td>{{$solicitud->observaciones}}</td>
                                        <td>{{$solicitud->cliente->direccion ?? 'N/A'}}</td>
                                        <td>
                                            @if($solicitud->cliente)
                                                <a href="{{route('user.clientes.edit',$solicitud->cliente->id_enc)}}" class="btn btn-sm btn-primary">Ver</a>
                                            @else
                                                <span class="badge bg-secondary">Sin cliente</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-sm" style="font-size: 12px">
                    <tr class="table-info">
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Fecha Solicitado</th>
                        <th>Monto</th>
                        <th>Forma Pago</th>
                        <th>Tasa</th>
                        <th>Plazo (meses)</th>
                        <th>Fecha Primer Pago</th>
                        <th>Observaciones</th>
                        <th>Dirección</th>
                        <th>Creado Por</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                    @foreach($prestamos as $prestamo)
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$prestamo->cliente->full_name ?? 'N/A'}}</td>
                            <td>{{fecha_d_m_Y($prestamo->created_at)}}</td>
                            <td>{{$prestamo->moneda." ".$prestamo->monto_prestamo}}</td>
                            <td>{{$prestamo->forma_pago}}</td>
                            <td>{{$prestamo->tasa_prestamo}}</td>
                            <td>{{$prestamo->plazo_pago}}</td>
                            <td>{{$prestamo->fecha_primer_pago ? fecha_d_m_Y($prestamo->fecha_primer_pago):'N-D'}}</td>
                            <td>{{$prestamo->observaciones}}</td>
                            <td>{{$prestamo->cliente->direccion ?? 'N/A'}}</td>
                            <td>{{$prestamo->userCreado->username ?? 'N/A'}} {{$prestamo->estado_aprobacion}}</td>
                            <td>
                                <span class="badge @if($prestamo->estado_aprobacion==1) bg-info @elseif($prestamo->estado_aprobacion==2) bg-success @elseif($prestamo->estado_aprobacion==3) bg-danger @endif">{{$prestamo->estado_aprobacion_at}}</span>
                            </td>
                            <td>
                                @if($prestamo->cliente)
                                    <a href="{{route('user.clientes.edit',$prestamo->cliente->id_enc)}}" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></a>
                                @else
                                    <span class="badge bg-secondary">Sin cliente</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
                {{$prestamos->appends(request()->all())->links()}}
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.getElementById('buscar').addEventListener('change',function (){
            document.getElementById('frmSolicitud').submit();
        })
    </script>
@endsection
