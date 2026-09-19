@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('reportes.index')])
    Listado de Desembolsos
@endsection
@section('content')
    {{html()->form('GET',route('reportes.listaClientes.index'))->open()}}
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="cliente"><strong>Cliente:</strong></label>
                {{html()->select('cliente',[0=>'Todos']+$listaClientes,request('cliente'))->class('form-control select2')->style(['width'=>'100%'])->id('cliente')}}
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
                <label for="cobrador"><strong>Vendedor:</strong></label>
                {{html()->select('vendedor',[0=>'Todos']+$listaVendedores,request('vendedor'))->class('form-control select2')->style(['width'=>'100%'])->id('cobrador')}}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="estado"><strong>Estado del Préstamo:</strong></label>
                {{html()->select('estado',['1'=>'Pendientes / Activos','2'=>'Cancelados','3'=>'Vencidos','4'=>'Anulados'],request('estado'))->class('form-control')->id('estado')}}
            </div>
        </div>


    </div>
    <br>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="frecuencia"><strong>Frecuencia:</strong></label>
                {{html()->select('frecuencia',['0'=>'Todos','1'=>'Diario','2'=>'Semanal','3'=>'Quincenal','4'=>'Mensual','5'=>'Trimestral','6'=>'Bimestral','7'=>'Catorcenal'],request('frecuencia'))->class('form-control')->id('frecuencia')}}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="fecha_inicio"><strong>Fecha de Creación Desde:</strong></label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" value="{{request('fecha_inicio')?:\Carbon\Carbon::now()->toDateString()}}" class="form-control">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="fecha_fin"><strong>Fecha de Creación Hasta:</strong></label>
                <input type="date" id="fecha_fin" name="fecha_fin" value="{{request('fecha_fin')?:\Carbon\Carbon::now()->toDateString()}}" class="form-control">
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
    <div class="col-md-12">
        <div class="table-responsive" > {{-- style="overflow: inherit" --}}
            <table class="table table-striped table-sm" style="font-size: 12px">
                <tr class="table-success">
                    <th># Préstamo</th>
                    <th>Tipo</th>
                    <th># Cuotas</th>
                    <th>Cliente</th>
                    <th>Frecuencia</th>
                    <th>Fecha del Préstamo</th>
                    <th>Fecha Cancelado</th>
                    <th>Cuota</th>
                    <th>Estado</th>
                    <th>Cobrador</th>
                    <th>Vendedor</th>
                    <th>Acción</th>
                </tr>
                @foreach($prestamos as $prestamo)
                    <tr>
                        <td>{{$prestamo->consecutivo}}</td>
                        <td>{{$prestamo->tipo_prestamo}}</td>
                        <td>{{$prestamo->cuotas->count()}}</td>
                        <td>
                            {{$prestamo->cliente->full_name}} <br>
                            <span title="Monto del Desembolso"><u>MP</u></span>: {{$prestamo->moneda." ".$prestamo->monto_prestamo}} | <span title="Pendiente"><u>P</u></span>: {{$prestamo->moneda." ".number_format( $prestamo->suma_cuotas - $prestamo->suma_abonos,2)}}
                        </td>
                        <td>{{$prestamo->forma_pago}}</td>
                        <td>{{fecha_d_m_Y($prestamo->fecha_prestamo)}}</td>
                        <td>{{count($prestamo->abonos) > 0 && $prestamo->estado === 2 ?fecha_d_m_Y($prestamo->abonos()->orderBy('fecha_abono','desc')->first()->fecha_abono):'-'}}</td>
                        <td>{{$prestamo->monto_cuota}}</td>
                        <td class="text-center">
                            <span @if($prestamo->estado==1) class="badge bg-success" @elseif($prestamo->estado == 4) class="badge bg-danger" @else class="badge bg-info"  @endif>
                                {{$prestamo->estado_prestamo}}
                                @if($prestamo->estado==4)
                                    <br> {{fecha_d_m_Y_h_i($prestamo->fecha_anulado)}}<br>{{$prestamo->userAnulado ? $prestamo->userAnulado->full_name:null}} @endif
                            </span>
                        </td>
                        <td>{{$prestamo->agente->full_name}}</td>
                        <td>{{$prestamo->vendedor->full_name}}</td>
                        <td><div class="btn-group" role="group">
                                <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-cog"></i>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                    <li><a class="dropdown-item" target="_blank" href="{{route('prestamos.show',encode($prestamo->id))}}">Ver Préstamo</a></li>
                                    <li><a class="dropdown-item" target="_blank" href="{{route('user.clientes.edit',encode($prestamo->cliente->id))}}">Ver Cliente</a></li>
                                </ul>
                            </div></td>
                    </tr>
                @endforeach
            </table>
            @if(count($prestamos))
                {{$prestamos->appends(request()->all())->links()}}
            @endif
        </div>
    </div>
@endsection
