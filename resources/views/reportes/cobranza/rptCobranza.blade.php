@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('reportes.index')])
    Control y Gestión de Cobranza
@endsection
@section('content')
    {{html()->form('GET',route('reportes.cobranza'))->open()}}
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="fecha_inicio"><strong>Fecha de Creación Desde:</strong></label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" required value="{{request('fecha_inicio')}}" class="form-control">
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="fecha_fin"><strong>Fecha de Creación Hasta:</strong></label>
                <input type="date" id="fecha_fin" name="fecha_fin" required value="{{request('fecha_fin')}}" class="form-control">
            </div>
        </div>

          <div class="col-md-4">
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
            <button type="submit" name="excel" value="excel" class="btn btn-success w-100">Exportar a EXCEL <i class="fa fa-file-excel"></i></button>
        </div>
    </div>
    {{html()->form()->close()}}
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-sm table-striped table-bordered">
                    <tr class="table-info">
                        <th>#</th>
                        <th>Consecutivo</th>
                        <th>Tipo</th>
                        <th>Cedula</th>
                        <th>Cliente</th>
                        <th>Fecha Desembolso</th>
                        <th>Fecha Vencimiento</th>
                        <th>Fecha Cuota</th>
                        <th>Diario</th>
                        <th>Semanal</th>
                        <th>Quincenal</th>
                        <th>Mensual</th>
                        <th>Trimestral</th>
                        <th>Bimensual</th>
                        <th>Catorcenal</th>
                        <th>Cobrador</th>
                    </tr>
                    @foreach($cuotas as $cuota)
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$cuota->prestamo->consecutivo}}</td>
                            <td>{{$cuota->prestamo->tipo_prestamo}}</td>
                            <td>{{$cuota->prestamo->cliente->cedula}}</td>
                            <td>{{$cuota->prestamo->cliente->full_name}}</td>
                            <td>{{fecha_d_m_Y($cuota->prestamo->fecha_desembolso)}}</td>
                            <td>{{$cuota->prestamo->cuotas()->orderBy('id','desc')->first() ? fecha_d_m_Y($cuota->prestamo->cuotas()->orderBy('id','desc')->first()->fecha_cuota):"-"}}</td>
                            <td>{{fecha_d_m_Y($cuota->fecha_cuota)}}</td>
                            @if($cuota->prestamo->forma_pago_tipo==1)
                                <td class="text-center">{{number_format($cuota->monto_pendiente_cuota,2)}}</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                            @elseif($cuota->prestamo->forma_pago_tipo==2)
                                <td class="text-center">x</td>
                                <td class="text-center">{{$cuota->monto_pendiente_cuota}}</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                            @elseif($cuota->prestamo->forma_pago_tipo==3)
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">{{$cuota->monto_pendiente_cuota}}</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                            @elseif($cuota->prestamo->forma_pago_tipo==4)
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">{{$cuota->monto_pendiente_cuota}}</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                            @elseif($cuota->prestamo->forma_pago_tipo==5)
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">{{$cuota->monto_pendiente_cuota}}</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                            @elseif($cuota->prestamo->forma_pago_tipo==6)
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">{{$cuota->monto_pendiente_cuota}}</td>
                                <td class="text-center">x</td>
                            @elseif($cuota->prestamo->forma_pago_tipo==7)
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">x</td>
                                <td class="text-center">{{$cuota->monto_pendiente_cuota}}</td>
                            @endif
                            <td>{{$cuota->prestamo->agente->username}}</td>
                        </tr>
                    @endforeach
                </table>
                @if(count($cuotas))
                {{$cuotas->appends(request()->all())->links()}}
                @endif
            </div>
        </div>
    </div>

@endsection
