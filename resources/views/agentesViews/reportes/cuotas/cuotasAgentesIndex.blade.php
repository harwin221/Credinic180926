@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('agentes.reportes.index')])
    Listado de Cuotas
@endsection
@section('content')
    {{html()->form('GET',route('agentes.reportes.cuotas'))->open()}}
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
    </div>
    <br>

    <div class="row">
        <div class="col-md-8">
            <button type="submit" class="btn btn-primary w-100">Generar Lista <i class="fa fa-cog"></i></button>
        </div>
{{--        <div class="col-md-2">--}}
{{--            <button type="submit" name="pdf" value="pdf" class="btn btn-danger w-100">PDF <i class="fa fa-file-pdf"></i></button>--}}
{{--        </div>--}}
{{--        <div class="col-md-2">--}}
{{--            <button type="submit" name="excel" value="excel" class="btn btn-success w-100">EXCEL <i class="fa fa-file-excel"></i></button>--}}
{{--        </div>--}}
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
                        <th>Estado</th>
                        <th>Cuota</th>
                        <th>Interes</th>
                        <th>Pagado</th>
                        <th>Pendiente</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $suma = 0; ?>
                    @foreach($abonos as $abono)
                        <tr>
                            <td>{{$abono->prestamo_cuota->numero_cuota}}</td>
                            <td>{{$abono->prestamo_cuota->prestamo->cliente->full_name}}</td>
                            <td>{{$abono->prestamo_cuota->prestamo->agente->full_name}}</td>
                            <td>{{fecha_d_m_Y_h_i($abono->fecha_abono)}}<br></td>
                            <td>{{$abono->prestamo_cuota->estado_cuota}}</td>
                            <td>{{number_format($abono->prestamo_cuota->monto_cuota,2)}}</td>
                            <td>{{number_format($abono->prestamo_cuota->monto_intereses,2)}}</td>
                            <td>{{number_format($abono->monto_abono,2)}}</td>
                            <td>{{number_format($abono->prestamo_cuota->monto_cuota - $abono->prestamo_cuota->abonos()->sum('monto_abono'),2)}}</td>
                        </tr>
                    <?php $suma += $abono->monto_abono; ?>
                    @endforeach
                    <tr class="table-secondary">
                        <th colspan="7" style="text-align: right">Total C$</th>
                        <th colspan="2">{{number_format($suma)}}</th>
                    </tr>
                    </tbody>
                </table>
                {{$abonos->appends(request()->all())->links()}}
            </div>
        </div>
    </div>
@endsection
