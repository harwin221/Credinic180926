@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('reportes.index')])
    Clientes Inactivos
@endsection
@section('content')
    {{html()->form('GET',route('reportes.estadoClientes'))->open()}}
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="cliente"><strong>Cliente:</strong></label>
                {{html()->select('cliente',[0=>'Todos']+$listaClientes,request('cliente'))->class('form-control select2')->style(['width'=>'100%'])->id('cliente')}}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="estado"><strong>Estado:</strong></label>
                {{html()->select('estado',['0'=>'Todos','1'=>'Activos','2'=>'Inactivos'],request('estado'))->class('form-control')->id('estado')}}
            </div>
        </div>
    </div>
    <br>
     <div class="row">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary w-100">Generar Lista <i class="fa fa-cog"></i></button>
        </div>
    </div>
    {{html()->form()->close()}}
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped table-sm" style="font-size: 12px">
                    <tr>
                        <td>#</td>
                        <td>Código</td>
                        <td>Cliente</td>
                        <td>Teléfono</td>
                        <td>Dirección</td>
                        <td>Último Crédito</td>
                        <td>Préstamos Activos</td>
                        <td>Estado</td>
                    </tr>
                    @php $totalMontoCreditos = 0; @endphp
                    @foreach($clientes as $cl)
                        @php
                            $ultimoPrestamo = $cl->prestamos->first();
                            $montoUltimo = $ultimoPrestamo ? $ultimoPrestamo->monto_prestamo : 0;
                            $monedaUltimo = $ultimoPrestamo ? ($ultimoPrestamo->moneda_prestamo == 1 ? 'C$' : 'U$') : 'C$';
                            $prestamosActivos = $cl->prestamos_activos_count ?? 0;
                            $telefono = trim($cl->telefono1 . ($cl->telefono2 ? ' / ' . $cl->telefono2 : ''));
                            $totalMontoCreditos += $montoUltimo;
                        @endphp
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$cl->codigo_cliente}}</td>
                            <td>{{$cl->full_name}}</td>
                            <td>{{$telefono ?: 'N/A'}}</td>
                            <td>{{$cl->direccion}}</td>
                            <td>{{$monedaUltimo}} {{number_format($montoUltimo, 2)}}</td>
                            <td>{{$prestamosActivos}}</td>
                            <td>
                                <span class="badge bg-{{$prestamosActivos > 0 ? 'success' : 'secondary'}}">
                                    {{$prestamosActivos > 0 ? 'Activo' : 'Inactivo'}}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    @if($clientes->count() > 0)
                    <tr style="background-color: #f3f4f6; font-weight: bold;">
                        <td colspan="5" class="text-right">TOTALES:</td>
                        <td><strong>C$ {{number_format($totalMontoCreditos, 2)}}</strong></td>
                        <td><strong>{{$clientes->total()}} clientes</strong></td>
                        <td></td>
                    </tr>
                    @endif
                </table>
                {{$clientes->appends(request()->all())->links()}}
            </div>
        </div>
    </div>
@endsection
