<div class="col-md-12">
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
            <tr class="table-success">
                <th>#</th>
                <th>Nombres</th>
                <th>Apellidos</th>
                <th>Cédula</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
            </thead>
            <tbody>
            @if(isset($user))
            @forelse($user->fiadores as $fiadores)
                <tr>
                    <td>{{$loop->index+1}}</td>
                    <td>{{$fiadores->fiador->nombres}}</td>
                    <td>{{$fiadores->fiador->apellidos}}</td>
                    <td>{{$fiadores->fiador->cedula}}</td>
                    <td>
                        @if($fiadores->fiador->estado==1)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-secondary">Inactivo</span>
                        @endif
                    </td>
                    <td>
                            <a href="{{route('agentes.user.fiador.edit',$fiadores->fiador->id_enc)}}" target="_blank" class="btn btn-sm btn-primary" title="Ver datos del fiador">Editar</a>
{{--                            <a href="#" data-action="{{route('fiador.destroy',$fiadores->id_enc)}}" class="btn btn-sm btn-danger btnEliminarNegocio">Eliminar</a>--}}
                    </td>
                </tr>
            @empty
                <p class="text-center fw-bold">No hay registro de fiadores</p>
            @endforelse
            @endif
            </tbody>
        </table>
    </div>
</div>
