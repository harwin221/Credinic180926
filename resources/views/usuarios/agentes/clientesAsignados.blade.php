@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('user.clientes.index')])
    Clientes Asignados de: {{$agente->full_name}}
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h4>Préstamos Activos Asignados</h4>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                    <tr class="table-success">
                        <th>#</th>
                        <th># Préstamo</th>
                        <th>Cliente</th>
                        <th>Fecha Préstamo</th>
                        <th>Monto</th>
                        <th>Monto Financiado</th>
                        <th>Capital Pendiente</th>
                        <th>Interes Pendiente</th>
                        <th>Pendiente</th>
                        <th>Acción</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($prestamos as $asignados)
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$asignados->consecutivo}}</td>
                            <td>{{$asignados->cliente->full_name}}</td>
                            <td>{{fecha_d_m_Y($asignados->fecha_prestamo)}}</td>
                            <td>{{$asignados->moneda." ".$asignados->monto_prestamo}}</td>
                            <td>{{$asignados->moneda." ".$asignados->monto_financiado}}</td>
                            <td>{{$asignados->moneda." ".$asignados->total_pendiente_capital}}</td>
                            <td>{{$asignados->moneda." ".$asignados->total_pendiente_interes}}</td>
                            <td>{{$asignados->moneda." ".$asignados->pendiente_abono}}</td>
                            <td>
                                <a href="{{route('prestamos.show',$asignados->id_enc)}}" class="btn btn-sm btn-primary">Ver Préstamo <i class="fa fa-eye"></i></a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{$prestamos->links()}}
            </div>
        </div>
    </div>
@endsection
