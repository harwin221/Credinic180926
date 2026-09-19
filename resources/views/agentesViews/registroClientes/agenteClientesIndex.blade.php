@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    Lista de Clientes a Aplicar Solicitudes
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-6">
            <a href="{{route('agentes.user.clientes.create')}}" class="btn btn-primary">Nuevo Cliente <i class="fa fa-plus"></i></a>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-sm-8">
            <label for="buscarCliente"><b>Buscar:</b></label>
            <div class="input-group mb-3">
                <input type="text" id="buscarCliente" class="form-control" placeholder="Buscar por nombre, apellido o cédula..." autocomplete="off">
                <span class="input-group-text" id="loadingSpinner" style="display: none;">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                </span>
            </div>
            <small class="text-muted" id="resultadosContador"></small>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="estado"><b>Filtrar por Estado</b></label>
                {{html()->select('estado',['1'=>'Pendiente','3'=>'Rechazado','2'=>'Aprobado','4'=>'Todos'],request('estado')?request('estado'):4)->class('form-control')->id('filtroEstado')}}
            </div>
        </div>
    </div>

    <br>
    {{html()->form('post')->class('d-inline')->id('frmEliminar')->open()}}
    @method('DELETE')
    {{html()->form()->close()}}

    <div class="d-flex justify-content-center flex-row flex-wrap">
        <button type="button" class="btn btn-md m-2 btn-warning" data-bs-target="#modelId" data-bs-toggle="modal">Solicitudes Pendientes <span class="badge bg-secondary">{{$solicitudesPendientes->count()}}</span></button>

        <!-- Modal -->
        <div class="modal fade" id="modelId" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
             aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Solicitudes Pendientes</h5>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped" style="font-size: 12px">
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>Monto</th>
                                    <th>Monto Financiado</th>
                                    <th>Fecha</th>
                                    <th>Observaciones</th>
                                    <th>Dirección</th>
                                    <th>Acción</th>
                                </tr>
                                @foreach($solicitudesPendientes as $prestamo)
                                    <tr>
                                        <td>{{$loop->index+1}}</td>
                                        <td>{{$prestamo->cliente->full_name}}</td>
                                        <td>{{$prestamo->moneda." ". number_format($prestamo->monto_prestamo,2)}}</td>
                                        <td>{{$prestamo->moneda." ". number_format($prestamo->monto_financiado,2)}}</td>
                                        <td>{{fecha_d_m_Y($prestamo->created_at)}}</td>
                                        <td>{{$prestamo->observaciones}}</td>
                                        <td>{{$prestamo->cliente->direccion}}</td>
                                        <td><a href="{{route('agentes.user.clientes.edit',$prestamo->cliente->id_enc)}}" target="_blank" class="btn btn-sm btn-primary">Ver</a></td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" class="btn btn-md m-2 btn-success">Solicitudes Aprobadas (hoy) <span class="badge bg-primary">{{$solicitudesAprobadas}}</span></button>
        <button type="button" class="btn btn-md m-2 btn-danger">Solicitudes Rechazadas (hoy) <span class="badge bg-primary">{{$solicitudesRechazadas}}</span></button>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12 text-center">
            <h6 style="color: blue"><b><u>Aqui se encuentra el listado de los clientes a los que pueden realizar solicitudes</u></b></h6>
        </div>
    </div>
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
                        <th>Creado Por</th>
                        <th>Estado</th>
                        <th class="text-center">Solicitudes <br>Todos | Activos</th>
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
                            <td>{{$user->user_create->username}}</td>
                            <td>
                                <span class="badge {{$user->estado_user['color']}}">{{$user->estado_user['text']}}</span>
                            </td>
                            <td class="text-center">{{count($user->prestamos->where('desembolsado',0)->where('estado_aprobacion',1))}} | {{count($user->prestamos->where('desembolsado',1)->where('estado_aprobacion',2))}}</td>
                            <td class="text-center">
                                <x-actionDropdown>
                                    <li><a class="dropdown-item text-primary" href="{{route('agentes.user.clientes.edit',$user->id_enc)}}"><i class="fa fa-edit"></i> Editar</a></li>
                                    @if($user->estado==3)
                                        <li><a class="dropdown-item text-danger btnEliminar" href="#" data-target="{{route('agentes.user.clientes.destroy',$user->id_enc)}}"><i class="fa fa-trash"></i> Eliminar</a></li>
                                    @endif
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
@endsection
@section('script')
    <script>
        let debounceTimer;
        const buscarInput = document.getElementById('buscarCliente');
        const filtroEstado = document.getElementById('filtroEstado');
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

        // Filtro por estado
        filtroEstado.addEventListener('change', function() {
            realizarBusqueda(1);
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
                const estado = filtroEstado.value;
                
                const response = await axios.get('{{route('agentes.user.clientes.index')}}', {
                    params: {
                        buscar: buscar,
                        estado: estado,
                        page: page
                    }
                });
                
                // Crear un elemento temporal para parsear el HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(response.data, 'text/html');
                
                // Extraer solo la tabla y paginación
                const nuevaTabla = doc.querySelector('#tablaClientesContainer');
                
                if (nuevaTabla) {
                    tablaContainer.innerHTML = nuevaTabla.innerHTML;
                    
                    // Contar resultados
                    const filas = nuevaTabla.querySelectorAll('tbody tr');
                    if (buscar) {
                        resultadosContador.textContent = `${filas.length} resultado(s) encontrado(s)`;
                    } else {
                        resultadosContador.textContent = '';
                    }
                    
                    // Reinicializar event listeners para botones eliminar
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
