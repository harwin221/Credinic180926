@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('agentes.registroClientes')])
    Editar Cliente
@endsection

@section('content')



    {{html()->modelForm($user,'POST',route('agentes.updateNuevoCliente',$user->id_enc))->acceptsFiles()->open()}}
    @method('PUT')
    @include('agentesViews.clientes.agenteFormClientes')
    <br>
    <br>
        <div class="row justify-content-center">
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Guardar datos del cliente <i class="fa fa-save"></i> </button>
            </div>
        </div>
    {{html()->form()->close()}}
    <br>
@if(isset($user))
        {{html()->form('post')->class('d-inline')->id('frmEliminar')->open()}}
    @method('DELETE')
    {{html()->form()->close()}}

    <div class="row">
        <div class="col-md-12">
            <a href="#" data-bs-toggle="modal" data-bs-target="#modelId" class="btn btn-sm btn-primary">Nueva Solicitud</a>

            <!-- Modal -->
            <div class="modal fade" id="modelId" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
                 aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        {{html()->form('POST',route('agentes.nuevaSolicitud',$user->id_enc))->open()}}
                        <div class="modal-header">
                            <h5 class="modal-title">Nueva Solicitud</h5>
                        </div>
                        <div class="modal-body">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="monto"><b>Monto Solicitado:</b></label>
                                    <input type="number" step="0.01" required class="form-control" name="monto_solicitado" id="monto">
                                </div>
                                <br>
                                <div class="form-group">
                                    <label for="moneda"><b>Moneda:</b></label>
                                    {{html()->select('moneda',[''=>'** Seleccione **','1'=>'C$','2'=>'U$'])->class('form-control')->required()->id('moneda')}}
                                </div>
                                <br>
                                <div class="form-group">
                                    <label for="observaciones"><b>Observaciones:</b></label>
                                    <textarea class="form-control" id="observaciones" rows="2"></textarea>
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-sm">
                    <tr class="table-info">
                        <th>#</th>
                        <th>Fecha Solicitud</th>
                        <th>Moneda</th>
                        <th>Monto Solicitado</th>
                        <th>Estado Solicitud</th>
                        <th>Observaciones</th>
                        <th>Acción</th>
                    </tr>
                    @foreach($user->solicitudes as $sol)
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{fecha_d_m_Y($sol->created_at)}}</td>
                            <td>{{$sol->moneda_solicitud}}</td>
                            <td>{{number_format($sol->monto_solicitado,2)}}</td>
                            <td>{{$sol->estado_solicitud}}</td>
                            <td>{{$sol->observaciones}}</td>
                            <td>
                                @if($sol->estado==1)
                                    <a href="#" data-target="{{route('agentes.destroySolicitud',$sol->id_enc)}}" class="btn btn-sm btn-danger btnEliminar">Eliminar</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>

@endif

@endsection
@section('script')
    <script>
        const btnEliminar = document.querySelectorAll('.btnEliminar');
        const frmEliminar = document.getElementById('frmEliminar');

        btnEliminar.forEach(button =>{
            button.addEventListener("click",(event)=>{
                console.log(frmEliminar)
                Swal.fire({
                    title: '¿Está seguro?',
                    text: "¿Desea eliminar al usuario?",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#3085d6',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let action = event.target.dataset.target;
                        let form = document.getElementById('frmEliminar');
                        form.action = action;
                        form.submit();
                    }
                })
            })
        })
    </script>
@endsection

