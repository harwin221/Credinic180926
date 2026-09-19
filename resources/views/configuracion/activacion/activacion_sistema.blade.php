@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('configuracion.index')])
    Horas de Activación
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <table class="table table-sm tabla">
                <thead class="table-info">
                <tr>
                    <th>#</th>
                    <th>Sistema</th>
                    <th>Hora Inicio</th>
                    <th>Hora Fin</th>
                    <th></th>
                </tr>
                </thead>

                <tbody>
                @foreach($configuraciones as $config)
                    <tr>
                        <td>{{$loop->index+1}}</td>
                        <td>{{$config->sistema}}</td>
                        <td>{{date('h:i a',strtotime($config->hora_inicio_activacion))}}</td>
                        <td>{{date('h:i a',strtotime($config->hora_fin_activacion))}}</td>
                        <td>
                            <a href="#" data-bs-target="#config{{$config->id}}" data-bs-toggle="modal"><i class="fa fa-edit"></i></a>

                            <div class="modal fade" data-bs-backdrop="static" id="config{{$config->id}}" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        {{html()->form('POST',route('configuracion.configuracion.updactivacion_sistema',$config->id))->open()}}
                                        <div class="modal-header">
                                            <h5 class="modal-title">Editar Tiempos</h5>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for=""><b>Sistema:</b></label>
                                                {{html()->text('sistema',$config->sistema)->class('form-control')->disabled()}}
                                            </div>

                                            <div class="form-group">
                                                <label for=""><b>Inicio Activación:</b></label>
                                                {{html()->time('hora_inicio_activacion',$config->hora_inicio_activacion)->class('form-control')}}
                                            </div>

                                            <div class="form-group">
                                                <label for=""><b>Fin Activación:</b></label>
                                                {{html()->time('hora_fin_activacion',$config->hora_fin_activacion)->class('form-control')}}
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                        </div>
                                        {{html()->form()->close()}}
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
