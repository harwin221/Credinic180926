@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('reportes.index')])
    Detalle de Recuperación
@endsection
@section('content')
    {{html()->form('GET',route('reportes.detalleColocacion.index'))->open()}}
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="inicio"><strong>Fecha desde:</strong></label>
                <input type="date" name="desde" class="form-control" value="{{request('desde')}}" id="desde">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="inicio"><strong>Fecha hasta:</strong></label>
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
                <label for="cobrador"><strong>Tipo:</strong></label>
                {{html()->select('tipo',[''=>'Todos','0'=>'Ordinarios','1'=>'Deducciones','2'=>'Dispensas'],request('tipo'))->class('form-control select2')->style(['width'=>'100%'])}}
            </div>
        </div>
        
        <div class="col-md-3 mt-3">
            <div class="form-group">
                <label><strong>Tipo de Vista:</strong></label><br>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="tipo_vista" id="vista_detallada" value="detallado" {{ request('tipo_vista', 'detallado') == 'detallado' ? 'checked' : '' }}>
                    <label class="form-check-label" for="vista_detallada">Detallado</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="tipo_vista" id="vista_resumida" value="resumido" {{ request('tipo_vista') == 'resumido' ? 'checked' : '' }}>
                    <label class="form-check-label" for="vista_resumida">Resumido</label>
                </div>
            </div>
        </div>
    </div>
    <br>

    <div class="row">
        <div class="col-md-6">
            <button type="submit" name="pdf" value="pdf" class="btn btn-primary w-100" formtarget="_blank">Generar Reporte PDF <i class="fa fa-file-pdf"></i>
            </button>
        </div>
        <div class="col-md-6">
            <div class="btn-group w-100" role="group">
                <button id="btnGroupDrop1" type="button" class="btn btn-success dropdown-toggle"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    Exportar a Excel
                </button>
                <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                    <li>
                        <button class="dropdown-item" type="submit" name="excel1" value="excel1">V1</button>
                    </li>
                    <li>
                        <button class="dropdown-item" type="submit" name="excel2" value="excel2">V2 (Detallado)</button>
                    </li>
                    <li>
                        <button class="dropdown-item" type="submit" name="excel3" value="excel2">Cuotas Detalladas</button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
        {{html()->form()->close()}}
        <br>
        <br>
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                        <tr class="table-success">
                            <th>#</th>
                            <th>Consecutivo</th>
                            <th>Cédula</th>
                            <th>Cliente</th>
                            <th>Forma Pago</th>
                            <th>Fecha Abono</th>
                            <th>Agente</th>
                            <th>Abono Capital</th>
                            <th>Abono Interes</th>
                            <th>Total Abonado</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($abonosDia as  $ind=> $abonos)
                            <tr>
                                <td>{{$loop->index+1}}</td>
                                <td>{{$abonos->prestamo->consecutivo}}</td>
                                <td>{{$abonos->prestamo->cliente->cedula}}</td>
                                <td>{{$abonos->prestamo->cliente->full_name}}</td>
                                <td>{{$abonos->prestamo->forma_pago}}</td>
                                <td>{{fecha_d_m_Y_h_i($abonos->created_at)}}</td>
                                <td>{{$abonos->prestamo->agente->full_name}}</td>
                                <td>{{$abonos->total_abonado_capital}}</td>
                                <td>{{$abonos->total_abonado_interes}}</td>
                                <td>{{$abonos->total_abonado}}</td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                    @if(isset($abonosDia) && count($abonosDia))
                        {{$abonosDia->appends(request()->all())->links()}}
                    @endif
                </div>
            </div>
        </div>
@endsection
