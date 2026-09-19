@extends('layouts.app')

@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('roles.index')])
    Permisos
@endsection

@section('content')
    @can('Crear Permisos')
        <div class="row">
            <div class="col-md-12">
                <a href="#" class="btn btn-primary" id="btnNuevo">Nuevo Permiso <i class="fa fa-plus"></i> </a>
            </div>
        </div>
    @endcan
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table tabla table-bordered table-sm">
                    <thead>
                    <tr class="table-success">
                        <th style="width: 3%">#</th>
                        <th>Permiso</th>
                        <th>Descripción</th>
                        <th style="width: 15%">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($permisos as $perm)
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$perm->name}}</td>
                            <td>{{$perm->description}}</td>
                            <td class="text-center">
                                <x-actionDropdown>
                                    @can('Editar Permisos')
                                        <li><a class="dropdown-item text-primary btnActualizar" href="#" data-id="{{encode($perm->id)}}"><i class="fa fa-edit"></i> Editar</a></li>
                                    @endcan
                                    @can('Eliminar Permisos')
                                        <li><a class="dropdown-item text-danger btnEliminar" href="#" data-id="{{encode($perm->id)}}"><i class="fa fa-trash"></i> Eliminar</a></li>
                                    @endcan
                                </x-actionDropdown>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{$permisos->links()}}
            </div>
        </div>
    </div>

        @include('permissions.modals.permisosModal')

    @can('Eliminar Permisos')
        {{html()->form('post')->id('frmDelete')->open()}}
        @method('DELETE')
        {{html()->form()->close()}}
    @endcan

@endsection


@section('script')
    <script>
        document.getElementById('btnNuevo').addEventListener('click',crearPermiso);
        document.querySelectorAll('.btnActualizar').forEach(button=>{
            button.addEventListener('click',editarPermiso)
        })
        document.querySelectorAll('.btnEliminar').forEach(button=>{
            button.addEventListener('click',eliminarPermiso)
        })
        const frmPermiso = document.getElementById('frmPermiso')
        const modalPermisos = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPermisos'))
        const method = document.querySelector("[name=_method]");

        function limpiarControles(){
            document.getElementById('name').value = '';
            document.getElementById('description').value = '';
        }

        function crearPermiso(event) {
            limpiarControles()
            frmPermiso.action = '{{route('permisos.store')}}'
            method.value = 'POST'
            modalPermisos.show()
        }

        async function editarPermiso(event) {
            limpiarControles()

            let roleId = null
            if(event.target.classList.contains('btnActualizar'))
                roleId = event.target.dataset.id
            else
                roleId = event.target.closest('a','.btbActualizar').dataset.id

            let url = '{{route('permisos.update','?')}}'
            url = url.replace('?', roleId)

            let urlGet = '{{route('permisos.getPermiso','?')}}'
            urlGet = urlGet.replace('?', roleId)

            let {data} = await axios.get(urlGet)
            document.getElementById('name').value = data.name
            document.getElementById('description').value = data.description

            frmPermiso.action = url
            method.value = 'PUT'
            modalPermisos.show()

        }

        function eliminarPermiso(event){

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

                    let url = '{{route('permisos.destroy','?')}}'
                    url=url.replace('?',roleId)

                    document.getElementById('frmDelete').action = url
                    document.getElementById('frmDelete') .submit();
                }
            })
        }
    </script>
@endsection
