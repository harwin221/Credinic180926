<div class="col-md-12">

    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
            <tr class="table-success">
                <th>#</th>
                <th>Nombre</th>
                <th>Dirección</th>
                <th>Acción</th>
            </tr>
            </thead>
            <tbody>
            @if(isset($user))
            @forelse($user->user_negocios as $negocio)
                <tr>
                    <td>{{$loop->index+1}}</td>
                    <td>{{$negocio->nombre}}</td>
                    <td>{{$negocio->direccion}}</td>
                    <td class="text-center">
                        <x-actionDropdown>
                            @can('Editar Negocios')
                                <li><a class="dropdown-item text-primary" href="{{route('negocio.edit',$negocio->id_enc)}}"><i class="fa fa-edit"></i> Editar</a></li>
                            @endcan
                            @can('Eliminar Negocios')
                                <li><a class="dropdown-item text-danger btnEliminarNegocio" href="#" data-action="{{route('negocio.destroy',$negocio->id_enc)}}"><i class="fa fa-trash"></i> Eliminar</a></li>
                            @endcan
                        </x-actionDropdown>
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
