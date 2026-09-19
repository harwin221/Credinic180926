@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('configuracion.index')])
    Agentes
@endsection

@section('content')
    <div class="row">
        @can('Crear Agentes')
        <div class="col-sm-12 col-md-6">
            <a href="{{route('user.agentes.create')}}" class="btn btn-primary">Nuevo <i class="fa fa-plus"></i></a>
        </div>
        @endcan
        <div class="col-sm-12 col-md-6">
            {{html()->form('GET',route('user.agentes.index'))->open()}}
            <div class="input-group mb-3">
                <input type="text" name="buscar" value="{{request('buscar')}}" class="form-control" placeholder="Buscar Agente" aria-label="Recipient's username" aria-describedby="button-addon2">
                <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Buscar</button>
            </div>
            {{html()->form()->close()}}
        </div>
    </div>
    <br>
    {{html()->form('post')->class('d-inline')->id('frmEliminar')->open()}}
    @method('DELETE')
    {{html()->form()->close()}}

    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-info">
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Cédula</th>
                        <th class="text-center">Clientes <br> Activos | Todos </th>
                        <th>Estado</th>
                        <th class="text-center">Acción</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($usuarios as $user)
                        @if($user->id!=4)
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$user->username}}</td>
                            <td>{{$user->nombres}}</td>
                            <td>{{$user->apellidos}}</td>
                            <td>{{$user->cedula}}</td>
                            <td class="text-center">{{count($user->clientesAsignados->where('estado',1))}} | {{count($user->clientesAsignados)}}</td>
                            <td>
                                @if($user->estado==1)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <x-actionDropdown>
                                    @can('Editar Agentes')
                                        <li><a class="dropdown-item text-primary" href="{{route('user.agentes.edit',$user->id_enc)}}"><i class="fa fa-edit"></i> Editar</a></li>
                                    @endcan
                                    <li><a class="dropdown-item text-info" href="{{route('user.agentes.asignados',$user->id_enc)}}"><i class="fa fa-users"></i> Préstamos Asignados</a></li>
                                    @can('Eliminar Agentes')
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger btnEliminar" href="#" data-target="{{route('user.agentes.destroy',$user->id_enc)}}"><i class="fa fa-trash"></i> Eliminar</a></li>
                                    @endcan
                                </x-actionDropdown>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
                {{$usuarios->links()}}
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        const btnEliminar = document.querySelectorAll('.btnEliminar');
        btnEliminar.forEach(button =>{
            button.addEventListener("click",(event)=>{
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
