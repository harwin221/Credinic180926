@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('agentes.abonos.index')])
    Información del Abono
@endsection
@section('content')
    @if($abono->estado==2)
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
                    <strong>Abono Anulado ( {{$abono->userAnulado ? $abono->userAnulado->full_name:null}} )</strong>
                </div>
            </div>
        </div>
    @endif
    <div class="row">
        <div class="col-md-12 text-end">
            <a href="{{route('agentes.abonos.printRecibo',[$abono->id_enc,'reimpresion'=>true])}}" target="_blank" class="btn btn-sm btn-warning">Reimprimir <i class="fa fa-print"></i></a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <h5>Información del Cliente:</h5>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-3">
            <div class="my-auto">
                <div class="form-group text-center">
                    <img src="{{ isset($abono->prestamo->cliente) ? $abono->prestamo->cliente->user_image : asset('assets/img/clientesFotos/no-photo.jpg')}}" class="img-thumbnail" alt="">
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="nombreCliente" class="fw-bold">Nombres:</label>
                        <input type="text" class="form-control bg-secondary-light" id="nombreCliente" value="{{$abono->prestamo->cliente->nombres}}">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="apellidoCliente" class="fw-bold">Apellidos:</label>
                        <input type="text" class="form-control bg-secondary-light" id="apellidoCliente" value="{{$abono->prestamo->cliente->apellidos}}">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="cedulaCliente" class="fw-bold">Cédula:</label>
                        <input type="text" disabled class="form-control bg-secondary-light" id="cedulaCliente" value="{{$abono->prestamo->cliente->cedula}}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="direccionCliente" class="fw-bold">Dirección:</label>
                        <input  type="text" disabled class="form-control bg-secondary-light" id="direccionCliente" value="{{$abono->prestamo->cliente->direccion}}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="telefonoCliente" class="fw-bold">Teléfono:</label>
                        <input type="text" disabled class="form-control bg-secondary-light" id="telefonoCliente" value="{{$abono->prestamo->cliente->telefono}}">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="celularCliente" class="fw-bold">Celular:</label>
                        <input type="text" disabled class="form-control bg-secondary-light" id="celularCliente" value="{{$abono->prestamo->cliente->celular}}">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="departamentoCliente" class="fw-bold">Departamento:</label>
                        <input type="text" disabled class="form-control bg-secondary-light" id="departamentoCliente" value="{{$abono->prestamo->cliente->departamento_municipio->departamento->nombre}}">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="municipioCliente" class="fw-bold">Municipio:</label>
                        <input type="text" class="form-control bg-secondary-light" id="municipioCliente" value="{{$abono->prestamo->cliente->departamento_municipio->nombre}}">
                    </div>
                </div>
            </div>
        </div>

    </div>
    <br>
    <hr>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for=""><strong>Fecha del Abono:</strong></label>
                <input type="text" class="form-control" disabled value="{{$abono->fecha_abono}}">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for=""><strong>Total Abonado:</strong></label>
                <input type="text" class="form-control" disabled value="{{$abono->total_abonado}}">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for=""><strong>Realizado Por:</strong></label>
                <input type="text" class="form-control" disabled value="{{$abono->user_create->full_name}}">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for=""><strong>N° del Préstamo</strong></label>
                <input type="text" class="form-control" disabled value="{{$abono->prestamo->consecutivo}}">
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for=""><strong>Monto Efectivo</strong></label>
                <input type="text" class="form-control" disabled value="{{$abono->prestamo->moneda." ".number_format($abono->total_efectivo,2)}}">
            </div>
        </div>

{{--        <div class="col-md-3">--}}
{{--            <div class="form-group">--}}
{{--                <label for=""><strong>Monto Tarjeta</strong></label>--}}
{{--                <input type="text" class="form-control" disabled value="{{$abono->prestamo->moneda." ".number_format($abono->total_tarjeta,2)}}">--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <div class="col-md-3">--}}
{{--            <div class="form-group">--}}
{{--                <label for=""><strong>Monto Cheque</strong></label>--}}
{{--                <input type="text" class="form-control" disabled value="{{$abono->prestamo->moneda." ".number_format($abono->total_cheque,2)}}">--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <div class="col-md-3">--}}
{{--            <div class="form-group">--}}
{{--                <label for=""><strong>Monto Transferencia</strong></label>--}}
{{--                <input type="text" class="form-control" disabled value="{{$abono->prestamo->moneda." ".number_format($abono->total_transferencia,2)}}">--}}
{{--            </div>--}}
{{--        </div>--}}
    </div>

    <br>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">

            </div>
        </div>

{{--        <div class="col-md-3">--}}
{{--            <div class="form-group">--}}
{{--                <label for=""><strong>#REF Tarjeta</strong></label>--}}
{{--                <input type="text" class="form-control" disabled value="{{isset($abono->referencia_tarjeta) && $abono->referencia_tarjeta!=''?number_format($abono->referencia_tarjeta,2):'-'}}">--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <div class="col-md-3">--}}
{{--            <div class="form-group">--}}
{{--                <label for=""><strong>#REF Cheque</strong></label>--}}
{{--                <input type="text" class="form-control" disabled value="{{isset($abono->referencia_cheque) && $abono->referencia_cheque!=''?number_format($abono->referencia_cheque,2):'-'}}">--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <div class="col-md-3">--}}
{{--            <div class="form-group">--}}
{{--                <label for=""><strong>#REF Transferencia</strong></label>--}}
{{--                <input type="text" class="form-control" disabled value="{{isset($abono->referencia_transferencia)&& $abono->referencia_transferencia!='' ?number_format($abono->referencia_transferencia,2):'-'}}">--}}
{{--            </div>--}}
{{--        </div>--}}
    </div>
    <br>
    <div class="row">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                <tr class="table-success">
                    <th>#</th>
                    <th>Número Cuota</th>
                    <th>Total Abonado</th>
                    <th>Total Interes</th>
                    <th>Total Capital</th>
                    <th>Total Mora</th>
                    <th>Total Pendiente</th>
                </tr>
                </thead>
                @foreach($abono->abono_detalle as $detalle)
                    <tr>
                        <td>{{$loop->index+1}}</td>
                        <td>{{$detalle->prestamo_cuota->numero_cuota." / ".count($detalle->prestamo_cuota->prestamo->cuotas)}}</td>
                        <td>{{$detalle->prestamo_cuota->prestamo->moneda." ". $detalle->monto_abono}}</td>
                        <td>{{$detalle->prestamo_cuota->prestamo->moneda." ". $detalle->total_interes}}</td>
                        <td>{{$detalle->prestamo_cuota->prestamo->moneda." ". $detalle->total_capital}}</td>
                        <td>{{$detalle->prestamo_cuota->prestamo->moneda." ". $detalle->total_mora}}</td>
                        <td>{{$detalle->prestamo_cuota->prestamo->moneda." ". $detalle->prestamo_cuota->monto_pendiente_cuota}}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
