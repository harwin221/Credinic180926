@extends('layouts.app')
@section('tituloPagina')
@include('utlisComponents.btnBack',['url'=>route('reportes.index')])
    Relación Prestamo - Cobrador
@endsection
@section('content')
    {{html()->form('GET',route('reportes.asignacionClientes.index'))->open()}}
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
            Aquí solo se muestran los préstamos activos
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                    <tr class="table-success">
                        <th>#</th>
                        <th># Préstamo</th>
                        <th>Cliente</th>
                        <th>Cobrador</th>
                        <th>Monto</th>
                        <th>Monto Financiado</th>
                        <th>Acción</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($prestamos as $prest)
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$prest->consecutivo}}</td>
                            <td>{{$prest->cliente->full_name}}</td>
                            <td>{{$prest->agente->full_name}}</td>
                            <td>{{$prest->moneda." ". number_format($prest->monto_prestamo,2)}}</td>
                            <td>{{$prest->moneda." ". number_format($prest->monto_financiado,2)}}</td>
                            <td class="text-center">
                                <x-actionDropdown>
                                    <li><a class="dropdown-item text-warning" href="#" data-bs-target="#modalUsuarioCobrador{{$prest->id_enc}}" data-bs-toggle="modal"><i class="fa fa-exchange-alt"></i> Cambiar Cobrador</a></li>
                                </x-actionDropdown>
                            </td>

                            <div class="modal fade" id="modalUsuarioCobrador{{$prest->id_enc}}" data-bs-backdrop="static" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        {{html()->form('POST',route('prestamo.actualizar.usuario',$prest->id_enc))->open()}}
                                        <input type="hidden" value="cobrador" name="tipo">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Cambiar Cobrador</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group text-center">
                                                <label for="usuario"><b>Cobrador:</b></label>
                                                {{html()->select('usuario',[''=>'*Seleccione*']+$listaCobradores,encode($prest->agente_id))->class('form-control select2')->style('width:100%')->required()->id('selectUsuario')}}
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                                            <button type="submit" class="btn btn-success">Guardar</button>
                                        </div>
                                        {{html()->form()->close()}}
                                    </div>
                                </div>
                            </div>

                        </tr>
                    @endforeach

                    </tbody>
                </table>
                {{$prestamos->appends(request()->all())->links()}}
            </div>
        </div>
    </div>
@endsection
