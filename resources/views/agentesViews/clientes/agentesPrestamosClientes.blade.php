@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('agentes.misClientes')])
    Prestamos del Cliente [ <span style="color: blue">{{$cliente->full_name}}</span> ]
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <h3 class="p-1 rounded" style="background: steelblue;color: white">Datos del Cliente</h3>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 my-auto">
            <div class="form-group text-center">
                <img src="{{ isset($cliente) ? $cliente->user_image : asset('assets/img/clientesFotos/no-photo.jpg')}}" class="img-thumbnail" alt="">
            </div>
        </div>
        <div class="col-md-9">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nombres" class="fw-bold">Nombres:</label>
                        <input type="text" class="form-control" disabled id="nombres" value="{{$cliente->nombres}}">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nombres" class="fw-bold">Apellidos:</label>
                        <input type="text" class="form-control" disabled id="nombres" value="{{$cliente->apellidos}}">
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="estado_civil" class="fw-bold">Estado Civil:</label>
                        <input type="text" class="form-control" disabled id="estado_civil" value="{{$cliente->estado_civil_user}}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="sexo" class="fw-bold">Sexo:</label>
                        <input type="text" class="form-control" disabled id="sexo" value="{{$cliente->sexo==0?'Masculino':'Femenino'}}">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="cedula" class="fw-bold">Cédula:</label>
                        <input type="text" class="form-control" disabled id="cedula" value="{{$cliente->cedula}}">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="direccion" class="fw-bold">Dirección:</label>
                <textarea rows="3" class="form-control" disabled id="direccion">{{$cliente->direccion}}</textarea>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="telefono1" class="fw-bold">Teléfono 1:</label>
                        <input type="text" class="form-control" disabled id="telefono1" value="{{$cliente->telefono1}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="telefono2" class="fw-bold">Teléfono 2:</label>
                        <input type="text" class="form-control" disabled id="telefono2" value="{{$cliente->telefono2}}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="departamento" class="fw-bold">Departamento:</label>
                        <input type="text" class="form-control" disabled id="departamento" value="{{$cliente->departamento_municipio->departamento->nombre}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="municipio" class="fw-bold">Municipio:</label>
                        <input type="text" class="form-control" disabled id="municipio" value="{{$cliente->departamento_municipio->departamento->nombre}}">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
    <hr>
    @foreach($prestamosCliente as $prestamo)
        <div class="row">
            <div class="col-md-12 text-center bg-success p-2" style="color: white;border-radius: 5px">
                <h2><b>Número Desembolso: {{$prestamo->consecutivo}}</b></h2>
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
                                        <td>{{fecha_d_m_Y($cuota->fecha_cuota)}}</td>
                                        <td>{{$cuota->prestamo->moneda." ". $cuota->monto_cuota}}</td>
                                        <td>{{$cuota->prestamo->moneda." ". $cuota->monto_interes}}</td>
                                        <td>{{$cuota->prestamo->moneda." ". $cuota->monto_cuota - $cuota->monto_interes}}</td>
                                        <td>{{$cuota->prestamo->moneda." ". $cuota->monto_mora}}</td>
                                        <td>{{$cuota->prestamo->moneda." ". $cuota->monto_pendiente_cuota}}</td>
                                        <td>{{$cuota->fecha_pagado}}</td>
                                        <td>
                                            @if($cuota->estado == 1)
                                                <span class="badge bg-primary text-white">Pendiente <i class="fa fa-clock"></i></span>
                                            @elseif($cuota->estado == 3)
                                                <span class="badge bg-success">Pagado <i class="fa fa-check"></i> </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(count($cuota->abonos))
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
                                    <th>{{number_format($prestamo->suma_capital,2)}}</th>
                                    <th></th>
                                    <th>{{number_format($prestamo->pendiente_abono,2)}}</th>
                                    <td colspan="3"></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        @include('agentesViews.clientes.agenteModalVerAbonos')
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
        document.querySelectorAll('#carouselExample .carousel-item img').forEach(function(item) {
            item.addEventListener('click', function() {
                const imgSrc = this.getAttribute('data-img-src');
                document.getElementById('modal-image').src = imgSrc;
            });
        });

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
    </script>
@endsection
