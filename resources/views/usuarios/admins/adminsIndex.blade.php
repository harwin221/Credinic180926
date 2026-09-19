@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('configuracion.index')])
    Administrativos
@endsection

@section('content')
    @can('Crear Administrativos')
        <div class="row">
            <div class="col-md-12">
                <a href="{{route('user.create')}}" class="btn btn-primary">Nuevo <i class="fa fa-plus"></i></a>
            </div>
        </div>
    @endcan
    <br>


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
                        <th>Sucursal</th>
                        <th>Estado</th>
                        <th class="text-center">Acción</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($usuarios as $user)
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$user->username}}</td>
                            <td>{{$user->nombres}}</td>
                            <td>{{$user->apellidos}}</td>
                            <td>{{$user->cedula}}</td>
                            <td>
                                @if($user->sucursal)
                                    <span class="badge bg-info text-dark">
                                        <i class="fa fa-building"></i> {{ $user->sucursal->nombre }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($user->estado==1)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <x-actionDropdown>
                                    @can('Editar Administrativos')
                                        <li><a class="dropdown-item text-primary" href="{{route('user.edit',$user->id_enc)}}"><i class="fa fa-edit"></i> Editar</a></li>
                                    @endcan
                                    @can('Asignar Agentes a Administrativos')
                                        <li><a class="dropdown-item text-success" href="{{route('user.admins.asignados',$user->id_enc)}}"><i class="fa fa-user-plus"></i> Asignar Usuarios ({{count($user->agentesAsignado)}})</a></li>
                                    @endcan
                                    @can('Eliminar Administrativos')
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger btnEliminar" href="#" data-target="{{route('user.destroy',$user->id_enc)}}"><i class="fa fa-trash"></i> Eliminar</a></li>
                                    @endcan
                                </x-actionDropdown>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{$usuarios->links()}}
            </div>
        </div>
    </div>
    @can('Eliminar Administrativos')
        {{html()->form('post')->class('d-inline')->id('frmEliminar')->open()}}
        @method('DELETE')
        {{html()->form()->close()}}
    @endcan
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
