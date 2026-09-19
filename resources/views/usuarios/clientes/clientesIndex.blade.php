@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('home')])
    Listado de Clientes
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-md-12">
            @can('Crear Clientes')
                <a href="{{route('user.clientes.create')}}" class="btn btn-sm btn-primary">Nuevo Cliente <i class="fa fa-plus"></i></a>
            @endcan
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-4">
            <label for="buscarCliente"><b>Buscar:</b></label>
            <div class="input-group mb-2">
                <input type="text" id="buscarCliente" class="form-control" placeholder="Buscar por nombre, apellido o cédula..." autocomplete="off">
                <span class="input-group-text" id="loadingSpinner" style="display: none;">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                </span>
            </div>
            <small class="text-muted" id="resultadosContador"></small>
        </div>
    </div>

    @can('Eliminar Clientes')
        {{html()->form('post')->class('d-inline')->id('frmEliminar')->open()}}
        @method('DELETE')
        {{html()->form()->close()}}
    @endcan

    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive" id="tablaClientesContainer">
                <table class="table table-bordered table-sm table-striped" style="font-size: 14px">
                    <thead class="table-info">
                    <tr>
                        <th>#</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Teléfonos</th>
                        <th>Cédula</th>
                        <th>Estado</th>
                        <th class="text-center">Préstamos <br>Todos | Activos</th>
                        <th class="text-center">Acción</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($usuarios as $user)
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$user->nombres}}</td>
                            <td>{{$user->apellidos}}</td>
                            <td>{{$user->telefono1}} / {{$user->telefono2}}</td>
                            <td>{{$user->cedula}}</td>
                            <td>
                                @if($user->estado==1)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center">{{count($user->prestamos)}} | {{count($user->prestamos->where('estado',1))}}</td>
                            <td class="text-center">
                                @can('Editar Clientes')
                                    <a href="{{route('user.clientes.edit',$user->id_enc)}}" class="btn btn-sm btn-primary" title="Editar">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                @endcan
                                @can('Eliminar Clientes')
                                    <button type="button" class="btn btn-sm btn-danger btnEliminar" data-target="{{route('user.clientes.destroy',$user->id_enc)}}" title="Eliminar">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
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
        let debounceTimer;
        const buscarInput = document.getElementById('buscarCliente');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const resultadosContador = document.getElementById('resultadosContador');
        const tablaContainer = document.getElementById('tablaClientesContainer');

        // Búsqueda en vivo con debounce
        buscarInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            loadingSpinner.style.display = 'inline-block';
            
            debounceTimer = setTimeout(() => {
                realizarBusqueda(1);
            }, 500);
        });

        // Paginación con delegación de eventos
        document.addEventListener('click', function(e) {
            if (e.target.closest('.pagination a')) {
                e.preventDefault();
                const url = e.target.closest('.pagination a').getAttribute('href');
                const page = new URL(url).searchParams.get('page');
                realizarBusqueda(page);
            }
        });

        async function realizarBusqueda(page = 1) {
            try {
                loadingSpinner.style.display = 'inline-block';
                
                const buscar = buscarInput.value;
                
                const response = await axios.get('{{route('user.clientes.index')}}', {
                    params: {
                        buscar: buscar,
                        page: page
                    }
                });
                
                const parser = new DOMParser();
                const doc = parser.parseFromString(response.data, 'text/html');
                const nuevaTabla = doc.querySelector('#tablaClientesContainer');
                
                if (nuevaTabla) {
                    tablaContainer.innerHTML = nuevaTabla.innerHTML;
                    
                    const filas = nuevaTabla.querySelectorAll('tbody tr');
                    if (buscar) {
                        resultadosContador.textContent = `${filas.length} resultado(s) encontrado(s)`;
                    } else {
                        resultadosContador.textContent = '';
                    }
                    
                    inicializarBotonesEliminar();
                }
                
                loadingSpinner.style.display = 'none';
            } catch (error) {
                console.error('Error en la búsqueda:', error);
                loadingSpinner.style.display = 'none';
                Swal.fire('Error', 'No se pudo realizar la búsqueda', 'error');
            }
        }

        function inicializarBotonesEliminar() {
            const btnEliminar = document.querySelectorAll('.btnEliminar');
            btnEliminar.forEach(button => {
                button.addEventListener("click", (event) => {
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
                            let action = event.target.closest('.btnEliminar').dataset.target;
                            let form = document.getElementById('frmEliminar');
                            form.action = action;
                            form.submit();
                        }
                    })
                })
            })
        }

        // Inicializar botones al cargar la página
        inicializarBotonesEliminar();
    </script>
@endsection
