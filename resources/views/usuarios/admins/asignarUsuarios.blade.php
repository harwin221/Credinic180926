@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('user.index')])
    Asignar Usuarios a [ <span style="color: blue">{{$user->full_name}}</span> ]
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <p>SI EL USUARIO NO POSEE NINGÚN USUARIO ASIGNADO, QUIERE DECIR QUE TIENE LA OPCIÓN DE VERLOS TODOS, EN CASO CONTRARIO SOLO PUEDE VER LOS QUE TIENE ASIGNADOS </p>
        </div>
    </div>
    <br>
    {{html()->form('POST',route('user.admins.asignar',$user->id_enc))->open()}}
    <div class="row">
        <div class="col-md-9">
            <label for=""><b>Asignar Agente: <small>Para efectos de reportes</small></b></label>
            {{html()->select('admins',[''=>'-- Seleccione --']+$admins)->class('form-control select2')->required()}}
        </div>
        <div class="col-md-3">
            <br>
            <button type="submit" class="btn btn-success">Asignar</button>
        </div>
    </div>
    {{html()->form()->close()}}
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
                        <th>Estado</th>
                        <th class="text-center">Acción</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($usuariosAsignados as $user2)
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$user2->asignado->username}}</td>
                            <td>{{$user2->asignado->nombres}}</td>
                            <td>{{$user2->asignado->apellidos}}</td>
                            <td>{{$user2->asignado->estado_user['text']}}</td>
                            <td class="text-center">
                                {{html()->form('POST',route('user.admins.quitarAsignacion',$user2->id))->open()}}
                                @method('DELETE')
                                <a href="javascript:void(0);" class="btnEliminar"><i class="fa fa-trash" style="color: red"></i></a>
                                {{html()->form()->close()}}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{$usuariosAsignados->links()}}
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
                        event.target.closest('form').submit()
                    }
                })
            })
        })
    </script>
@endsection
