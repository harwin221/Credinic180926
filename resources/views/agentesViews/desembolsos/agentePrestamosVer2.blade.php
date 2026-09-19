@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('agentes.user.desembolso.index')])
    Detalle de Préstamo
@endsection
@section('content')

    @if($prestamo->estado==4)
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
                <strong>Desembolso Anulado ( {{$prestamo->userAnulado ? $prestamo->userAnulado->full_name:null}} )</strong>
            </div>
        </div>
    </div>
    @endif

        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
                    @if($prestamo->desembolsado == 1)
                        <strong>Préstamo actualmente desembolsado</strong>
                    @else
                        <strong>Pendiente desembolso al cliente</strong>
                    @endif
                </div>
            </div>
        </div>

    <div class="row">
        <div class="col-md-12">
            @if($prestamo->represtamo == 0)
{{--                @can('Realizar Représtamo')--}}
{{--                    <a href="#" class="btn btn-warning float-end" data-bs-target="#modalReprestamo" data-bs-toggle="modal" onclick="calcularValores()">Realizar Représtamo</a>--}}
{{--                    <a href="#" class="btn btn-primary" data-bs-target="#modalEvidencias" data-bs-toggle="modal">Evidencias</a>--}}
{{--                    @include('prestamos.modals.modalReprestamo')--}}
{{--                    @include('prestamos.modals.modalEvidencias')--}}
{{--                @endcan--}}
            @else
                <div class="alert alert-info alert-dismissible fade show text-center" role="alert">
                    <strong>Este es un Représtamo</strong>
                </div>
            @endif
        </div>
    </div>
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
                        <input type="text" class="form-control" disabled id="desembolsado" value="{{$prestamo->userDesembolso->full_name}}">
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

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tipo_mora" class="fw-bold">Tipo Mora:</label>
                        <input type="text" class="form-control" disabled id="tipo_mora" value="{{$prestamo->tipo_mora==1?'Valor Fijo':'Valor Porcentual'}}">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="desembolsado" class="fw-bold">Dias Aplicar Mora:</label>
                        <input type="text" class="form-control" disabled id="dias_mora" value="{{$prestamo->dias_aplicar_mora}}">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="desembolsado" class="fw-bold">Monto Mora:</label>
                        <input type="text" class="form-control" disabled id="monto_mora" value="{{$prestamo->monto_mora}}">
                    </div>
                </div>
            </div>
            <br>
{{--            @if($prestamo->estado!=4)--}}
{{--            <div class="row">--}}
{{--                @can('Reasignar Vendedor')--}}
{{--                    <div class="col-md-6 text-end">--}}
{{--                        <a href="#" class="btn btn-sm btn-primary btnModificarUsuario" data-bs-target="#modalUsuario" data-bs-toggle="modal" data-tipo="vendedor">Reasignar Vendedor <i class="fa fa-user"></i></a>--}}
{{--                    </div>--}}
{{--                @endcan--}}
{{--                @can('Reasignar Corbrador')--}}
{{--                    <div class="col-md-6">--}}
{{--                        <a href="#" class="btn btn-sm btn-primary btnModificarUsuario" data-bs-target="#modalUsuarioCobrador" data-bs-toggle="modal" data-tipo="cobrador">Reasignar Cobrador <i class="fa fa-user"></i></a>--}}
{{--                    </div>--}}
{{--                @endcan--}}
{{--            </div>--}}
{{--            @endif--}}
            <br>
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
                                    <td>{{fecha_d_m_Y($cuota->fecha_cuota)}}</td>
                                    <td>{{$cuota->prestamo->moneda." ". $cuota->monto_cuota}}</td>
                                    <td>{{$cuota->prestamo->moneda." ". $cuota->monto_interes}}</td>
                                    <td>{{$cuota->prestamo->moneda." ". $cuota->monto_cuota - $cuota->monto_interes}}</td>
                                    <td>{{$cuota->prestamo->moneda." ". $cuota->monto_mora}}</td>
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
        </div>
    </div>

    @include('prestamos.modals.modalVerAbonos')
    @include('prestamos.modals.modalCambiarUsuarioPrestamo')
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
            let ruta = '{{route('agente.abono.getListAbonos','?')}}'
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


        // document.getElementById('btnAmortizacion').addEventListener('click',generarTablaAmortizacion)

        async function getAgentes() {
            $("#selectAgente").empty();
            let url = '{{route('agentes.user.getAgentes')}}'

            const {data} = await axios.get(url)
            for (let key in data) {
                let opcion = `<option value="${key}"> ${data[key]} </option>`

                $("#selectAgente").append(opcion)
            }
        }

        async function getVendedores() {
            $("#selectVendedor").empty();
            let url = '{{route('agente.user.getListAdministrativos')}}'
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

                    let resPlazo = parseFloat(plazoPago) * formapagovalor
                    if( resPlazo - Math.floor(resPlazo) > 0 )
                        Swal.fire('Advertencia','El número de cuotas según los parámetros ingresados generan valores decimales ['+resPlazo+"]",'warning')

                    interesesMes = parseFloat(montoFinanciar) * (parseFloat(tasaInteres)/100);
                    interesesPagar = interesesMes * parseFloat(plazoPago);
                    subTotalFinanciamiento = parseFloat(montoFinanciar) + interesesPagar;

                    let montoCuotaCalculado = subTotalFinanciamiento / resPlazo;
                    let montoCuotaRounded = parseFloat(montoCuotaCalculado.toFixed(2));
                    
                    // La última cuota se ajustará automáticamente para cuadrar el total exacto
                    interesCuota = interesesPagar / resPlazo;
                    montoCuotaMes = montoCuotaRounded;
                }
            }

            txtMontoFinanciarTotal.value = subTotalFinanciamiento.toFixed(2);
            txtMontoCuota.value = montoCuotaMes.toFixed(2);
            txtInteresesPagar.value = interesCuota.toFixed(2);
            txtInteresesPorMes.value = interesesMes.toFixed(2);
            txtTotalIntereses.value = interesesPagar.toFixed(2);
        }

        function getFormaPagoValores() {
            const formaPago = document.getElementById('formaPago').value
            let formapagovalor = 0;
            if (formaPago !== '') {
                switch (formaPago) {
                    case "1":
                        formapagovalor = 20
                        break
                    case "2":
                        formapagovalor = 4;
                        break
                    case "3":
                        formapagovalor = 2;
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
                }
            }
            return valorAumentar
        }

        function procesarNumeroDecimal(numero) {
            return numero;
        }

        function generarTablaAmortizacion() {

            $("#tblAmortizacion tbody").empty()

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
                // let plazo2 = parseFloat(plazoPago.value) - Math.floor(parseFloat(plazoPago.value))
                let resPlazo = parseFloat(plazoPago.value) * formapagovalor
                if( resPlazo - Math.floor(resPlazo) > 0 )
                    Swal.fire('Advertencia','El número de cuotas según los parámetros ingresados generan valores decimales ['+resPlazo+"]",'warning')

                const fechaInicial = new Date(fechaPrimerPago); // Fecha actual como ejemplo
                let fechaCopia = fechaInicial
                const banderaAumentar = getValorFechaAumentar(); // Número de días a agregar
                let fechaCuotaFormateada = fechaPrimerPago
                let ultimaFecha = ""

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

                        const fechaCuota = new Date(fechaCopia);
                        ultimaFecha = fechaCuotaFormateada
                        fechaCuota.setDate(fechaCuota.getDate() + banderaAumentar);

                        fechaCuotaFormateada = fechaCuota.toISOString().split('T')[0];

                        fechaCopia = new Date(fechaCuotaFormateada);
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

            } else {
                Swal.fire('Advertencia♠', 'Se deben de completar todos los campos para poder ver la tabla de amortización', 'warning')
            }
        }


    </script>
@endsection
