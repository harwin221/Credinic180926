@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('home')])
    Desembolsos
@endsection
@section('content')
    <div class="row mb-3">
        <div class="col-md-12">
            @can('Crear Desembolsos')
                <a href="{{route('prestamos.create')}}" class="btn btn-sm btn-primary">Nuevo Desembolso <i class="fa fa-plus"></i></a>
            @endcan
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-4">
                    <label for="buscarDesembolso"><b>Buscar:</b></label>
                    <div class="input-group mb-2">
                        <input type="text" id="buscarDesembolso" class="form-control" placeholder="Buscar por cliente, número de préstamo..." autocomplete="off">
                        <span class="input-group-text" id="loadingSpinner" style="display: none;">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </span>
                    </div>
                    <small class="text-muted" id="resultadosContador"></small>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="clasificacion"><b>Clasificación:</b></label>
                        {{html()->select('clasificacion',[''=>'-- Todos --','1'=>'Clasificados'],request()->get('clasificacion'))->class('form-control')->id('clasificacion')}}
                    </div>
                </div>
            </div>

            <!-- Leyenda en una sola línea completa -->
            <div class="mt-2 mb-3" style="font-size: 0.8rem; color: #495057;">
                <i class="fas fa-info-circle me-1" style="color: #1f9cb5;"></i>
                <strong>N:</strong> Nuevo |
                <strong>R:</strong> Represtamo |
                <strong>REAC:</strong> Reactivación |
                <strong>REEST:</strong> Reestructuración |
                <span class="badge bg-danger" style="font-size: 0.7rem;">PENDIENTE DESEMBOLSO</span>
                <span class="mx-2">•</span>
                <span class="badge" style="background-color: rgba(88,139,216,0.47); color: #000; font-size: 0.7rem;">A: Normal</span>
                <span class="badge" style="background-color: #96d858; color: #000; font-size: 0.7rem;">B: Potencial</span>
                <span class="badge" style="background-color: #d8d358; color: #000; font-size: 0.7rem;">C: Real</span>
                <span class="badge" style="background-color: #d3a065; color: #000; font-size: 0.7rem;">D: Dudosa</span>
                <span class="badge" style="background-color: #d36565; color: #fff; font-size: 0.7rem;">E: Irrecuperable</span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive" id="tablaDesembolsosContainer">
                <table class="table table-stripped table-sm" style="font-size: 14px">
                    <thead class="table-info">
                    <tr>
                        <th></th>
                        <th>N° Préstamo</th>
                        <th>Tipo</th>
                        <th>Frecuencia</th>
                        <th>Cliente</th>
                        <th>Monto</th>
                        <th>Fecha Creado</th>
                        <th>Fecha de Vencimiento</th>
                        <th>Agente Asignado</th>
                        <th>Estado</th>
                        <th>Entregado</th>
                        <th>Acción</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($prestamos as $prestamo)
                        <tr @if($prestamo->desembolsado==0 && in_array($prestamo->estado,[1,3])) class="table-danger" @endif>
                            <td style="background: {{$prestamo->dias_atraso['color']}}">
                                {{$prestamo->dias_atraso['letra']}}
                            </td>
                            <td>
                                {{$prestamo->consecutivo}}
                                @if($prestamo->represtamo)
                                    <a href="javascript:void(0)" class="btn btn-sm btn-info"><i class="fa fa-r text-white" title="Represtamo"></i></a>
                                @endif
                            </td>
                            <td>{{$prestamo->tipo_prestamo_abrev}}</td>
                            <td>{{$prestamo->forma_pago}}</td>
                            <td>{{$prestamo->cliente->full_name}}</td>
                            <td>{{$prestamo->moneda}} {{number_format($prestamo->monto_prestamo,2)}}</td>
                            <td>{{fecha_d_m_Y_h_i($prestamo->created_at)}}</td>
                            <td>{{$prestamo->cuotas()->orderBy('id','desc')->first() ? fecha_d_m_Y($prestamo->cuotas()->orderBy('id','desc')->first()->fecha_cuota) : '-'}}</td>
                            <td>{{$prestamo->agente ? $prestamo->agente->username : 'N-D'}}</td>
                            <td><span class="badge @if($prestamo->estado==1) bg-success @elseif($prestamo->estado == 4) bg-danger @else bg-primary @endif">
                                    {{$prestamo->estado_prestamo}}
                                </span>
                            </td>
                            <td class="text-center">
                                <span>@if($prestamo->desembolsado==1) <i class="fa fa-check" style="color: green"></i>@else <i class="fa fa-times" style="color: red"></i> @endif</span>
                            </td>
                            <td class="text-center">
                                @can('Ver Desembolsos')
                                    <a class="btn btn-sm btn-primary"
                                       href="{{route('prestamos.show', $prestamo->id_enc)}}"><i class="fa fa-eye"></i>
                                        Ver</a>
                                @endcan
                                @can('Anular Desembolsos')
                                    @if($prestamo->estado != 4)
                                        <a class="btn btn-sm btn-danger btnEliminar" href="#"
                                           data-id="{{$prestamo->id_enc}}"><i class="fa fa-ban"></i> Anular</a>
                                    @endif
                                @endcan
                                @can('Eliminar Desembolsos')
                                    <a class="btn btn-sm btn-dark btnEliminarFisico" href="#"
                                       data-id="{{$prestamo->id_enc}}"><i class="fa fa-trash"></i> Eliminar</a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{$prestamos->links()}}
            </div>
        </div>
    </div>
    @can('Anular Desembolsos')
        {{html()->form('POST')->id('frmEliminarPrestamo')->open()}}
        @method('DELETE')
        {{html()->form()->close()}}
    @endcan
    @can('Eliminar Desembolsos')
        {{html()->form('POST')->id('frmEliminarFisicoPrestamo')->open()}}
        @method('DELETE')
        {{html()->form()->close()}}
    @endcan

