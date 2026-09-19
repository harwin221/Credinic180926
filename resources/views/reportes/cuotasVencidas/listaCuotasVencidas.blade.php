@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('reportes.index')])
    Listado de Cuotas Vencidas
@endsection
@section('content')
    {{html()->form('GET',route('reportes.cuotasVencidas.index'))->open()}}
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="desde"><strong>Fecha desde:</strong></label>
                <input type="date" name="desde" class="form-control" value="{{request('desde')}}" id="desde">
            </div>
        </div>


        <div class="col-md-3">
            <div class="form-group">
                <label for="hasta"><strong>Fecha hasta:</strong></label>
                <input type="date" name="hasta" class="form-control" value="{{request('hasta')}}" id="hasta">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="cobrador"><strong>Cobrador:</strong></label>
                {{html()->select('cobrador',[0=>'Todos']+$listaCobradores,request('cobrador'))->class('form-control select2')->style(['width'=>'100%'])->id('cobrador')}}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="cliente"><strong>Cliente:</strong></label>
                {{html()->select('cliente',[0=>'Todos']+$listaClientes,request('cliente'))->class('form-control select2')->style(['width'=>'100%'])->id('cliente')}}
            </div>
        </div>
    </div>
    <br>

    <div class="row">
        <div class="col-md-6">
            <button type="submit" name="pdf" value="pdf" class="btn btn-primary w-100" formtarget="_blank">Generar Reporte PDF <i class="fa fa-file-pdf"></i></button>
        </div>
        <div class="col-md-6">
            <button type="submit" name="excel" value="excel" class="btn btn-success w-100">Exportar a EXCEL <i class="fa fa-file-excel"></i></button>
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
                        <th>N° Préstamo</th>
                        <th>Cliente</th>
                        <th>Cobrador</th>
                        <th>Fecha Cuota</th>
                        <th>Estado</th>
                        <th>Cuota</th>
                        <th>Pagado</th>
                        <th>Pendiente</th>
{{--                        <th>Acción</th>--}}
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($cuotasVencidas as $cuota)
                        <tr>
                            <td>{{$cuota->numero_cuota}}</td>
                            <td>{{$cuota->prestamo->consecutivo}}</td>
                            <td>{{$cuota->prestamo->cliente->full_name}}</td>
                            <td>{{$cuota->prestamo->agente->full_name}}</td>
                            <td>{{fecha_d_m_Y($cuota->fecha_cuota)}}</td>
                            <td>{{$cuota->estado_cuota}}</td>
                            <td>{{$cuota->monto_cuota}}</td>
                            <td>{{$cuota->abonos()->where('prestamo_cuota_abono.estado',1)->sum('monto_abono')}}</td>
                            <td>{{$cuota->monto_cuota - $cuota->abonos()->where('prestamo_cuota_abono.estado',1)->sum('monto_abono')}}</td>
{{--                            <td><a href="#" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></a></td>--}}
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{$cuotasVencidas->appends(request()->all())->links()}}
            </div>
        </div>
    </div>
@endsection
