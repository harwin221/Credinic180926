@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('reportes.index')])
    Listado de Abonos
@endsection
@section('content')
    {{html()->form('GET',route('reportes.listaCuotas.index'))->open()}}
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="inicio"><b>Fecha desde:</b></label>
                <input type="date" name="desde" class="form-control" value="{{request('desde')}}" id="desde">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="inicio"><b>Fecha hasta:</b></label>
                <input type="date" name="hasta" class="form-control" value="{{request('hasta')}}" id="hasta">
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="cobrador"><strong>Cobrador:</strong></label>
                {{html()->select('cobrador',[0=>'Todos']+$listaCobradores,request('cobrador'))->class('form-control select2')->style(['width'=>'100%'])->id('cobrador')}}
            </div>
        </div>
    </div>
    <br>

    <div class="row">
        <div class="col-md-6">
            <button type="submit" name="pdf" value="pdf" class="btn btn-primary w-100" formtarget="_blank">Generar Reporte PDF <i class="fa fa-file-pdf"></i></button>
        </div>
        <div class="col-md-6">
            <div class="btn-group w-100" role="group">
                <button id="btnGroupDrop1" type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    Exportar a Excel <i class="fa fa-file-excel"></i>
                </button>
                <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                    <li>
                        <button class="dropdown-item" type="submit" name="excel1" value="excel1">Detallado</button>
                    </li>
                    <li>
                        <button class="dropdown-item" type="submit" name="excel2" value="excel2">Consolidado</button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    {{html()->form()->close()}}
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                    <tr class="table-success">
                        <th>N° Cuota</th>
                        <th>Cliente</th>
                        <th>Cobrador</th>
                        <th>Fecha</th>
                        <th>Estado Abono</th>
                        <th>Estado Cuota</th>
                        <th>Cuota</th>
                        <th>Pagado</th>
                        <th>Pendiente</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($abonos as $abono)
                        <tr>
                            <td>{{$abono->prestamo_cuota->numero_cuota}}</td>
                            <td>{{$abono->prestamo_cuota->prestamo->cliente->full_name}}</td>
                            <td>{{$abono->prestamo_cuota->prestamo->agente->full_name}}</td>
                            <td>{{fecha_d_m_Y_h_i($abono->fecha_abono)}}<br></td>
                            <td>
                                @if(isset($abono->abono))
                                    @if($abono->abono->estado == 2)
                                        <span class="badge bg-danger">Anulado</span>
                                    @else
                                        {{$abono->abono->estado_abono}}
                                    @endif
                                @endif
                            </td>
                            <td>{{$abono->prestamo_cuota->estado_cuota}}</td>
                            <td>{{$abono->prestamo_cuota->prestamo->moneda." ".number_format($abono->prestamo_cuota->monto_cuota,2)}}</td>
                            <td>{{$abono->prestamo_cuota->prestamo->moneda." ".number_format($abono->monto_abono,2)}}</td>
                            <td>{{$abono->prestamo_cuota->prestamo->moneda." ".number_format($abono->prestamo_cuota->monto_pendiente_cuota,2)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if(count($abonos))
                    {{$abonos->appends(request()->all())->links()}}
                @endif
            </div>
        </div>
    </div>
@endsection
