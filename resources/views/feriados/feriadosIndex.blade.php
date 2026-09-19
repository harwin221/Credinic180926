@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('configuracion.index')])
    Lista de Feriados
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalFeriado">Agregar Fecha <i class="fa fa-plus"></i></a>

            <div class="modal fade" id="modalFeriado" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        {{html()->form('POST',route('configuracion.feriados.storeFeriados'))->id('frmAbono')->open()}}
                        <div class="modal-header">
                            <h5 class="modal-title">Establecer Feriados</h5>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="fecha"><b>Fecha:</b></label>
                                        <input type="date" class="form-control" name="fecha" id="fecha" required>
                                    </div>
                                    <br>
                                    <div class="form-group">
                                        <label for="nombre"><b>Nombre:</b></label>
                                        <input type="text" class="form-control" name="nombre" id="nombre" required>
                                    </div>
                                    <br>
                                    <div class="form-group text-center">
                                        <label for="nombre"><b>Tipo:</b></label>
                                    </div>

                                    <div class="form-group">
                                        <input type="radio" value="1" name="tipo" id="repite" required>
                                        <label for="repite">Se repite todos los años</label>
                                    </div>

                                    <div class="form-group">
                                        <input type="radio" value="2" name="tipo" id="no_repite" required checked>
                                        <label for="no_repite">Solo aplica el año seleccionado</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                        {{html()->form()->close()}}
                    </div>
                </div>
            </div>

        </div>
    </div>
    <br>

    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <h6><i class="fa fa-info-circle"></i> <strong>Información sobre Tipos de Feriados:</strong></h6>
                <ul class="mb-0">
                    <li><strong>Recurrente:</strong> El feriado se aplicará automáticamente cada año en la misma fecha (ej: Navidad, Año Nuevo, Día de la Independencia).</li>
                    <li><strong>No recurrente:</strong> El feriado solo se aplicará para el año específico de la fecha ingresada (ej: días festivos especiales de un año en particular).</li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <b>Las fechas ingresadas aquí no se tomarán en cuenta para la distribución de las fechas de cuotas</b>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                    <tr class="table-info">
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Acción</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($feriados as $f)
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{fecha_d_m_Y($f->fecha)}}</td>
                            <td>{{$f->nombre}}</td>
                            <td>
                                @if($f->tipo == 1)
                                    <span class="badge bg-success"><i class="fa fa-sync"></i> Recurrente</span>
                                @else
                                    <span class="badge bg-info"><i class="fa fa-calendar"></i> No recurrente</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <x-actionDropdown>
                                    <li><a class="dropdown-item text-primary" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modalFeriado{{$f->id_enc}}"><i class="fa fa-edit"></i> Editar</a></li>
                                    <li>
                                        {{html()->form('post',route('configuracion.feriados.destroyFeriados',$f->id_enc))->class('d-inline')->open()}}
                                        @method('DELETE')
                                        <a href="javascript:void(0)" class="dropdown-item text-danger btnEliminar"><i class="fa fa-trash"></i> Eliminar</a>
                                        {{html()->form()->close()}}
                                    </li>
                                </x-actionDropdown>
                            </td>


                            <div class="modal fade" id="modalFeriado{{$f->id_enc}}" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
                                <div class="modal-dialog modal-md" role="document">
                                    <div class="modal-content">
                                        {{html()->modelForm($f,'POST',route('configuracion.feriados.updateFeriados',$f->id_enc))->open()}}
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Realizar Abono</h5>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="fecha"><b>Fecha:</b></label>
                                                        <input type="date" value="{{$f->fecha}}" class="form-control" name="fecha" id="fecha" required>
                                                    </div>
                                                    <br>
                                                    <div class="form-group">
                                                        <label for="nombre"><b>Nombre:</b></label>
                                                        <input type="text" value="{{$f->nombre}}" class="form-control" name="nombre" id="nombre" required>
                                                    </div>
                                                    <br>
                                                    <div class="form-group text-center">
                                                        <label for="nombre"><b>Tipo:</b></label>
                                                    </div>

                                                    <div class="form-group">
                                                        <input type="radio" @if($f->tipo==1) checked @endif value="1" name="tipo" required>
                                                        <label for="repite">Se repite todos los años</label>
                                                    </div>

                                                    <div class="form-group">
                                                        <input type="radio" @if($f->tipo==2) checked @endif value="2" name="tipo" required>
                                                        <label for="no_repite">Solo aplica el año seleccionado</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                        </div>
                                        {{html()->form()->close()}}
                                    </div>
                                </div>
                            </div>

                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.querySelectorAll('.btnEliminar').forEach((button)=>{
            button.addEventListener('click', (event) => {
                Swal.fire({
                    title: 'Eliminar Registro',
                    text: "¿Está seguro que desea eliminar el registro?",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#3085d6',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        event.target.closest('form').submit()
                    }
                })
            })
        })
    </script>
@endsection
