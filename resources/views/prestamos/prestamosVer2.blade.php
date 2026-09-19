@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('prestamos.index')])
    Detalle de Préstamo
@endsection
@section('content')

    @if($prestamo->desembolsado!=1 && $prestamo->estado!=4)
         <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
                <strong>El préstamo sera visible al agente hasta que se marque como desembolsado</strong>
            </div>
        </div>
    </div>
    @endif
    @if($prestamo->estado==4)
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
                <strong>Desembolso Anulado ( {{$prestamo->userAnulado ? $prestamo->userAnulado->full_name:null}} )</strong>
            </div>
        </div>
    </div>
    @endif
    @if($prestamo->estado!=4)
          <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info alert-dismissible fade show text-center" role="alert">
                        @if($prestamo->desembolsado == 1)
                            <strong>Préstamo actualmente desembolsado</strong>
                        @else
                            <strong>Pendiente desembolso al cliente</strong>
                        @endif
                    </div>
                </div>
            </div>
    @endif
    <div class="row my-2">
        <div class="col-md-12 d-flex justify-content-end">
            <x-actionDropdown>
                {{-- Marcar como desembolsado --}}
                @can('Crear Desembolsos')
                    @if($prestamo->desembolsado != 1 && $prestamo->estado!=4 && $prestamo->estado!=2 && (in_array($prestamo->estado_aprobacion, [2, null])))
                        <li><a class="dropdown-item text-success" href="#" id="btnDesembolsar" data-action="{{route('prestamos.solicitudes.prestamoDesembolsado',$prestamo->id_enc)}}"><i class="fa fa-check-circle"></i> Marcar como desembolsado</a></li>
                        <li><hr class="dropdown-divider"></li>
                    @endif
                @endcan

                {{-- Editar Préstamo (Admin) --}}
                @can('Editar Desembolsos')
                    <li><a class="dropdown-item text-danger" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalEditarAdmin"><i class="fa fa-edit"></i> Editar Préstamo (Admin)</a></li>
                @endcan

                {{-- Clasificar Desembolso --}}
                @can('Clasificar Desembolsos')
                    @if(in_array($prestamo->estado,[1,3]) && $prestamo->desembolsado===1)
                        @can('Editar Desembolsos')
                            <li><hr class="dropdown-divider"></li>
                        @endcan
                        <li><a class="dropdown-item text-primary" href="javascript:void(0)" data-bs-target="#clasificar" data-bs-toggle="modal"><i class="fa fa-tags"></i> Clasificar Desembolso</a></li>
                    @endif
                @endcan
            </x-actionDropdown>

            {{-- Modal Clasificar Desembolso --}}
            @can('Clasificar Desembolsos')
            @if(in_array($prestamo->estado,[1,3]) && $prestamo->desembolsado===1)
                <div class="modal fade" id="clasificar" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                        <div class="modal-content">
                            {{html()->form('POST',route('prestamos.clasificar_prestamo',$prestamo->id_enc))->open()}}
                            <div class="modal-header">
                                <h5 class="modal-title">Clasificar Desembolso</h5>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="clasificacion"><b>Clasificar:</b></label>
                                    {{html()->text('clasificacion',$prestamo->clasificacion)->required()->class('form-control')->id('clasificacion')}}
                                </div>

                                <div class="form-group">
                                    <label for="motivo"><b>Motivo:</b></label>
                                    {{html()->textarea('motivo_clasificacion',$prestamo->motivo_clasificacion)->class('form-control')->required()->id('motivo')}}
                                </div>

                                <div class="form-group">
                                    <label for="fecha"><b>Fecha Clasificación:</b></label>
                                    {{html()->date('fecha_clasificacion',$prestamo->fecha_clasificacion)->required()->class('form-control')->id('fecha_clasificacion')}}
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success">Guardar</button>
                                @if($prestamo->clasificacion > 0)
                                    <button type="submit" value="desclasificar" name="desclasificar" class="btn btn-danger">Desclasificar</button>
                                @endif
                            </div>
                            {{html()->form()->close()}}
                        </div>
                    </div>
                </div>
            @endif
            @endcan
        </div>
    </div>
    <br>
    @if($prestamo->clasificacion!=null)
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
                        <strong>El desembolso se encuentra actualmente clasificado como: <br> <u>{{$prestamo->clasificacion}}</u> Motivo: <u>{{$prestamo->motivo_clasificacion}}</u> Fecha Clasificado: <u>{{$prestamo->fecha_clasificacion}}</u> Clasificado Por: <u>{{$prestamo->clasificadoPor ? $prestamo->clasificadoPor->full_name:'-'}}</u></strong>
                </div>
            </div>
        </div>
    @endif

    <br>
    <div class="row">
        <div class="col-md-12">
            <h3 class="p-1 rounded" style="background: steelblue;color: white">Datos del Cliente</h3>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 my-auto">
            <div class="form-group text-center">
                <img src="{{ isset($prestamo->cliente) ? $prestamo->cliente->user_image : asset('assets/img/clientesFotos/no-photo.jpg')}}" class="img-thumbnail" alt="">
            </div>
        </div>
        <div class="col-md-9">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nombres" class="fw-bold">Nombres:</label>
                        <input type="text" class="form-control" disabled id="nombres" value="{{$prestamo->cliente->nombres}}">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nombres" class="fw-bold">Apellidos:</label>
                        <input type="text" class="form-control" disabled id="nombres" value="{{$prestamo->cliente->apellidos}}">
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="estado_civil" class="fw-bold">Estado Civil:</label>
                        <input type="text" class="form-control" disabled id="estado_civil" value="{{$prestamo->cliente->estado_civil_user}}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="sexo" class="fw-bold">Sexo:</label>
                        <input type="text" class="form-control" disabled id="sexo" value="{{$prestamo->cliente->sexo==0?'Masculino':'Femenino'}}">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="cedula" class="fw-bold">Cédula:</label>
                        <input type="text" class="form-control" disabled id="cedula" value="{{$prestamo->cliente->cedula}}">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="direccion" class="fw-bold">Dirección:</label>
                <textarea rows="3" class="form-control" disabled id="direccion">{{$prestamo->cliente->direccion}}</textarea>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="telefono1" class="fw-bold">Teléfono 1:</label>
                        <input type="text" class="form-control" disabled id="telefono1" value="{{$prestamo->cliente->telefono1}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="telefono2" class="fw-bold">Teléfono 2:</label>
                        <input type="text" class="form-control" disabled id="telefono2" value="{{$prestamo->cliente->telefono2}}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="departamento" class="fw-bold">Departamento:</label>
                        <input type="text" class="form-control" disabled id="departamento" value="{{$prestamo->cliente->departamento_municipio->departamento->nombre}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="municipio" class="fw-bold">Municipio:</label>
                        <input type="text" class="form-control" disabled id="municipio" value="{{$prestamo->cliente->departamento_municipio->departamento->nombre}}">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <h3 class="p-1 rounded" style="background: steelblue;color: white">Datos del Negocio</h3>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-body">
                    <img id="modal-image" class="img-fluid" alt="Imagen en modal">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
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
                                <img src="{{asset('assets/img/negocios/'.$doc->url)}}" class="d-block w-100 img-thumbnail" alt="Imagen 1" data-bs-toggle="modal" data-bs-target="#exampleModal" data-img-src="{{asset('assets/img/negocios/'.$doc->url)}}">
                            </div>
                        @endforeach
                    @else
                        <img src="{{asset('assets/img/no-photo.jpg')}}" class="d-block m-auto img-thumbnail" alt="Imagen 1" data-bs-toggle="modal" data-bs-target="#exampleModal" data-img-src="{{asset('assets/img/no-photo.jpg')}}">
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

