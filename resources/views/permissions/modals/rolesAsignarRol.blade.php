<div class="modal fade" id="modalAsignarRole{{encode($rol->id)}}" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {{html()->form('POST',route('roles.asignarRol',encode($rol->id)))->id('frmAsignarRol')->open()}}
            @method('POST')
            <div class="modal-header">
                <h5 class="modal-title">Asignación de Roles</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="modalSelectUsers" class="required">Rol:</label>
                            {{html()->select('user',$admins)->class('form-control select2')->required()->style('width:100%')->id('selectUsers'.encode($rol->id))}}
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <h6 class="text-center fw-bold text-uppercase">Usuarios actualmente con este rol asignado</h6>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <ul class="list-group">
                            @foreach($rol->users as $users)
                                <li class="list-group-item">
                                    {{$loop->index+1}} - {{$users->nombres. " ".$users->apellidos}}
                                    @if($users->id!=1)
                                        <a href="#" class="btn btn-sm btn-danger float-end btnRemoveRol" data-userid="{{encode($users->id)}}" data-roleid="{{encode($rol->id)}}"><i class="fa fa-trash"></i> </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
            {{html()->form()->close()}}
        </div>
    </div>
</div>