@endsection

@section('script')
    <script>
        let debounceTimer;
        const buscarInput = document.getElementById('buscarDesembolso');
        const clasificacionSelect = document.getElementById('clasificacion');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const resultadosContador = document.getElementById('resultadosContador');
        const tablaContainer = document.getElementById('tablaDesembolsosContainer');

        // Búsqueda en vivo con debounce
        buscarInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            loadingSpinner.style.display = 'inline-block';

            debounceTimer = setTimeout(() => {
                realizarBusqueda(1);
            }, 500);
        });

        // Filtro por clasificación
        clasificacionSelect.addEventListener('change', function() {
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
                const clasificacion = clasificacionSelect.value;

                const response = await axios.get('{{route('prestamos.index')}}', {
                    params: {
                        buscar: buscar,
                        clasificacion: clasificacion,
                        page: page
                    }
                });

                const parser = new DOMParser();
                const doc = parser.parseFromString(response.data, 'text/html');
                const nuevaTabla = doc.querySelector('#tablaDesembolsosContainer');

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
            document.querySelectorAll('.btnEliminar').forEach((button) => {
                button.addEventListener('click', function (event) {
                    let id = event.target.closest('a', '.btnEliminar').dataset.id

                    Swal.fire({
                        title: '¿Está seguro?',
                        text: "¿Desea anular el desembolso? El desembolso no se puede eliminar si ya tiene registro de abonos",
                        icon: 'warning',
                        showCancelButton: true,
                        cancelButtonColor: '#3085d6',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Anular'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let url = '{{route('prestamos.destroy','?')}}'
                            url = url.replace('?', id)

                            document.getElementById('frmEliminarPrestamo').action = url
                            document.getElementById('frmEliminarPrestamo').submit();
                        }
                    })
                })
            })

            document.querySelectorAll('.btnEliminarFisico').forEach((button) => {
                button.addEventListener('click', function (event) {
                    let id = event.target.closest('a', '.btnEliminarFisico').dataset.id

                    Swal.fire({
                        title: '¿Eliminar permanentemente?',
                        text: "Esta acción eliminará el desembolso de forma definitiva y no se podrá recuperar. No se puede eliminar si tiene abonos registrados.",
                        icon: 'warning',
                        showCancelButton: true,
                        cancelButtonText: 'Cancelar',
                        cancelButtonColor: '#3085d6',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let url = '{{route('prestamos.eliminar','?')}}'
                            url = url.replace('?', id)

                            document.getElementById('frmEliminarFisicoPrestamo').action = url
                            document.getElementById('frmEliminarFisicoPrestamo').submit();
                        }
                    })
                })
            })
        }

        // Inicializar botones al cargar la página
        inicializarBotonesEliminar();
    </script>
@endsection