{{--    sin asignar--}}
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
                        <input type="text" class="form-control" disabled id="desembolsado" value="{{isset($prestamo->userDesembolso) ? $prestamo->userDesembolso->full_name:'N-D'}}">
                    </div>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="desembolsado" class="fw-bold">Fecha Préstamo:</label>
                        <input type="text" class="form-control" disabled id="desembolsado" value="{{fecha_d_m_Y($prestamo->fecha_prestamo)}}">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="desembolsado" class="fw-bold">Fecha Desembolso:</label>
                        <input type="text" class="form-control" disabled id="desembolsado" value="{{fecha_d_m_Y($prestamo->fecha_desembolso)}}">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="desembolsado" class="fw-bold">Fecha Primer Pago:</label>
                        <input type="text" class="form-control" disabled id="desembolsado" value="{{fecha_d_m_Y($prestamo->fecha_primer_pago)}}">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="desembolsado" class="fw-bold">Fecha Último Pago:</label>
                        <input type="text" class="form-control" disabled id="desembolsado" value="{{$prestamo->cuotas()->orderBy('id','desc')->first() ? fecha_d_m_Y($prestamo->cuotas()->orderBy('id','desc')->first()->fecha_cuota):"-"}}">
                    </div>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="desembolsado" class="fw-bold">Monto Financiado</label>
                        <input type="text" class="form-control" disabled id="desembolsado" value="{{$prestamo->moneda." ". $prestamo->monto_prestamo}}">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="desembolsado" class="fw-bold">Forma de Pago</label>
                        <input type="text" class="form-control" disabled id="desembolsado" value="{{$prestamo->forma_pago}}">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="desembolsado" class="fw-bold">Plazo del Préstamo (Meses)</label>
                        <input type="text" class="form-control" disabled id="desembolsado" value="{{$prestamo->plazo_pago}}">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="desembolsado" class="fw-bold">Tasa del Interes % (Mensual)</label>
                        <input type="text" class="form-control" disabled id="desembolsado" value="{{$prestamo->tasa_prestamo}}">
                    </div>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="desembolsado" class="fw-bold">Monto Total Financiado</label>
                        <input type="text" class="form-control" disabled id="desembolsado" value="{{$prestamo->moneda." ". $prestamo->monto_financiado}}">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="desembolsado" class="fw-bold">Monto Cuota</label>
                        <input type="text" class="form-control" disabled id="desembolsado" value="{{$prestamo->moneda." ". $prestamo->monto_cuota}}">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="desembolsado" class="fw-bold">Intereses Pagar</label>
                        <input type="text" class="form-control" disabled id="desembolsado" value="{{$prestamo->moneda." ". $prestamo->interes_pagar}}">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="desembolsado" class="fw-bold">Intereses Mes</label>
                        <input type="text" class="form-control" disabled id="desembolsado" value="{{$prestamo->moneda." ".$prestamo->interes_mes}}">
                    </div>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="desembolsado" class="fw-bold">Total Intereses Pagar:</label>
                        <input type="text" class="form-control" disabled id="desembolsado" value="{{$prestamo->moneda." ".$prestamo->interes_total_pagar}}">
                    </div>
                </div>

