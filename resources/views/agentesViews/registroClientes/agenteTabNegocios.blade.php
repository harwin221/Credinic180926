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
                    <td>
                        <a href="{{route('agentes.user.negocios.edit',$negocio->id_enc)}}" class="btn btn-sm btn-primary">Editar</a>
                        {{--<a href="#" data-action="{{route('negocio.destroy',$negocio->id_enc)}}" class="btn btn-sm btn-danger btnEliminarNegocio">Eliminar</a>--}}
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
