@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('agentes.user.clientes.index')])
    Editar Cliente Agente
@endsection

@section('content')
    @if(is_array($totalPendiente) && !empty($totalPendiente))
    <div class="row text-center">
        <div class="col-md-12">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                @foreach($totalPendiente as $pendiente)
                    <strong>El cliente posee un total de: <span style="font-size: 22px">{{number_format($pendiente['pendiente'],2)}}</span> pendiente del desembolso N° <span style="font-size: 22px">{{$pendiente['consecutivo']}}</span> </strong>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{html()->modelForm($user,'POST',route('agentes.user.clientes.update',$user->id_enc))->acceptsFiles()->open()}}
    @method('PUT')
    <hr>
    <div class="row">
        <div class="col-md-12">
            <a href="{{route('agentes.user.desembolso.create',['cliente'=>$user->id_enc])}}" class="btn btn-sm btn-success float-end">Nueva Solicitud <i class="fa fa-plus"></i></a>
        </div>
    </div>
    <br>
    @include('agentesViews.registroClientes.agenteFormClientes')
{{--    <br>--}}
{{--    <hr>--}}
{{--    <div class="row">--}}
{{--        <div class="col-md-12 bg-info p-2 text-center">--}}
{{--            <h4><b>Historial de Solicitudes de Desembolso</b></h4>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    <br>--}}
{{--    <div class="row">--}}
{{--        <div class="col-md-12">--}}
{{--            <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modelId">Nueva Solicitud de Desembolso <i class="fa fa-plus"></i></a>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    <br>--}}
{{--    <div class="col-md-12">--}}
{{--        <div class="table-responsive">--}}
{{--            <table class="table table-sm table-striped">--}}
{{--                <tr class="table-info">--}}
{{--                    <th>#</th>--}}
{{--                    <th>Fecha Creado</th>--}}
{{--                    <th>Monto</th>--}}
{{--                    <th>Plazo (Meses)</th>--}}
{{--                    <th>Forma de Pago</th>--}}
{{--                    <th>Tasa</th>--}}
{{--                    <th>Fecha Primer Pago</th>--}}
{{--                    <th>Observaciones</th>--}}
{{--                    <th>Estado</th>--}}
{{--                    <th>Acción</th>--}}
{{--                </tr>--}}
{{--                @foreach($user->solicitudes->where('created_user_id',userLogeado()->id) as $solicitud)--}}
{{--                    <tr>--}}
{{--                        <td>{{$loop->index+1}}</td>--}}
{{--                        <td>{{fecha_d_m_Y($solicitud->created_at)}}</td>--}}
{{--                        <td>{{$solicitud->moneda_solicitud." ".number_format($solicitud->monto_solicitado,2)}}</td>--}}
{{--                        <td>{{$solicitud->plazo_solicitado}}</td>--}}
{{--                        <td>{{$solicitud->forma_pago}}</td>--}}
{{--                        <td>{{$solicitud->tasa_propuesta}}</td>--}}
{{--                        <td>{{fecha_d_m_Y($solicitud->fecha_primer_pago)}}</td>--}}
{{--                        <td>{{$solicitud->observaciones}}</td>--}}
{{--                        <td>{{$solicitud->estado_solicitud}}</td>--}}
{{--                        @if($solicitud->estado==1)--}}
{{--                            <td>--}}
{{--                                <a href="javascript:void(0)" data-action="{{route('agentes.destroySolicitud',$solicitud->id_enc)}}" class="btn btn-danger btn-sm btnEliminarSolicitud">Eliminar</a>--}}
{{--                            </td>--}}
{{--                        @endif--}}
{{--                    </tr>--}}
{{--                @endforeach--}}
{{--            </table>--}}
{{--        </div>--}}
{{--    </div>--}}
    <br>
    <div class="row justify-content-center">
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">Actualizar datos del cliente <i class="fa fa-save"></i>
            </button>
        </div>
    </div>

    {{html()->closeModelForm()}}

    {{html()->form()->id('frmEliminarSolicitud')->open()}}
    @method('DELETE')
    {{html()->form()->close()}}
    <br>
    <hr>
     @foreach($prestamosCliente as $prestamo)
        <div class="row">
            @if($prestamo->estado_aprobacion == 1)
                <div class="col-md-12 text-center bg-warning p-2" style="color: white;border-radius: 5px">
                    <h2><b>Solicitud de Desembolso (PENDIENTE) <a href="#" class="btn btn-lg btn-danger float-end btnEliminarSolicitud" data-action="{{route('agentes.destroySolicitud',$prestamo->id_enc)}}">Eliminar Solicitud <i class="fa fa-trash"></i> </a> </b></h2>
                </div>
            @elseif($prestamo->estado_aprobacion == 2)
                <div class="col-md-12 text-center bg-success p-2" style="color: white;border-radius: 5px">
                    <h2><b>Solicitud de Desembolso (APROBADO) </b></h2>
                </div>
            @elseif($prestamo->estado_aprobacion == 3)
                <div class="col-md-12 text-center bg-danger p-2" style="color: white;border-radius: 5px">
                    <h2><b>Solicitud de Desembolso (RECHAZADO) </b></h2>
                </div>
            @endif
        </div>
        <br>
        <div class="row">
            <div class="col-md-12">
                <h3 class="p-1 rounded" style="background: steelblue;color: white">Datos del Negocio</h3>
            </div>
        </div>

        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-body">
                        <img id="modal-image" class="img-fluid" alt="Imagen en modal">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        @if($prestamo->negocio)
            <div class="row">
                <div class="col-md-3 my-auto">
                    <div id="carouselExample" class="carousel slide" data-pause="hover" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="0" class="active"></button>
                            <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="1"></button>
                            <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="2"></button>
                        </div>
                        <div class="carousel-inner">
                            @if(count($prestamo->negocio->documentos_negocio))
                                @foreach($prestamo->negocio->documentos_negocio as $doc)
                                    <div class="carousel-item active">
                                        <img src="{{asset('assets/img/negocios/'.$doc->url)}}" class="d-block w-100" alt="Imagen 1" data-bs-toggle="modal" data-bs-target="#exampleModal" data-img-src="{{asset('assets/img/negocios/'.$doc->url)}}">
                                    </div>
                                @endforeach
                            @else
                                <img src="{{asset('assets/img/no-photo.jpg')}}" class="d-block w-100" alt="Imagen 1" data-bs-toggle="modal" data-bs-target="#exampleModal" data-img-src="{{asset('assets/img/no-photo.jpg')}}">
                            @endif
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Anterior</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Siguiente</span>
                        </button>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="form-group">
                        <label for="nombre_negocio" class="fw-bold">Nombre:</label>
                        <input type="text" class="form-control" disabled id="nombre_negocio" value="{{$prestamo->negocio->nombre}}">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="departamento" class="fw-bold">Departamento:</label>
                                <input type="text" class="form-control" disabled id="departamento" value="{{$prestamo->negocio->departamento_municipio->departamento->nombre}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="municipio" class="fw-bold">Municipio:</label>
                                <input type="text" class="form-control" disabled id="municipio" value="{{$prestamo->negocio->departamento_municipio->nombre}}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipo_negocio" class="fw-bold">Teléfono Negocio:</label>
                                <input type="text" class="form-control" disabled id="tipo_negocio" value="{{$prestamo->negocio->telefono_negocio}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ubicacion_geografica" class="fw-bold">Ubicación geográfica:</label>
                                <input type="text" class="form-control" disabled id="ubicacion_geografica" value="{{$prestamo->punto_geografico}}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="direccion_negocio" class="fw-bold">Dirección:</label>
                        <textarea rows="3" class="form-control" disabled id="direccion_negocio">{{$prestamo->negocio->direccion}}</textarea>
                    </div>

                </div>
            </div>
        @else
            <h4 class="text-center">Cliente Asalariado</h4>
        @endif

        <br>
        <div class="row">
            <div class="col-md-12">
                <h3 class="p-1 rounded" style="background: steelblue;color: white">Datos del Fiador</h3>
            </div>
        </div>

        @if($prestamo->fiador && $prestamo->fiador->id!=3)
            <div class="row">
                <div class="col-md-3 my-auto">
                    <div class="form-group text-center">
                        <img src="{{ isset($prestamo->fiador) ? $prestamo->fiador->user_image : asset('assets/img/clientesFotos/no-photo.jpg')}}" class="img-thumbnail" alt="">
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombres" class="fw-bold">Nombres:</label>
                                <input type="text" class="form-control" disabled id="nombres" value="{{$prestamo->fiador->nombres}}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombres" class="fw-bold">Apellidos:</label>
                                <input type="text" class="form-control" disabled id="nombres" value="{{$prestamo->fiador->nombres}}">
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="estado_civil" class="fw-bold">Estado Civil:</label>
                                <input type="text" class="form-control" disabled id="estado_civil" value="{{$prestamo->fiador->estado_civil_user}}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="sexo" class="fw-bold">Sexo:</label>
                                <input type="text" class="form-control" disabled id="sexo" value="{{$prestamo->fiador->sexo==0?'Masculino':'Femenino'}}">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cedula" class="fw-bold">Cédula:</label>
                                <input type="text" class="form-control" disabled id="cedula" value="{{$prestamo->fiador->cedula}}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="departamento" class="fw-bold">Departamento:</label>
                                <input type="text" class="form-control" disabled id="departamento" value="{{$prestamo->fiador->departamento_municipio->departamento->nombre}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="municipio" class="fw-bold">Municipio:</label>
                                <input type="text" class="form-control" disabled id="municipio" value="{{$prestamo->fiador->departamento_municipio->nombre}}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="direccion" class="fw-bold">Dirección:</label>
                        <textarea rows="3" class="form-control" disabled id="direccion">{{$prestamo->fiador->direccion}}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefono1" class="fw-bold">Teléfono 1:</label>
                                <input type="text" class="form-control" disabled id="telefono1" value="{{$prestamo->fiador->telefono1}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefono2" class="fw-bold">Teléfono 2:</label>
                                <input type="text" class="form-control" disabled id="telefono2" value="{{$prestamo->fiador->telefono2}}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <h4 class="text-center">No se requirió fiador</h4>
        @endif



        <br>
        <div class="row">
            <div class="col-md-12">
                <h3 class="p-1 rounded" style="background: steelblue;color: white">Datos del Préstamo</h3>
            </div>
        </div>
{{--        <div class="row">--}}
{{--            <div class="col-md-12">--}}
{{--                <div class="row">--}}
{{--                    <div class="col-md-4">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="vendedor" class="fw-bold">Vendedor:</label>--}}
{{--                            <input type="text" class="form-control" disabled id="vendedor" value="{{$prestamo->vendedor->full_name}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-4">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="cobrador" class="fw-bold">Cobrador:</label>--}}
{{--                            <input type="text" class="form-control" disabled id="cobrador" value="{{$prestamo->agente->full_name}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="col-md-4">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="desembolsado" class="fw-bold">Desembolsado Por:</label>--}}
{{--                            <input type="text" class="form-control" disabled id="desembolsado" value="{{$prestamo->userDesembolso->full_name}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <br>--}}
{{--                <div class="row">--}}
{{--                    <div class="col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="desembolsado" class="fw-bold">Fecha Préstamo:</label>--}}
{{--                            <input type="text" class="form-control" disabled id="desembolsado" value="{{fecha_d_m_Y($prestamo->fecha_prestamo)}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="desembolsado" class="fw-bold">Fecha Desembolso:</label>--}}
{{--                            <input type="text" class="form-control" disabled id="desembolsado" value="{{fecha_d_m_Y($prestamo->fecha_desembolso)}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="desembolsado" class="fw-bold">Fecha Primer Pago:</label>--}}
{{--                            <input type="text" class="form-control" disabled id="fechaPago" value="{{fecha_d_m_Y($prestamo->fecha_primer_pago)}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="desembolsado" class="fw-bold">Fecha Último Pago:</label>--}}
{{--                            <input type="text" class="form-control" disabled id="desembolsado" value="{{$prestamo->cuotas()->orderBy('id','desc')->first() ? fecha_d_m_Y($prestamo->cuotas()->orderBy('id','desc')->first()->fecha_cuota):"-"}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <br>--}}
{{--                <div class="row">--}}
{{--                    <div class="col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="desembolsado" class="fw-bold">Monto Financiado</label>--}}
{{--                            <input type="text" class="form-control" disabled id="desembolsado" value="{{$prestamo->moneda." ". $prestamo->monto_prestamo}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="desembolsado" class="fw-bold">Forma de Pago</label>--}}
{{--                            <input type="text" class="form-control" disabled id="formaPago" value="{{$prestamo->forma_pago}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="desembolsado" class="fw-bold">Plazo del Préstamo (Meses)</label>--}}
{{--                            <input type="text" class="form-control" disabled id="desembolsado" value="{{$prestamo->plazo_pago}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="desembolsado" class="fw-bold">Tasa del Interes % (Mensual)</label>--}}
{{--                            <input type="text" class="form-control" disabled id="desembolsado" value="{{$prestamo->tasa_prestamo}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <br>--}}
{{--                <div class="row">--}}
{{--                    <div class="col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="desembolsado" class="fw-bold">Monto Total Financiado</label>--}}
{{--                            <input type="text" class="form-control" disabled id="montoTotalFinanciar" value="{{$prestamo->moneda." ". $prestamo->monto_financiado}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="desembolsado" class="fw-bold">Monto Cuota</label>--}}
{{--                            <input type="text" class="form-control" disabled id="montoCuota" value="{{$prestamo->moneda." ". $prestamo->monto_cuota}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="desembolsado" class="fw-bold">Intereses Pagar</label>--}}
{{--                            <input type="text" class="form-control" disabled id="interesPagar" value="{{$prestamo->moneda." ". $prestamo->interes_pagar}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="desembolsado" class="fw-bold">Intereses Mes</label>--}}
{{--                            <input type="text" class="form-control" disabled id="interesMes" value="{{$prestamo->moneda." ".$prestamo->interes_mes}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <br>--}}
{{--                <div class="row">--}}
{{--                    <div class="col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="desembolsado" class="fw-bold">Total Intereses Pagar:</label>--}}
{{--                            <input type="text" class="form-control" disabled id="desembolsado" value="{{$prestamo->moneda." ".$prestamo->interes_total_pagar}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="tipo_mora" class="fw-bold">Tipo Mora:</label>--}}
{{--                            <input type="text" class="form-control" disabled id="tipo_mora" value="{{$prestamo->tipo_mora==1?'Valor Fijo':'Valor Porcentual'}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="desembolsado" class="fw-bold">Dias Aplicar Mora:</label>--}}
{{--                            <input type="text" class="form-control" disabled id="dias_mora" value="{{$prestamo->dias_aplicar_mora}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="desembolsado" class="fw-bold">Monto Mora:</label>--}}
{{--                            <input type="text" class="form-control" disabled id="monto_mora" value="{{$prestamo->monto_mora}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <br>--}}
{{--                <div class="row">--}}
{{--                    <div class="col-md-12">--}}
{{--                        <div class="table-responsive">--}}
{{--                            <table class="table table-striped table-sm">--}}
{{--                                <thead>--}}
{{--                                <tr class="table-success">--}}
{{--                                    <th>#</th>--}}
{{--                                    <th>Fecha Cuota</th>--}}
{{--                                    <th>Monto cuota</th>--}}
{{--                                    <th>Monto Intereses</th>--}}
{{--                                    <th>Capital</th>--}}
{{--                                    <th>Mora</th>--}}
{{--                                    <th>Pendiente</th>--}}
{{--                                    <th>Fecha Cancelado</th>--}}
{{--                                    <th>Estado cuota</th>--}}
{{--                                    <th>Acción</th>--}}
{{--                                </tr>--}}
{{--                                </thead>--}}
{{--                                <tbody>--}}
{{--                                    <?php--}}
{{--                                    $Date = date('Y-m-d');--}}
{{--                                    $fechaAplicarMora = date('Y-m-d', strtotime($Date. ' + '.$prestamo->dias_aplicar_mora.' days'));--}}
{{--                                    ?>--}}
{{--                                @foreach($prestamo->cuotas as $cuota)--}}
{{--                                    <tr @if($cuota->fecha_cuota < $fechaAplicarMora && $cuota->estado == 1) class="table-danger" @endif>--}}
{{--                                        <td>{{$cuota->numero_cuota}}</td>--}}
{{--                                        <td>{{fecha_d_m_Y($cuota->fecha_cuota)}}</td>--}}
{{--                                        <td>{{$cuota->prestamo->moneda." ". $cuota->monto_cuota}}</td>--}}
{{--                                        <td>{{$cuota->prestamo->moneda." ". $cuota->monto_interes}}</td>--}}
{{--                                        <td>{{$cuota->prestamo->moneda." ". $cuota->monto_cuota - $cuota->monto_interes}}</td>--}}
{{--                                        <td>{{$cuota->prestamo->moneda." ". $cuota->monto_mora}}</td>--}}
{{--                                        <td>{{$cuota->prestamo->moneda." ". $cuota->monto_pendiente_cuota}}</td>--}}
{{--                                        <td>{{$cuota->fecha_pagado}}</td>--}}
{{--                                        <td>--}}
{{--                                            @if($cuota->estado == 1)--}}
{{--                                                <span class="badge bg-primary">Pendiente <i class="fa fa-clock"></i></span>--}}
{{--                                            @elseif($cuota->estado == 3)--}}
{{--                                                <span class="badge bg-success">Pagado <i class="fa fa-check"></i> </span>--}}
{{--                                            @endif--}}
{{--                                        </td>--}}
{{--                                        <td>--}}
{{--                                            @if(count($cuota->abonos))--}}
{{--                                                <a href="javascript:void(0);" data-id="{{$cuota->id_enc}}" class="btn btn-warning btn-sm btnVerAbonos"><i class="fa fa-list" data-id="{{$cuota->id_enc}}"></i></a>--}}
{{--                                            @endif--}}
{{--                                        </td>--}}
{{--                                    </tr>--}}
{{--                                @endforeach--}}
{{--                                <tr class="table-light">--}}
{{--                                    <th></th>--}}
{{--                                    <th>Total</th>--}}
{{--                                    <th>{{number_format($prestamo->suma_cuotas,2)}}</th>--}}
{{--                                    <th>{{number_format($prestamo->suma_interes,2)}}</th>--}}
{{--                                    <td colspan="6"></td>--}}
{{--                                </tr>--}}
{{--                                </tbody>--}}
{{--                            </table>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}


         {{html()->form('POST',route('agentes.user.desembolso.update',$prestamo->id_enc))->open()}}
        @method('PUT')
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="vendedor" class="fw-bold">Vendedor:</label>
                            <input type="text" class="form-control" disabled id="vendedor" value="{{$prestamo->vendedor->full_name}}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cobrador" class="fw-bold">Cobrador:</label>
                            <input type="text" class="form-control" disabled id="cobrador" value="{{$prestamo->agente->full_name}}">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="desembolsado" class="fw-bold">Desembolsado Por:</label>
                            <input type="text" class="form-control" disabled id="desembolsado" value="{{$prestamo->userDesembolso->full_name}}">
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="desembolsado" class="fw-bold">Fecha Préstamo:</label>
                            <input type="date" name="fechaPrestamo" required {{$prestamo->estado_aprobacion != 1?'disabled':null}} value="{{$prestamo->fecha_prestamo}}" class="form-control" id="fechaPrestamo">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="desembolsado" class="fw-bold">Fecha Desembolso:</label>
                            <input type="date" name="fechaDesembolso" required {{$prestamo->estado_aprobacion != 1?'disabled':null}} value="{{$prestamo->fecha_desembolso}}" class="form-control" id="fechaDesembolso">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="desembolsado" class="fw-bold">Fecha Primer Pago:</label>
                            <input type="date" class="form-control" required {{$prestamo->estado_aprobacion != 1?'disabled':null}} name="fechaPago" id="fechaPago" value="{{$prestamo->fecha_primer_pago}}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="desembolsado" class="fw-bold">Fecha Último Pago:</label>
                            <input type="text" disabled class="form-control" id="fechaUltimoPago" value="{{$prestamo->cuotas()->orderBy('id','desc')->first() ? fecha_d_m_Y($prestamo->cuotas()->orderBy('id','desc')->first()->fecha_cuota):"-"}}">
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-3">
            <label for="desembolsado" class="fw-bold">Monto Financiado</label>
                        <div class="input-group mb-3">
                <span class="input-group-text">
                    {{html()->select('moneda',['1'=>'C$','2'=>'U$'])->id('moneda')->required()}}
                </span>
                            <input type="number" name="montoFinanciar" min="1" step="0.001"
                                   class="form-control calcular" aria-label="Monto"
                                   {{$prestamo->estado_aprobacion != 1?'disabled':null}} value="{{$prestamo->monto_prestamo}}"
                                   id="montoFinanciar" required>
                        </div>

                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="desembolsado" class="fw-bold">Forma de Pago</label>
                            {{html()->select('formaPago',[''=>'* Seleccione *','1'=>'Diario','2'=>'Semanal','3'=>'Quincenal','4'=>'Mensual','5'=>'Trimestral','6'=>'Bimestral','7'=>'Catorcenal'],$prestamo->forma_pago_tipo)->class('form-control calcular')->disabled($prestamo->estado_aprobacion != 1)->id('formaPago')->required()}}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="desembolsado" class="fw-bold">Plazo del Préstamo (Meses)</label>
                            <input type="number" required value="{{$prestamo->plazo_pago}}" {{$prestamo->estado_aprobacion != 1?'disabled':null}} step="0.5" min="0" name="plazoPago" required class="form-control calcular" id="plazoPago">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="desembolsado" class="fw-bold">Tasa del Interes % (Mensual)</label>
                            <input type="number" required value="{{$prestamo->tasa_prestamo}}" {{$prestamo->estado_aprobacion != 1?'disabled':null}} min="0" step="0.01" required class="form-control calcular" name="tasaInteres" id="tasaInteres">
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="desembolsado" class="fw-bold">Monto Total Financiado</label>
                            <input type="number" value="{{$prestamo->monto_financiado}}" name="montoTotalFinanciar" style="background: lightgray" required class="form-control calcular" id="montoTotalFinanciar" readonly>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="desembolsado" class="fw-bold">Monto Cuota</label>
                            <input type="number" value="{{$prestamo->monto_cuota}}" name="montoCuota" required class="form-control" style="background: lightgray" id="montoCuota" readonly>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="desembolsado" class="fw-bold">Intereses Pagar</label>
                            <input type="text" name="interesPagar" value="{{$prestamo->interes_pagar}}" required class="form-control" style="background: lightgray" id="interesPagar" readonly>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="desembolsado" class="fw-bold">Intereses Mes</label>
                            <input type="text" name="interesMes" value="{{$prestamo->interes_mes}}" required class="form-control calcular" style="background: lightgray" id="interesMes" readonly>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="desembolsado" class="fw-bold">Total Intereses Pagar:</label>
                            <input type="text" name="totalIntereses" value="{{$prestamo->interes_total_pagar}}" required class="form-control calcular" style="background: lightgray" id="totalIntereses" readonly>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo_mora" class="fw-bold">Tipo Mora:</label>
                            <input type="text" class="form-control" {{$prestamo->estado_aprobacion != 1?'disabled':null}} id="tipo_mora" value="{{$prestamo->tipo_mora==1?'Valor Fijo':'Valor Porcentual'}}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="desembolsado" class="fw-bold">Dias Aplicar Mora:</label>
                            <input type="text" class="form-control" {{$prestamo->estado_aprobacion != 1?'disabled':null}} id="dias_mora" value="{{$prestamo->dias_aplicar_mora}}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="desembolsado" class="fw-bold">Monto Mora:</label>
                            <input type="text" class="form-control" {{$prestamo->estado_aprobacion != 1?'disabled':null}} id="monto_mora" value="{{$prestamo->monto_mora}}">
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fechaPago"><b>Tipo de Préstamo:</b></label>
                            {{html()->select('tipo_prestamo',[''=>'--- Seleccione ---','1'=>'Nuevo','2'=>'Represtamo','3'=>'Reactivación'],$prestamo->tipo_desembolso)->class('form-control')->required()->id('selTipoPrestamo')}}
                        </div>
                    </div>

                    <div class="col-md-3" id="divDiasPreferidos">
                        <div class="form-group">
                            <label for="diasPreferidos">Siguiente Dia Pago: <small><b>Para pagos quincenales</b></small></label>
                            {{html()->number('diasPago',$prestamo->dias_pago)->class('form-control')->id('diasPreferidos')->attributes(['min'=>1])}}
                        </div>
                    </div>
                    
                    <div class="col-md-3" id="divDiaSemanaPreferido" style="display: none;">
                        <div class="form-group">
                            <label for="diaSemanaPreferido" id="labelDiaSemana">Día de la Semana:</label>
                            {{html()->select('diaSemanaPreferido',[''=>'-- Seleccione --','1'=>'Lunes','2'=>'Martes','3'=>'Miércoles','4'=>'Jueves','5'=>'Viernes','6'=>'Sábado'])->class('form-control')->id('diaSemanaPreferido')}}
                        </div>
                    </div>
                </div>
                <br>
                @if($prestamo->estado_aprobacion === 1)
                <div class="row">
                    <div class="col-md-12 text-center">
                        <a href="javascript:void(0)" id="btnAmortizacion" class="btn btn-sm btn-warning w-100 p-2 bold text-uppercase"><b>Ver tabla de amortización</b></a>
                    </div>
                </div>
                <br>
                @endif
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm" id="tblAmortizacion">
                                <thead>
                                <tr class="table-success">
                                    <th>#</th>
                                    <th>Fecha Cuota</th>
                                    <th>Monto cuota</th>
                                    <th>Monto Intereses</th>
                                    <th>Capital</th>
                                    <th>Mora</th>
                                    <th>Pendiente</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($prestamo->cuotas as $cuota)
                                    <tr>
                                        <td>{{$cuota->numero_cuota}}</td>
                                        <td>{{fecha_d_m_Y($cuota->fecha_cuota)}}</td>
                                        <td>{{$cuota->prestamo->moneda." ". $cuota->monto_cuota}}</td>
                                        <td>{{$cuota->prestamo->moneda." ". $cuota->monto_interes}}</td>
                                        <td>{{$cuota->prestamo->moneda." ". $cuota->monto_cuota - $cuota->monto_interes}}</td>
                                        <td>{{$cuota->prestamo->moneda." ". $cuota->monto_mora}}</td>
                                        <td>{{$cuota->prestamo->moneda." ". $cuota->monto_pendiente_cuota}}</td>
                                    </tr>
                                @endforeach
                                <tr class="table-light">
                                    <th></th>
                                    <th>Total</th>
                                    <th>{{number_format($prestamo->suma_cuotas,2)}}</th>
                                    <th>{{number_format($prestamo->suma_interes,2)}}</th>
                                    <td colspan="6"></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @if($prestamo->estado_aprobacion === 1)
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-sm btn-success">Actualizar datos de la solicitud <i class="fa fa-refresh"></i></button>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{html()->form()->close()}}


    @endforeach
    <br>
    <div class="row">
        <div class="col-md-12">
            {{$prestamosCliente->links()}}
        </div>
    </div>

@endsection
@section('script')
    <script>

        document.getElementById('btnAmortizacion').addEventListener('click',generarTablaAmortizacion)

        // document.addEventListener('DOMContentLoaded', function () {
        //     // calcularValores()
        //     generarTablaAmortizacion()
        // })

        document.querySelectorAll('.btnEliminarDocumento').forEach((button) => {
            button.addEventListener('click', function (event) {
                Swal.fire({
                    title: '¿Está seguro?',
                    text: "¿Desea eliminar el documento seleccionado?",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#3085d6',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let url = '{{route('user.clientes.eliminarDocumento','?')}}'
                        url = url.replace('?', event.target.closest('a, .btnEliminarDocumento').dataset.id)
                        window.location = url
                    }
                })
            })
        })

        document.querySelectorAll('.btnEliminarFoto').forEach((button) => {
            button.addEventListener('click', function (event) {
                Swal.fire({
                    title: '¿Está seguro?',
                    text: "¿Desea eliminar la imagen seleccionada?",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#3085d6',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let url = '{{route('user.clientes.eliminarFoto','?')}}'
                        url = url.replace('?', event.target.closest('a, .btnEliminarFoto').dataset.id)
                        window.location = url
                    }
                })
            })
        })

        const btnEliminar = document.querySelectorAll('.btnEliminarNegocio');
        btnEliminar.forEach(button => {
            button.addEventListener("click", (event) => {

                let form = document.getElementById('frmEliminarNegocio');

                Swal.fire({
                    title: '¿Está seguro?',
                    text: "¿Desea eliminar al usuario?",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#3085d6',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.action = event.target.dataset.action;
                        form.submit();
                    }
                })
            })
        })

        const btnEliminarSolicitud = document.querySelectorAll('.btnEliminarSolicitud')
        btnEliminarSolicitud.forEach(button => {
            button.addEventListener("click", (event) => {
                Swal.fire({
                    title: '¿Está seguro?',
                    text: "Desea eliminar la solicitud",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#3085d6',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let form = document.getElementById('frmEliminarSolicitud')
                        form.action = event.target.dataset.action;
                        form.submit();
                    }
                })
            })
        })

        document.querySelectorAll('.calcular').forEach((input) => {
            input.addEventListener('change', calcularValores);
        })

 function calcularValores(){
            $("#tblAmortizacion tbody").empty()

            document.getElementById('fechaUltimoPago').value = ""

            const montoFinanciar = document.getElementById('montoFinanciar').value
            const tasaInteres = document.getElementById('tasaInteres').value
            const plazoPago = document.getElementById('plazoPago').value

            const txtMontoFinanciarTotal = document.getElementById('montoTotalFinanciar');
            const txtMontoCuota = document.getElementById('montoCuota');
            const txtInteresesPagar = document.getElementById('interesPagar');
            const txtInteresesPorMes = document.getElementById('interesMes');
            const txtTotalIntereses = document.getElementById('totalIntereses');

            let subTotalFinanciamiento = 0;
            let montoCuotaMes = 0;

            let interesCuota = 0;
            let interesesMes = 0;
            let interesesPagar = 0;
            let formapagovalor = 0;


            if(esNumeroValido(montoFinanciar) && esNumeroValido(tasaInteres) && esNumeroValido(plazoPago) && formaPago!=='')
            {
                if(parseFloat(montoFinanciar) > 0 && parseFloat(tasaInteres) > 0 && parseFloat(plazoPago) > 0)
                {
                    formapagovalor = getFormaPagoValores()

                    //let plazo = procesarNumeroDecimal(parseFloat(plazoPago));

                    let resPlazo = parseFloat(plazoPago) * formapagovalor
                    if( resPlazo - Math.floor(resPlazo) > 0 )
                        Swal.fire('Advertencia','El número de cuotas según los parámetros ingresados generan valores decimales ['+resPlazo+"]",'warning')

                    interesesMes = parseFloat(montoFinanciar) * (parseFloat(tasaInteres) / 100);
                    interesesPagar = interesesMes * parseFloat(plazoPago);
                    subTotalFinanciamiento = parseFloat(montoFinanciar) + interesesPagar;

                    let montoCuotaCalculado = subTotalFinanciamiento / resPlazo;
                    let montoCuotaRounded = parseFloat(montoCuotaCalculado.toFixed(2));
                    
                    // La última cuota se ajustará automáticamente para cuadrar el total exacto
                    interesCuota = interesesPagar / resPlazo;
                    montoCuotaMes = montoCuotaRounded;


                }
            }

            txtMontoFinanciarTotal.value = parseFloat(subTotalFinanciamiento).toFixed(2)
            txtMontoCuota.value = parseFloat(montoCuotaMes).toFixed(2)
            txtInteresesPagar.value = interesCuota.toFixed(2)
            txtInteresesPorMes.value = interesesMes.toFixed(2)
            txtTotalIntereses.value = interesesPagar.toFixed(2)
        }

        function getFormaPagoValores() {
            const formaPago = document.getElementById('formaPago').value
            let formapagovalor = 0;
            if (formaPago !== '') {
                switch (formaPago) {
                    case "1":
                        formapagovalor = 20
                        $("#divDiaSemanaPreferido").hide()
                        $("#diaSemanaPreferido").val('')
                        break
                    case "2":
                        formapagovalor = 4;
                        $("#divDiaSemanaPreferido").show()
                        $("#labelDiaSemana").html('Día de la Semana: <small><b>Para pagos semanales</b></small>')
                        break
                    case "3":
                        formapagovalor = 2;
                        $("#divDiaSemanaPreferido").hide()
                        $("#diaSemanaPreferido").val('')
                        break
                    case "4":
                        formapagovalor = 1;
                        $("#divDiaSemanaPreferido").hide()
                        $("#diaSemanaPreferido").val('')
                        break
                    case "5":
                        formapagovalor = 3;//PLAZO TOTALES DEBE DIVIDIRSE CON ESTE VALOR
                        $("#divDiaSemanaPreferido").hide()
                        $("#diaSemanaPreferido").val('')
                        break
                    case "6":
                        formapagovalor = 2;//PLAZO TOTALES DEBE DIVIDIRSE CON ESTE VALOR
                        $("#divDiaSemanaPreferido").hide()
                        $("#diaSemanaPreferido").val('')
                        break
                      case "7":
                        formapagovalor = 2;
                        $("#divDiaSemanaPreferido").show()
                        $("#labelDiaSemana").html('Día de la Semana: <small><b>Para pagos catorcenales</b></small>')
                        break
                }
            }
            return formapagovalor
        }

        function getValorFechaAumentar(){
            const formaPago = document.getElementById('formaPago').value
            let valorAumentar = 0;
            if (formaPago !== '') {
                switch (formaPago) {
                    case "1":
                        valorAumentar = 1
                        break
                    case "2":
                        valorAumentar = 7;
                        break
                    case "3":
                        valorAumentar = 15;
                        break
                    case "4":
                        valorAumentar = 30;
                        break
                    case "5":
                        valorAumentar = 90;
                        break
                    case "6":
                        valorAumentar = 60;
                        break
                    case "7":
                        valorAumentar = 14;
                        break
                }
            }
            return valorAumentar
        }

        function procesarNumeroDecimal(numero) {
            return numero;
        }

         async function generarTablaAmortizacion() {

            $("#tblAmortizacion tbody").empty()
            let diasFeriados = await getDiasFeriados();
            const formaPago = document.getElementById('formaPago').value

            const fechaPrimerPago = document.getElementById('fechaPago').value
            let montoFinanciar = document.getElementById('montoFinanciar');
            let plazoPago = document.getElementById('plazoPago');

            let montoTotalFinanciar = document.getElementById('montoTotalFinanciar');
            let montoIntereses = document.getElementById('interesPagar');
            let montoCuota = document.getElementById('montoCuota');

            let formapagovalor = getFormaPagoValores();

            if (fechaPrimerPago === '') {
                Swal.fire('Advertencia', 'Para ver la tabla de amortización debe ingresar la fecha del primer pago', 'warning')
                return
            }

            if (montoIntereses.value !== '' && montoTotalFinanciar.value !== '' && montoCuota.value !== '') {

                let montoActual = parseFloat(montoFinanciar.value)

                let sumaCuotas = 0;
                let sumaIntereses = 0;
                let sumaCapital = 0;

                let resPlazo = 0;
                if (["5", "6"].includes(formaPago))//trimestrales,bimestrales
                    resPlazo = parseFloat(plazoPago.value) / formapagovalor
                else
                    resPlazo = parseFloat(plazoPago.value) * formapagovalor

                if( resPlazo - Math.floor(resPlazo) > 0 )
                    Swal.fire('Advertencia','El número de cuotas según los parámetros ingresados generan valores decimales ['+resPlazo+"]",'warning')

                let anyo = fechaPrimerPago.split('-')[0]
                let mes = parseInt(fechaPrimerPago.split('-')[1]) - 1
                let dia = fechaPrimerPago.split('-')[2]

                const fechaInicial = new Date(anyo,mes,dia); // Fecha actual como ejemplo
                let fechaCopia = fechaInicial
                const banderaAumentar = getValorFechaAumentar(); // Número de días a agregar
                
                // Ajustar la primera fecha si cae en feriado o domingo (o sábado para diarios)
                let fechaPrimeraAjustada = new Date(anyo, mes, dia);
                if (formaPago === "1") {
                    // Para diarios, usar función especial
                    fechaPrimeraAjustada = calcularFechaFinalDiario(fechaPrimeraAjustada, 0, diasFeriados);
                } else {
                    // Para otros, usar función normal
                    fechaPrimeraAjustada = calcularFechaFinal(fechaPrimeraAjustada, 0, diasFeriados);
                }
                let fechaCuotaFormateada = fechaPrimeraAjustada.toISOString().split('T')[0];
                
                let ultimaFecha = ""
                let diasPreferidos = document.getElementById('diasPreferidos').value
                let diaEvaluar = dia
                let diaPreferidoEvaluar = diasPreferidos

                if (diasPreferidos !== '' && (diasPreferidos > 31 || diasPreferidos <= 0 || diasPreferidos % 1 > 0)) {
                    Swal.fire('Error', 'El siguiente de dia de pago es incorrecto', 'error')
                    return
                }
                let arrFechasMes = [];
                let diaSemanaPactado = null;
                let diaSemanaPreferido = document.getElementById('diaSemanaPreferido').value;
                
                for (let i=0;i <= resPlazo ;i++)
                {
                    if (i === 0) {
                        let fila = `<tr>
                            <td> ${i} </td>
                            <td> - </td>
                            <td> - </td>
                            <td> - </td>
                            <td> - </td>
                            <td> - </td>
                            <td> ${montoActual.toFixed(2)} </td>
                          </tr>`
                        $("#tblAmortizacion tbody").append(fila)
                        
                        // Guardar el día de la semana para SEMANAL y CATORCENAL
                        if (formaPago === "2" || formaPago === "7") {
                            if ((formaPago === "2" || formaPago === "7") && diaSemanaPreferido) {
                                diaSemanaPactado = parseInt(diaSemanaPreferido);
                            } else {
                                diaSemanaPactado = fechaInicial.getDay();
                            }
                        }
                    } else {

                        // Suponiendo que tienes una fecha inicial y un número de días a agregar
                        let abonoCapital = parseFloat(montoCuota.value) - parseFloat(montoIntereses.value)
                            sumaCapital+=abonoCapital
                            sumaIntereses+=parseFloat(montoIntereses.value)
                            sumaCuotas+=parseFloat(montoCuota.value)

                            let fila = `<tr>
                            <td> ${i} </td>
                            <td> ${fechaCuotaFormateada} </td>
                            <td> ${montoActual} </td>
                            <td> ${parseFloat(montoCuota.value).toFixed(2)} </td>
                            <td> ${parseFloat(montoIntereses.value).toFixed(2)} </td>
                            <td> ${abonoCapital.toFixed(2)} </td>
                            <td> ${montoActual = (montoActual - abonoCapital).toFixed(2)} </td>
                          </tr>`

                            $("#tblAmortizacion tbody").append(fila)


                        let diaCopia = parseInt(fechaCopia.toLocaleString().split('/')[0])
                        let mesCopia = parseInt(fechaCopia.toLocaleString().split('/')[1]) - 1
                        let anyoCopia = parseInt(fechaCopia.toLocaleString().split('/')[2])

                        let fechaCuota = new Date(anyoCopia,mesCopia,diaCopia);
                        ultimaFecha = fechaCuotaFormateada

                        if (formaPago === "3" && diasPreferidos > 0)
                            console.log()
                        else {
                            // Calcular la siguiente fecha sumando días
                            fechaCuota.setDate(fechaCuota.getDate() + banderaAumentar);
                            
                            // Para SEMANAL y CATORCENAL: Forzar al día de la semana pactado
                            if ((formaPago === "2" || formaPago === "7") && diaSemanaPactado !== null) {
                                let diferenciaDias = diaSemanaPactado - fechaCuota.getDay();
                                if (diferenciaDias < 0) diferenciaDias += 7;
                                if (diferenciaDias > 0 && diferenciaDias < 7) {
                                    fechaCuota.setDate(fechaCuota.getDate() + diferenciaDias);
                                }
                            }
                            
                            // Ajustar si cae en feriado o domingo
                            let fechaVerificar = fechaCuota.toISOString().split('T')[0];
                            while (fechaCuota.getDay() === 0 || diasFeriados.includes(fechaVerificar)) {
                                fechaCuota.setDate(fechaCuota.getDate() + 1);
                                fechaVerificar = fechaCuota.toISOString().split('T')[0];
                            }
                        }

                        arrFechasMes.push(mesCopia + "-" + anyoCopia)
                        if (diasPreferidos > 0 && formaPago === "3") {//solo quincenales
                            if (diaEvaluar === dia)
                                diaEvaluar = diaPreferidoEvaluar
                            else
                                diaEvaluar = dia

                            if (parseInt(dia) > parseInt(diaEvaluar) && i === 1 || arrFechasMes.filter(fecha => fecha === mesCopia + "-" + anyoCopia).length > 1)//si la primera fecha a agregar es mayor a la siguiente fecha
                                fechaCopia.setMonth(fechaCopia.getMonth() + 1, 1)

                            diaCopia = parseInt(diaEvaluar, 10)
                            mesCopia = parseInt(fechaCopia.toLocaleString().split('/')[1]) - 1
                            anyoCopia = parseInt(fechaCopia.toLocaleString().split('/')[2])
                            fechaCuota = new Date(anyoCopia, mesCopia, diaCopia);

                            do {
                                // Solo rechaza DOMINGOS (0), NO sábados (6)
                                if (fechaCuota.getDay() === 0) {
                                    fechaCuota.setDate(fechaCuota.getDate() + 1);
                                }

                                let fechaVerificar = fechaCuota.toISOString().split('T')[0];

                                if (diasFeriados.includes(fechaVerificar)) {
                                    fechaCuota.setDate(fechaCuota.getDate() + 1);
                                }
                            } while (fechaCuota.getDay() === 0 || diasFeriados.includes(fechaCuota.toISOString().split('T')[0]));

                        }

                        fechaCuotaFormateada = fechaCuota.toISOString().split('T')[0];
                        if (diasPreferidos > 0 && formaPago==="3") {//solo quincenales
                            let cadenaBusqueda = mesCopia + "-" + anyoCopia;
                            let ocurrencias = arrFechasMes.filter(fecha => fecha === cadenaBusqueda).length;
                            if (ocurrencias > 1) {
                                fechaCuota.setDate(1)
                                fechaCuota.setMonth(fechaCuota.getMonth() + 1, 1)
                            } else{
                                fechaCuota.setDate(1)
                                fechaCuota.setDate(fechaCuota.getDate() + 15)
                            }
                        }

                        let diaFormateada = parseInt(fechaCuota.toLocaleString().split('/')[0])
                        let mesFormateada = parseInt(fechaCuota.toLocaleString().split('/')[1]) - 1
                        let anyoFormateada = parseInt(fechaCuota.toLocaleString().split('/')[2])

                        fechaCopia = new Date(anyoFormateada,mesFormateada,diaFormateada);
                    }
                }
                document.getElementById('fechaUltimoPago').value = ultimaFecha

                let filaTotales = `<tr style="font-weight: bold;background: gray">
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>${sumaCuotas.toFixed(2)}</td>
                                            <td>${sumaIntereses.toFixed(2)}</td>
                                            <td>${sumaCapital.toFixed(2)}</td>
                                            <td>-</td>
                                            </tr>`
                $("#tblAmortizacion tbody").append(filaTotales)

                // openModal('modalAmortizacion')
            } else {
                Swal.fire('Advertencia♠', 'Se deben de completar todos los campos para poder ver la tabla de amortización', 'warning')
            }
        }

        function calcularFechaFinal(fechaInicial, banderaAumentar, diasFeriados) {
            let fechaCuota = new Date(fechaInicial);
            fechaCuota.setDate(fechaCuota.getDate() + banderaAumentar);

            let fechaVerificar = fechaCuota.toISOString().split('T')[0];
            // Solo rechaza DOMINGOS (0) y FERIADOS, NO sábados (6)
            while (fechaCuota.getDay() === 0 || diasFeriados.includes(fechaVerificar)) {
                fechaCuota.setDate(fechaCuota.getDate() + 1);
                fechaVerificar = fechaCuota.toISOString().split('T')[0];
            }

            return fechaCuota;
        }

        async function getDiasFeriados() {
            let {data} = await axios.get('{{route('agente.feriados.getFeriados')}}')
            return data
        }
        
        // Función para ajustar fechas en préstamos DIARIOS
        // Solo lunes a viernes son válidos (NO sábado, NO domingo, NO feriados)
        function calcularFechaFinalDiario(fechaInicial, banderaAumentar, diasFeriados) {
            let fechaCuota = new Date(fechaInicial);
            fechaCuota.setDate(fechaCuota.getDate() + banderaAumentar);

            while (true) {
                let fechaVerificar = fechaCuota.toISOString().split('T')[0];
                
                // Si es sábado o domingo, avanzar
                if (fechaCuota.getDay() === 0 || fechaCuota.getDay() === 6) {
                    fechaCuota.setDate(fechaCuota.getDate() + 1);
                    continue;
                }
                
                // Si es feriado, avanzar
                if (diasFeriados.includes(fechaVerificar)) {
                    fechaCuota.setDate(fechaCuota.getDate() + 1);
                    continue;
                }
                
                // Si llegamos aquí, es un día válido (lunes a viernes, no feriado)
                break;
            }

            return fechaCuota;
        }

        function calcularFechaFinal(fechaInicial, banderaAumentar, diasFeriados) {
            let fechaCuota = new Date(fechaInicial);
            fechaCuota.setDate(fechaCuota.getDate() + banderaAumentar);

            let fechaVerificar = fechaCuota.toISOString().split('T')[0];
            // Solo rechaza DOMINGOS (0) y FERIADOS, NO sábados (6)
            while (fechaCuota.getDay() === 0 || diasFeriados.includes(fechaVerificar)) {
                fechaCuota.setDate(fechaCuota.getDate() + 1);
                fechaVerificar = fechaCuota.toISOString().split('T')[0];
            }

            return fechaCuota;
        }

    </script>
@endsection
