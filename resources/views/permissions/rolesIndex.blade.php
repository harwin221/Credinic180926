@extends('layouts.app')

@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('configuracion.index')])
    Roles
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            @can('Crear Roles')
                <a href="#" class="btn btn-primary" id="btnNuevo">Nuevo Rol <i class="fa fa-plus"></i> </a>
            @endcan
            @can('Crear Permisos')
                <a href="{{route('permisos.index')}}" class="btn btn-warning">Permisos <i class="fa fa-plus"></i></a>
            @endcan
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table tabla table-bordered table-sm">
                    <thead>
                    <tr class="table-success">
                        <th style="width: 3%">#</th>
                        <th>Rol</th>
                        <th style="width: 15%">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($roles as $rol)
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$rol->name}}</td>
                            <td class="text-center">
                                @can('Editar Roles')
                                    <a class="btn btn-sm btn-primary btnActualizar" href="#" data-id="{{encode($rol->id)}}" title="Editar">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                @endcan
                                @can('Ver Permisos')
                                    <a class="btn btn-sm btn-warning" href="{{route('roles.detallePermisos',encode($rol->id))}}" title="Ver Permisos">
                                        <i class="fa fa-list"></i>
                                    </a>
                                @endcan
                                @can('Asignar Roles')
                                    <a class="btn btn-sm btn-success" href="#" data-bs-toggle="modal" data-bs-target="#modalAsignarRole{{encode($rol->id)}}" title="Asignar a Usuarios">
                                        <i class="fa fa-user"></i>
                                    </a>
                                    @include('permissions.modals.rolesAsignarRol')
                                @endcan
                                @can('Eliminar Roles')
                                    @if(mb_strtolower($rol->name)!='administrador')
                                        <a class="btn btn-sm btn-danger btnEliminar" href="#" data-id="{{encode($rol->id)}}" title="Eliminar">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{$roles->links()}}
            </div>
        </div>
    </div>

    @include('permissions.modals.rolesModal')
    @can('Eliminar Roles')
    {{html()->form('post')->id('frmDelete')->open()}}
    @method('DELETE')
    {{html()->form()->close()}}
    @endcan
@endsection


@section('script')
    <script>

        document.getElementById('btnNuevo').addEventListener('click',crearRol);
        document.querySelectorAll('.btnActualizar').forEach(button=>{
            button.addEventListener('click',editarRol)
        })
        document.querySelectorAll('.btnEliminar').forEach(button=>{
            button.addEventListener('click',eliminarRol)
        })

        // document.querySelectorAll('.btnAsignar').forEach(button=>{
        //     button.addEventListener('click',asignarRol)
        // })

        document.querySelectorAll('.btnRemoveRol').forEach(button=>{
            button.addEventListener('click',removeRol)
        })

        const frmRol = document.getElementById('frmRol')
        const modalDocumentos = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRole'))
        // const modalAsignarRoles = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAsignarRole'))
        const method = document.querySelector("[name=_method]");

        function limpiarControles(){
            document.getElementById('name').value = '';
        }

        function crearRol(event) {
            limpiarControles()
            frmRol.action = '{{route('roles.store')}}'
            method.value = 'POST'
            modalDocumentos.show()
        }

        async function editarRol(event) {
            limpiarControles()

            let roleId = null
            if(event.target.classList.contains('btnActualizar'))
                roleId = event.target.dataset.id
            else
                roleId = event.target.closest('a','.btbActualizar').dataset.id

            let url = '{{route('roles.update','?')}}'
            url = url.replace('?', roleId)

            let urlGet = '{{route('roles.getRol','?')}}'
            urlGet = urlGet.replace('?', roleId)

            let {data} = await axios.get(urlGet)
            document.getElementById('name').value = data.name

            frmRol.action = url
            method.value = 'PUT'
            modalDocumentos.show()

        }

        function eliminarRol(event){
            let roleId = null
            if (event.target.classList.contains('btnEliminar'))
                roleId = event.target.dataset.id
            else
                roleId = event.target.closest('a', '.btnEliminar').dataset.id

            Swal.fire({
                title: '¿Esta seguro?',
                text: "¿Esta seguro que desea eliminar el item seleccionado?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {

                    let url = '{{route('roles.destroy','?')}}'
                    url=url.replace('?',roleId)

                    document.getElementById('frmDelete').action = url
                    document.getElementById('frmDelete') .submit();
                }
            })

        }

        {{--async function asignarRol(event){--}}
        {{--    $("#modalSelectUsers").empty()--}}
        {{--    let roleId = null--}}
        {{--    if (event.target.classList.contains('btnAsignar'))--}}
        {{--        roleId = event.target.dataset.id--}}
        {{--    else--}}
        {{--        roleId = event.target.closest('a', '.btnAsignar').dataset.id--}}

        {{--    const {data} = await axios.get('{{route('user.getListAdministrativos')}}')--}}

        {{--    for (let key in data) {--}}
        {{--        let opcion = `<option value="${key}"> ${data[key]} </option>`--}}

        {{--        $("#modalSelectUsers").append(opcion)--}}
        {{--    }--}}

        {{--    let url = '{{route('roles.asignarRol','?')}}'--}}
        {{--    url = url.replace('?', roleId)--}}

        {{--    document.getElementById('frmAsignarRol').action = url--}}

        {{--    modalAsignarRoles.show()--}}

        {{--    $('#modalSelectUsers').select2({--}}
        {{--        dropdownParent: $('#modalAsignarRole')--}}
        {{--    });--}}
        {{--}--}}

        function removeRol(event) {
            let userId = null
            let roleId = null
            if (event.target.classList.contains('.btnRemoveRol')) {
                roleId = event.target.dataset.roleid
                userId = event.target.dataset.userId
            } else {
                roleId = event.target.closest('a', '.btnRemoveRol').dataset.roleid
                userId = event.target.closest('a', '.btnRemoveRol').dataset.userid
            }

            Swal.fire({
                title: '¿Esta seguro?',
                text: "¿Esta seguro que desea quitar la asignación del rol al usuario seleccionado?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {

                    let url = '{{route('roles.desAsignarRol',['?','X'])}}'
                    url = url.replace('?', roleId)
                    url = url.replace('X', userId)

                    window.location = url
                }
            })
        }


    </script>
@endsection