{{--                <div class="col-md-3">--}}
{{--                    <div class="form-group">--}}
{{--                        <label for="tipo_mora" class="fw-bold">Tipo Mora:</label>--}}
{{--                        <input type="text" class="form-control" disabled id="tipo_mora" value="{{$prestamo->tipo_mora==1?'Valor Fijo':'Valor Porcentual'}}">--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <div class="col-md-3">--}}
{{--                    <div class="form-group">--}}
{{--                        <label for="desembolsado" class="fw-bold">Dias Aplicar Mora:</label>--}}
{{--                        <input type="text" class="form-control" disabled id="dias_mora" value="{{$prestamo->dias_aplicar_mora}}">--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <div class="col-md-3">--}}
{{--                    <div class="form-group">--}}
{{--                        <label for="desembolsado" class="fw-bold">Monto Mora:</label>--}}
{{--                        <input type="text" class="form-control" disabled id="monto_mora" value="{{$prestamo->monto_mora}}">--}}
{{--                    </div>--}}
{{--                </div>--}}
            </div>
            <br>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tipo_desembolso" class="fw-bold">Tipo Desembolso:</label>
                        <input type="text" class="form-control" disabled id="tipo_desembolso" value="{{$prestamo->tipo_prestamo}}">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tipo_destino" class="fw-bold">Tipo Destino: </label>
                        <input type="text" class="form-control" disabled id="tipo_destino" value="{{$prestamo->tipo_destino_prestamo}}">
                    </div>
                </div>
                {{-- Mostrar Día Preferido según Forma de Pago --}}
                @if(in_array($prestamo->forma_pago_tipo, [2, 7])) {{-- Semanal o Catorcenal --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="diaSemanaPactado" class="fw-bold">Día de Pago Pactado:</label>
                            @php
                                $diasSemana = ['1'=>'Lunes','2'=>'Martes','3'=>'Miércoles','4'=>'Jueves','5'=>'Viernes','6'=>'Sábado','0'=>'Domingo'];
                                $diaNombre = isset($diasSemana[$prestamo->dia_semana_preferido]) ? $diasSemana[$prestamo->dia_semana_preferido] : 'N/D';
                            @endphp
                            <input type="text" class="form-control" disabled value="{{$diaNombre}}">
                        </div>
                    </div>
                @elseif($prestamo->forma_pago_tipo == 3) {{-- Quincenal --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="diasPreferidos" class="fw-bold">Siguiente Dia Pago: <small><b>(Quincenal)</b></small></label>
                            <input type="text" class="form-control" disabled value="{{$prestamo->dias_pago}}">
                        </div>
                    </div>
                @endif
            </div>
            <br>
            @if($prestamo->estado!=4)
            <div class="row">
                @can('Reasignar Vendedor')
                    <div class="col-md-6 text-end">
                        <a href="#" class="btn btn-sm btn-primary btnModificarUsuario" data-bs-target="#modalUsuario" data-bs-toggle="modal" data-tipo="vendedor">Reasignar Vendedor <i class="fa fa-user"></i></a>
                    </div>
                @endcan
                @can('Reasignar Cobrador')
                    <div class="col-md-6">
                        <a href="#" class="btn btn-sm btn-primary btnModificarUsuario" data-bs-target="#modalUsuarioCobrador" data-bs-toggle="modal" data-tipo="cobrador">Reasignar Cobrador <i class="fa fa-user"></i></a>
                    </div>
                @endcan
            </div>
            @endif
            <br>

            @php
                $totalDiasAtrasoVista = 0;
                $totalCuotasVista = $prestamo->cuotas->count();
                foreach ($prestamo->cuotas as $cuotaAtraso) {
                    $fechaPlanCuotaVista = \Carbon\Carbon::parse($cuotaAtraso->fecha_cuota);
                    if ($cuotaAtraso->estado == 3) {
                        $ultimoAbonoVista = \App\Models\prestamoCuotaAbonoModel::where('prestamo_cuota_id', $cuotaAtraso->id)
                            ->where('estado', 1)
                            ->orderBy('created_at', 'desc')
                            ->first();
                        if ($ultimoAbonoVista) {
                            $fechaPagoRealVista = \Carbon\Carbon::parse($ultimoAbonoVista->created_at);
                            $diasAtrasoVista = $fechaPlanCuotaVista->diffInDays($fechaPagoRealVista, false);
                            if ($diasAtrasoVista > 0) $totalDiasAtrasoVista += $diasAtrasoVista;
                        }
                    } elseif (in_array($cuotaAtraso->estado, [1, 2]) && $fechaPlanCuotaVista->isPast()) {
                        $diasAtrasoVista = $fechaPlanCuotaVista->diffInDays(\Carbon\Carbon::now(), false);
                        if ($diasAtrasoVista > 0) $totalDiasAtrasoVista += $diasAtrasoVista;
                    }
                }
                $promedioDiasAtrasoVista = $totalCuotasVista > 0 ? round($totalDiasAtrasoVista / $totalCuotasVista, 2) : 0;
            @endphp

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="fw-bold">Promedio Días de Atraso:</label>
                        <div class="input-group">
                            <input type="text" class="form-control fw-bold {{ $promedioDiasAtrasoVista > 0 ? 'text-danger' : 'text-success' }}" disabled
                                   value="{{ $promedioDiasAtrasoVista }} días">
                            <span class="input-group-text {{ $promedioDiasAtrasoVista > 0 ? 'bg-danger text-white' : 'bg-success text-white' }}">
                                <i class="fas fa-{{ $promedioDiasAtrasoVista > 0 ? 'exclamation-triangle' : 'check-circle' }}"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                            <tr class="table-success">
                                <th>#</th>
                                <th>Fecha Cuota</th>
                                <th>Monto cuota</th>
                                <th>Monto Intereses</th>
                                <th>Capital</th>
                                <th>Mora</th>
                                <th>Pendiente</th>
                                <th>Fecha Cancelado</th>
                                <th>Estado cuota</th>
                                <th>Acción</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $Date = date('Y-m-d');
                            $fechaAplicarMora = date('Y-m-d', strtotime($Date. ' + '.$prestamo->dias_aplicar_mora.' days'));
                            ?>
                            @foreach($prestamo->cuotas as $cuota)
                                <tr @if($cuota->fecha_cuota < $fechaAplicarMora && $cuota->estado == 1) class="table-danger" @endif>
                                    <td>{{$cuota->numero_cuota}}</td>
                                    <td>
                                        @if($prestamo->estado === 1)
                                            @can('Modificar Fechas de Cuotas')
                                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#editarFechasModal{{encode($cuota->id)}}">{{fecha_d_m_Y($cuota->fecha_cuota)}} <i class="fa fa-pencil"></i></a
                                            @else
                                                {{fecha_d_m_Y($cuota->fecha_cuota)}}
                                            @endcan
                                        @else
                                            {{fecha_d_m_Y($cuota->fecha_cuota)}}
                                        @endif
                                    </td>
                                    <td>{{$cuota->prestamo->moneda." ". $cuota->monto_cuota}}</td>
                                    <td>{{$cuota->prestamo->moneda." ". $cuota->monto_interes}}</td>
                                    <td>{{$cuota->prestamo->moneda." ". $cuota->monto_cuota - $cuota->monto_interes}}</td>
                                    <td>-</td>
                                    <td>{{$cuota->prestamo->moneda." ". $cuota->monto_pendiente_cuota}}</td>
                                    <td>{{$cuota->fecha_pagado}}</td>
                                    <td>
                                        @if($cuota->estado == 1)
                                            <span class="badge bg-primary">Pendiente <i class="fa fa-clock"></i></span>
                                        @elseif($cuota->estado == 3)
                                            <span class="badge bg-success">Pagado <i class="fa fa-check"></i> </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(count($cuota->abonos->where('estado',1)))
                                            <a href="javascript:void(0);" data-id="{{$cuota->id_enc}}" class="btn btn-warning btn-sm btnVerAbonos"><i class="fa fa-list" data-id="{{$cuota->id_enc}}"></i></a>
                                        @endif
                                    </td>
                                </tr>
                                @if($prestamo->estado===1)
                                    @can('Modificar Fechas de Cuotas')
                                        <div class="modal fade" id="editarFechasModal{{encode($cuota->id)}}"
                                             tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
                                             aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                                                <div class="modal-content">
                                                    {{html()->form('POST',route('prestamos.editarFechaCuota',encode($cuota->id)))->open()}}
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Editar Fechas</h5>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="fecha_cuota">Fecha Cuota</label>
                                                            <input name="fecha_cuota" type="date" class="form-control"
                                                                   required value="{{$cuota->fecha_cuota}}">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger"
                                                                data-bs-dismiss="modal">Cancelar
                                                        </button>
                                                        <button type="submit" class="btn btn-success">Cambiar</button>
                                                    </div>
                                                    {{html()->form()->close()}}
                                                </div>
                                            </div>
                                        </div>
                                    @endcan
                                @endif
                            @endforeach
                            <tr class="table-light">
                                <th></th>
                                <th>Total</th>
                                <th>{{number_format($prestamo->suma_cuotas,2)}}</th>
                                <th>{{number_format($prestamo->suma_interes,2)}}</th>
                                <th>{{number_format($prestamo->suma_capital,2)}}</th>
                                <th></th>
                                <th>{{number_format($prestamo->pendiente_abono,2)}}</th>
                                <td colspan="5"></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('prestamos.modals.modalVerAbonos')
    @include('prestamos.modals.modalCambiarUsuarioPrestamo')

    @can('Editar Desembolsos')
    <div class="modal fade" id="modalEditarAdmin" tabindex="-1" role="dialog" aria-labelledby="modelTitleAdmin" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document" style="margin-top:70px; margin-bottom:10px;">
            <div class="modal-content" style="height:calc(100vh - 90px); display:flex; flex-direction:column;">
                <div class="modal-header bg-danger text-white" style="flex-shrink:0;">
                    <h5 class="modal-title">Editar Información del Préstamo (MODO ADMINISTRADOR)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                {{html()->form('POST',route('prestamos.update',$prestamo->id_enc))->id('frmEditarAdmin')->style('flex:1; display:flex; flex-direction:column; overflow:hidden; min-height:0;')->open()}}
                @method('PUT')
                <div class="modal-body" style="flex:1; overflow-y:auto; overflow-x:hidden; min-height:0;">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="fw-bold">Fecha Préstamo:</label>
                                <input type="date" name="fechaPrestamo" required value="{{$prestamo->fecha_prestamo}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="fw-bold">Fecha Desembolso:</label>
                                <input type="date" name="fechaDesembolso" required value="{{$prestamo->fecha_desembolso}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="fw-bold">Fecha Primer Pago:</label>
                                <input type="date" class="form-control" required name="fechaPago" id="fechaPagoAdmin" value="{{$prestamo->fecha_primer_pago}}">
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="fw-bold">Monto Financiado</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text">
                                    {{html()->select('moneda',['1'=>'C$','2'=>'U$'],$prestamo->moneda_prestamo)->id('monedaAdmin')->required()}}
                                </span>
                                <input type="number" name="montoFinanciar" min="1" step="0.01" class="form-control calcularAdmin" value="{{$prestamo->monto_prestamo}}" id="montoFinanciarAdmin" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="fw-bold">Forma de Pago</label>
                                {{html()->select('formaPago',[''=>'* Seleccione *','1'=>'Diario','2'=>'Semanal','3'=>'Quincenal','4'=>'Mensual','5'=>'Trimestral','6'=>'Bimestral','7'=>'Catorcenal'],$prestamo->forma_pago_tipo)->class('form-control calcularAdmin')->id('formaPagoAdmin')->required()}}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="fw-bold">Plazo (Meses)</label>
                                <input type="number" required value="{{$prestamo->plazo_pago}}" step="0.1" min="0" name="plazoPago" class="form-control calcularAdmin" id="plazoPagoAdmin">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="fw-bold">Tasa Interés % (Mensual)</label>
                                <input type="number" required value="{{$prestamo->tasa_prestamo}}" min="0" step="0.01" class="form-control calcularAdmin" name="tasaInteres" id="tasaInteresAdmin">
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="fw-bold">Monto Total Financiado</label>
                                <input type="number" step="0.01" value="{{$prestamo->monto_financiado}}" name="montoTotalFinanciar" style="background: lightgray" required class="form-control" id="montoTotalFinanciarAdmin" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="fw-bold">Monto Cuota</label>
                                <input type="number" step="0.01" value="{{$prestamo->monto_cuota}}" name="montoCuota" required class="form-control" style="background: lightgray" id="montoCuotaAdmin" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="fw-bold">Intereses Pagar</label>
                                <input type="number" step="0.01" name="interesPagar" value="{{$prestamo->interes_pagar}}" required class="form-control" style="background: lightgray" id="interesPagarAdmin" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="fw-bold">Intereses Mes</label>
                                <input type="number" step="0.01" name="interesMes" value="{{$prestamo->interes_mes}}" required class="form-control" style="background: lightgray" id="interesMesAdmin" readonly>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="fw-bold">Total Intereses Pagar:</label>
                                <input type="number" step="0.01" name="totalIntereses" value="{{$prestamo->interes_total_pagar}}" required class="form-control" style="background: lightgray" id="totalInteresesAdmin" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="fw-bold">Desembolsado Por:</label>
                                {{html()->select('desembolsado_por',$admins,encode($prestamo->user_desembolso))->class('form-control')->required()}}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="fw-bold">Tipo de Préstamo:</label>
                                {{html()->select('tipo_prestamo',tipoprestamos(),$prestamo->tipo_desembolso)->class('form-control')->required()}}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="fw-bold">Tipo Destino:</label>
                                {{html()->select('tipo_destino',[''=>'-- Seleccione --'] + destinoPrestamo(),$prestamo->tipo_destino)->class('form-control')->required()}}
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="fw-bold">Días Aplicar Mora:</label>
                                <input type="number" name="dias_mora" value="{{$prestamo->dias_aplicar_mora}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="fw-bold">Tipo Mora:</label>
                                {{html()->select('moraTipo',['1'=>'Valor Fijo','2'=>'Valor Porcentual'],$prestamo->tipo_mora)->class('form-control')}}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="fw-bold">Monto Mora:</label>
                                <input type="number" name="monto_mora" step="0.01" value="{{$prestamo->monto_mora}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="fw-bold">Siguiente Día Pago:</label>
                                <small class="text-muted d-block">Para pagos quincenales</small>
                                {{html()->number('diasPago',$prestamo->dias_pago)->class('form-control')->attributes(['min'=>1])}}
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="fw-bold">Día Preferido (Quincenal):</label>
                                <small class="text-muted d-block">Día del mes preferido (1-31)</small>
                                {{html()->number('dia_pago_preferido',$prestamo->dia_pago_preferido)->class('form-control')->attributes(['min'=>1,'max'=>31])}}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="fw-bold">Día Semana Preferido:</label>
                                <small class="text-muted d-block">Para pagos semanales/catorcenales</small>
                                {{html()->select('diaSemanaPreferido',[''=>'-- Ninguno --','1'=>'Lunes','2'=>'Martes','3'=>'Miércoles','4'=>'Jueves','5'=>'Viernes','6'=>'Sábado'],$prestamo->dia_semana_preferido)->class('form-control')}}
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="fw-bold">Observaciones:</label>
                                <textarea name="comentarios" class="form-control" rows="2">{{$prestamo->observaciones}}</textarea>
                            </div>
                        </div>
                    </div>
                    <br>
                    @if($prestamo->cuotas()->has('abonos')->count())
                        <div class="alert alert-danger">
                            <i class="fa fa-exclamation-circle"></i> <b>Advertencia:</b> Este préstamo ya posee abonos registrados. 
                            Al guardar, los datos del préstamo se actualizarán pero <b>la tabla de amortización NO se regenerará</b>. 
                            Si necesita ajustar fechas o montos de cuotas, deberá hacerlo manualmente.
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i> <b>Atención:</b> Al guardar estos cambios, la tabla de amortización será regenerada completamente.
                        </div>
                    @endif
                </div>
                <div class="modal-footer" style="flex-shrink:0;">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
                {{html()->form()->close()}}
            </div>
        </div>
    </div>
    @endcan
@endsection

@section('script')
    <script>
        document.querySelectorAll('#carouselExample .carousel-item img').forEach(function(item) {
            item.addEventListener('click', function() {
                const imgSrc = this.getAttribute('data-img-src');
                document.getElementById('modal-image').src = imgSrc;
            });
        });

        document.querySelectorAll('.btnCuota').forEach((button)=>{
            button.addEventListener('click',function (event){
                event.target.closest('form').submit()
            })
        })

        document.querySelectorAll('.btnVerAbonos').forEach((button)=>{
            button.addEventListener('click',function (event){
                let id = ""

                if (event.target.classList.contains('btnVerAbonos'))
                    id = event.target.dataset.id
                else
                    id = event.target.closest('a', '.btnVerAbonos').dataset.id

                getListAbonos(id)
            })
        })

        async function getListAbonos(id){
            let ruta = '{{route('prestamos.abono.getListAbonos','?')}}'
            ruta = ruta.replace('?',id)
            let {data} = await axios.get(ruta)
            if(data.length) {
                $("#tblAbonos tbody").empty()

                for (let key in data) {
                    let fila =
                        `<tr>
                            <td>${parseInt(key)+1}</td>
                            <td>${data[key].abono_tipo}</td>
                            <td>${data[key].monto_abono}</td>
                            <td>${data[key].fecha_abono}</td>
                        </tr>`

                    $("#tblAbonos tbody").append(fila)

                }
                openModal('modalAbonos')
            }
        }


        function closeModal(modalId){

            const modalElement = document.getElementById(modalId);
            if (!modalElement) return;
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.hide();
        }

        function openModal(modalId){
            const modalElement = document.getElementById(modalId);
            if (!modalElement) return;
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement, {
                keyboard: false
            });
            modal.show();
        }










        //REPRESTAMO
        document.addEventListener('DOMContentLoaded', function () {
            getAgentes()
            getVendedores()
        })

        document.querySelectorAll('.rbTipoMonto').forEach((radio) => {
            radio.addEventListener('change', function (e) {
                if (e.target.value === '1'){
                    document.getElementById('montoFinanciar').value = document.getElementById('suma_cuotas').value
                }
                else if (e.target.value === '2'){
                    document.getElementById('montoFinanciar').value = document.getElementById('suma_capital').value
                }
                $("#tblAmortizacion tbody").empty()
                calcularValores()
            })
        })


        //document.getElementById('btnAmortizacion').addEventListener('click',generarTablaAmortizacion)

        async function getAgentes() {
            $("#selectAgente").empty();
            let url = '{{route('user.agentes.getAgentes')}}'

            const {data} = await axios.get(url)
            for (let key in data) {
                let opcion = `<option value="${key}"> ${data[key]} </option>`

                $("#selectAgente").append(opcion)
            }
        }

        async function getVendedores() {
            $("#selectVendedor").empty();
            let url = '{{route('user.getListAdministrativos')}}'
            const {data} = await axios.get(url)
            for (let key in data) {
                let opcion = `<option value="${key}"> ${data[key]} </option>`

                $("#selectVendedor").append(opcion)
                $("#selectDesembolso").append(opcion)
            }
        }

        document.querySelectorAll('.calcular').forEach((input) => {
            input.addEventListener('change', calcularValores);
        })

        function calcularValores(){

            document.getElementById('fechaUltimoPago').value = ""

            const formaPago = document.getElementById('formaPago').value
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

            if(esNumeroValido(montoFinanciar) && esNumeroValido(tasaInteres) && esNumeroValido(plazoPago) && formaPago !== '')
            {
                if(parseFloat(montoFinanciar) > 0 && parseFloat(tasaInteres) > 0 && parseFloat(plazoPago) > 0)
                {
                    formapagovalor = getFormaPagoValores()

                    let resPlazo = 0;
                    if (["5", "6"].includes(formaPago)) // trimestrales, bimestrales
                        resPlazo = parseFloat(plazoPago) / formapagovalor
                    else
                        resPlazo = parseFloat(plazoPago) * formapagovalor

                    if( resPlazo - Math.floor(resPlazo) > 0 )
                        Swal.fire('Advertencia','El número de cuotas según los parámetros ingresados generan valores decimales ['+resPlazo+"]",'warning')

                    interesesMes = parseFloat(montoFinanciar) * (parseFloat(tasaInteres)/100)
                    interesesPagar = interesesMes * parseFloat(plazoPago)
                    interesCuota = interesesPagar / resPlazo

                    subTotalFinanciamiento = parseFloat(montoFinanciar) + interesesPagar
                    montoCuotaMes = subTotalFinanciamiento / resPlazo
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
                    case "1": formapagovalor = 20; break  // Diario
                    case "2": formapagovalor = 4;  break  // Semanal
                    case "3": formapagovalor = 2;  break  // Quincenal
                    case "4": formapagovalor = 1;  break  // Mensual
                    case "5": formapagovalor = 3;  break  // Trimestral
                    case "6": formapagovalor = 2;  break  // Bimestral
                    case "7": formapagovalor = 2;  break  // Catorcenal
                }
            }
            return formapagovalor
        }

        function getValorFechaAumentar(){
            const formaPago = document.getElementById('formaPago').value
            let valorAumentar = 0;
            if (formaPago !== '') {
                switch (formaPago) {
                    case "1": valorAumentar = 1;  break  // Diario
                    case "2": valorAumentar = 7;  break  // Semanal
                    case "3": valorAumentar = 15; break  // Quincenal
                    case "4": valorAumentar = 30; break  // Mensual
                    case "5": valorAumentar = 90; break  // Trimestral
                    case "6": valorAumentar = 60; break  // Bimestral
                    case "7": valorAumentar = 14; break  // Catorcenal
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

                let plazo = procesarNumeroDecimal(parseFloat(plazoPago.value));

                let resPlazo = 0;
                if (["5", "6"].includes(formaPago))
                    resPlazo = parseFloat(plazoPago.value) / formapagovalor
                else
                    resPlazo = parseFloat(plazoPago.value) * formapagovalor

                if (resPlazo - Math.floor(resPlazo) > 0)
                    Swal.fire('Advertencia','El número de cuotas según los parámetros ingresados generan valores decimales ['+resPlazo+"]",'warning')

                let anyo = fechaPrimerPago.split('-')[0]
                let mes = parseInt(fechaPrimerPago.split('-')[1]) - 1
                let dia = fechaPrimerPago.split('-')[2]

                const fechaInicial = new Date(anyo, mes, dia);
                let fechaCopia = fechaInicial
                const banderaAumentar = getValorFechaAumentar();

                // Ajustar la primera fecha si cae en feriado o domingo
                let fechaPrimeraAjustada = new Date(anyo, mes, dia);
                if (formaPago === "1") {
                    fechaPrimeraAjustada = calcularFechaFinalDiario(fechaPrimeraAjustada, 0, diasFeriados);
                } else {
                    fechaPrimeraAjustada = calcularFechaFinal(fechaPrimeraAjustada, 0, diasFeriados);
                }
                let fechaCuotaFormateada = fechaPrimeraAjustada.toISOString().split('T')[0];

                let ultimaFecha = ""
                let diasPreferidos = document.getElementById('diasPreferidos') ? document.getElementById('diasPreferidos').value : ''
                let diaEvaluar = dia
                let diaPreferidoEvaluar = diasPreferidos

                let arrFechasMes = [];
                let diaSemanaPactado = null;
                let diaSemanaPreferido = document.getElementById('diaSemanaPreferido') ? document.getElementById('diaSemanaPreferido').value : ''

                for (let i = 0; i <= resPlazo; i++) {
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

                        if (formaPago === "2" || formaPago === "7") {
                            diaSemanaPactado = diaSemanaPreferido ? parseInt(diaSemanaPreferido) : fechaPrimeraAjustada.getDay();
                        }
                    } else {

                        let montoCuotaRow = parseFloat(montoCuota.value);
                        let abonoCapital = 0;
                        let interesesRow = parseFloat(montoIntereses.value);

                        // Ajuste Senior: Si es la última cuota, el abono a capital debe ser el saldo restante
                        if (i === Math.floor(resPlazo)) {
                            abonoCapital = montoActual;
                            montoCuotaRow = abonoCapital + interesesRow;
                        } else {
                            abonoCapital = parseFloat(montoCuota.value) - interesesRow;
                        }

                        sumaCapital += abonoCapital
                        sumaIntereses += interesesRow
                        sumaCuotas += montoCuotaRow

                        let saldoFinalRow = (montoActual - abonoCapital).toFixed(2);

                        let fila = `<tr>
                            <td> ${i} </td>
                            <td> ${fechaCuotaFormateada} </td>
                            <td> ${montoCuotaRow.toFixed(2)} </td>
                            <td> ${interesesRow.toFixed(2)} </td>
                            <td> ${abonoCapital.toFixed(2)} </td>
                            <td> 0.00 </td>
                            <td> ${saldoFinalRow} </td>
                          </tr>`

                        montoActual = parseFloat(saldoFinalRow);
                        $("#tblAmortizacion tbody").append(fila)

                        let diaCopia = parseInt(fechaCopia.toLocaleString().split('/')[0])
                        let mesCopia = parseInt(fechaCopia.toLocaleString().split('/')[1]) - 1
                        let anyoCopia = parseInt(fechaCopia.toLocaleString().split('/')[2])

                        let fechaCuota = new Date(anyoCopia, mesCopia, diaCopia);
                        ultimaFecha = fechaCuotaFormateada

                        if (formaPago === "3" && diasPreferidos > 0) {
                            // QUINCENAL CON DÍA PREFERIDO
                            if (diaEvaluar === dia)
                                diaEvaluar = diaPreferidoEvaluar
                            else
                                diaEvaluar = dia

                            if (parseInt(dia) > parseInt(diaEvaluar) && i === 1 || arrFechasMes.filter(f => f === mesCopia + "-" + anyoCopia).length > 1)
                                fechaCopia.setMonth(fechaCopia.getMonth() + 1, 1)

                            diaCopia = parseInt(diaEvaluar, 10)
                            mesCopia = parseInt(fechaCopia.toLocaleString().split('/')[1]) - 1
                            anyoCopia = parseInt(fechaCopia.toLocaleString().split('/')[2])
                            fechaCuota = new Date(anyoCopia, mesCopia, diaCopia);

                            do {
                                if (fechaCuota.getDay() === 0) fechaCuota.setDate(fechaCuota.getDate() + 1);
                                let fv = fechaCuota.toISOString().split('T')[0];
                                if (diasFeriados.includes(fv)) fechaCuota.setDate(fechaCuota.getDate() + 1);
                            } while (fechaCuota.getDay() === 0 || diasFeriados.includes(fechaCuota.toISOString().split('T')[0]));

                        } else {
                            // OTROS TIPOS
                            fechaCuota.setDate(fechaCuota.getDate() + banderaAumentar);

                            if ((formaPago === "2" || formaPago === "7") && diaSemanaPactado !== null) {
                                let dif = diaSemanaPactado - fechaCuota.getDay();
                                if (dif < 0) dif += 7;
                                if (dif > 0 && dif < 7) fechaCuota.setDate(fechaCuota.getDate() + dif);
                            }

                            if (formaPago === "1") {
                                fechaCuota = calcularFechaFinalDiario(fechaCuota, 0, diasFeriados);
                            } else {
                                let fv = fechaCuota.toISOString().split('T')[0];
                                while (fechaCuota.getDay() === 0 || diasFeriados.includes(fv)) {
                                    fechaCuota.setDate(fechaCuota.getDate() + 1);
                                    fv = fechaCuota.toISOString().split('T')[0];
                                }
                            }
                        }

                        arrFechasMes.push(mesCopia + "-" + anyoCopia)
                        fechaCuotaFormateada = fechaCuota.toISOString().split('T')[0];

                        let diaF = parseInt(fechaCuota.toLocaleString().split('/')[0])
                        let mesF = parseInt(fechaCuota.toLocaleString().split('/')[1]) - 1
                        let anyoF = parseInt(fechaCuota.toLocaleString().split('/')[2])
                        fechaCopia = new Date(anyoF, mesF, diaF);
                    }
                }
                document.getElementById('fechaUltimoPago').value = ultimaFecha

                let filaTotales = `<tr style="font-weight: bold;background: gray">
                                            <td>-</td>
                                            <td>Total</td>
                                            <td>${sumaCuotas.toFixed(2)}</td>
                                            <td>${sumaIntereses.toFixed(2)}</td>
                                            <td>${sumaCapital.toFixed(2)}</td>
                                            <td>-</td>
                                            <td>-</td>
                                            </tr>`
                $("#tblAmortizacion tbody").append(filaTotales)

            } else {
                Swal.fire('Advertencia♠', 'Se deben de completar todos los campos para poder ver la tabla de amortización', 'warning')
            }
        }

        @can('Editar Desembolsos')
        document.querySelectorAll('.calcularAdmin').forEach(element => {
            element.addEventListener('change', calcularTotalesAdmin);
            element.addEventListener('keyup', calcularTotalesAdmin);
        });

        function calcularTotalesAdmin() {
            let monto = parseFloat(document.getElementById('montoFinanciarAdmin').value) || 0;
            let tasa = parseFloat(document.getElementById('tasaInteresAdmin').value) || 0;
            let plazo = parseFloat(document.getElementById('plazoPagoAdmin').value) || 0;
            let formaPago = document.getElementById('formaPagoAdmin').value;

            if (monto > 0 && tasa >= 0 && plazo > 0 && formaPago != "") {
                let formapagovalor = 0;
                switch (formaPago) {
                    case "1": formapagovalor = 20; break; // Diario
                    case "2": formapagovalor = 4; break;  // Semanal
                    case "3": formapagovalor = 2; break;  // Quincenal
                    case "4": formapagovalor = 1; break;  // Mensual
                    case "5": formapagovalor = 3; break;  // Trimestral
                    case "6": formapagovalor = 2; break;  // Bimestral
                    case "7": formapagovalor = 2; break;  // Catorcenal
                }

                let interesesMes = monto * (tasa / 100);
                let interesesTotalPagar = interesesMes * plazo;
                let subTotalFinanciamiento = monto + interesesTotalPagar;

                let totalCuotas = 0;
                if (["5", "6"].includes(formaPago)) {
                    totalCuotas = plazo / formapagovalor;
                } else {
                    totalCuotas = plazo * formapagovalor;
                }

                let montoCuota = subTotalFinanciamiento / totalCuotas;
                let montoCuotaRounded = parseFloat(montoCuota.toFixed(2));
                
                // La última cuota se ajustará automáticamente para cuadrar el total exacto
                let totalSincronizado = subTotalFinanciamiento;
                let interesSincronizado = interesesTotalPagar;

                document.getElementById('interesMesAdmin').value = interesesMes.toFixed(2);
                document.getElementById('totalInteresesAdmin').value = interesSincronizado.toFixed(2);
                document.getElementById('montoTotalFinanciarAdmin').value = totalSincronizado.toFixed(2);
                document.getElementById('montoCuotaAdmin').value = montoCuotaRounded.toFixed(2);
                document.getElementById('interesPagarAdmin').value = (interesSincronizado / totalCuotas).toFixed(2);
            }
        }
        @endcan

        if (document.getElementById('btnDesembolsar')) {
            document.getElementById('btnDesembolsar').addEventListener('click', function(event) {
                event.preventDefault();
                let url = this.dataset.action;
                Swal.fire({
                    title: '¿Está seguro?',
                    text: "¿Desea marcar como desembolsado?, el desembolso ya será visible para el agente",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#d33',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Guardar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                })
            })
        }
        // Función para ajustar fechas en préstamos DIARIOS (solo lunes a viernes)
        function calcularFechaFinalDiario(fechaInicial, banderaAumentar, diasFeriados) {
            let fechaCuota = new Date(fechaInicial);
            fechaCuota.setDate(fechaCuota.getDate() + banderaAumentar);
            while (true) {
                let fechaVerificar = fechaCuota.toISOString().split('T')[0];
                if (fechaCuota.getDay() === 0 || fechaCuota.getDay() === 6) {
                    fechaCuota.setDate(fechaCuota.getDate() + 1);
                    continue;
                }
                if (diasFeriados.includes(fechaVerificar)) {
                    fechaCuota.setDate(fechaCuota.getDate() + 1);
                    continue;
                }
                break;
            }
            return fechaCuota;
        }

        // Función para ajustar fechas en préstamos NO DIARIOS (sábado válido, rechaza domingo y feriados)
        function calcularFechaFinal(fechaInicial, banderaAumentar, diasFeriados) {
            let fechaCuota = new Date(fechaInicial);
            fechaCuota.setDate(fechaCuota.getDate() + banderaAumentar);
            let fechaVerificar = fechaCuota.toISOString().split('T')[0];
            while (fechaCuota.getDay() === 0 || diasFeriados.includes(fechaVerificar)) {
                fechaCuota.setDate(fechaCuota.getDate() + 1);
                fechaVerificar = fechaCuota.toISOString().split('T')[0];
            }
            return fechaCuota;
        }

        async function getDiasFeriados(){
            let {data} = await axios.get('{{route('configuracion.feriados.getFeriados')}}')
            return data
        }
    </script>
@endsection
