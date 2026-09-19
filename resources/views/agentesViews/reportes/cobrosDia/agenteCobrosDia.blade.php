@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url' => route('agentes.reportes.index')])
    Cobros por Día
@endsection
@section('content')
    {{html()->form('GET',route('agentes.reportes.cobrosDia'))->open()}}
    <div class="row">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="fecha"><strong>Fecha desde:</strong></label>
                    <input type="date" value="{{(request('fecha'))?request('fecha'):\Carbon\Carbon::now()->toDateString()}}" id="fecha" name="fecha" class="form-control" required>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label for="fecha2"><strong>Fecha hasta:</strong></label>
                    <input type="date" value="{{(request('fecha2'))?request('fecha2'):\Carbon\Carbon::now()->toDateString()}}" id="fecha2" name="fecha2" class="form-control" required>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary w-100">Generar Lista <i class="fa fa-cog"></i></button>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                    <tr class="table-success">
                        <th>#</th>
                        <th># Préstamo</th>
                        <th># Cuota</th>
                        <th>Cliente</th>
                        <th>Dirección</th>
                        <th>Dep / Mun </th>
                        <th>Cobrador</th>
                        <th>Fecha Cuota</th>
                        <th>Monto Pendiente Cuota</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($prestamoCuotas as $prestamo)
                        <tr @if($prestamo->fecha_cuota < \Carbon\Carbon::now()->toDateString()) class="table-danger" @endif>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$prestamo->prestamo->consecutivo}}</td>
                            <td>{{$prestamo->numero_cuota ." / ". count($prestamo->prestamo->cuotas)}}</td>
                            <td>{{$prestamo->prestamo->cliente->full_name}}</td>
                            <td>{{$prestamo->prestamo->cliente->direccion}}</td>
                            <td>{{$prestamo->prestamo->cliente->departamento_municipio->departamento->nombre}} / {{$prestamo->prestamo->cliente->departamento_municipio->nombre}}</td>
                            <td>{{$prestamo->prestamo->agente->full_name}}</td>
                            <td>{{fecha_d_m_Y($prestamo->fecha_cuota)}}</td>
                            <td>{{$prestamo->prestamo->moneda." ". number_format($prestamo->monto_pendiente_cuota)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{$prestamoCuotas->appends(request()->all())->links()}}
            </div>
        </div>
    </div>
@endsection
