@extends('layouts.app')
@section('tituloPagina')
    Listado de Abonos
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            @can('Crear Abonos')
                <a href="{{route('abonos.create')}}" class="btn btn-sm btn-success">Nuevo Abono <i class="fa fa-plus"></i></a>
            @endcan
        </div>
    </div>

    <br>
    <div class="row">
        <div class="col-md-8">
            <label for="buscarAbono"><b>Buscar:</b></label>
            <div class="input-group">
                <input type="text" id="buscarAbono" class="form-control" placeholder="Buscar por cliente, número de préstamo..." autocomplete="off">
                <span class="input-group-text" id="loadingSpinner" style="display: none;">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                </span>
            </div>
            <small class="text-muted" id="resultadosContador"></small>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="cobrador"><b>Cobrador:</b></label>
                {{html()->select('cobrador',[0=>'Todos']+$listaCobradores,request('cobrador'))->class('form-control select2')->style(['width'=>'100%'])->id('cobrador')}}
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive" id="tablaAbonosContainer">
                <table class="table table-sm table-striped" style="font-size: 14px">
                    <thead class="table-info">
                    <tr>
                        <th>#</th>
                        <th># Préstamo</th>
                        <th>Cliente</th>
                        <th>Fecha Abono</th>
                        <th>Total Abonado</th>
                        <th>Creado</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($abonos as $abono)

                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$abono->prestamo->consecutivo}}</td>
                            <td>{{$abono->prestamo->cliente->full_name}}</td>
                            <td>{{fecha_d_m_Y_h_i($abono->created_at)}}</td>
                            <td>{{$abono->prestamo->moneda." ".$abono->total_abonado2}}</td>
                            <td>{{$abono->user_create->username}}</td>
                            <td>
                                @if($abono->estado==1)
                                    <span class="badge bg-info">{{$abono->estado_abono}}</span>
                                @elseif($abono->estado==2)
                                    <span class="badge bg-danger">{{$abono->estado_abono}}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    @can('Ver Abonos')
                                        <a class="btn btn-outline-primary" title="Ver" href="{{route('abonos.show',$abono->id_enc)}}"><i class="fa fa-eye"></i></a>
                                    @endcan
                                    @can('Anular Abonos')
                                        @if($abono->estado==1)
                                            <a class="btn btn-outline-danger btnAnular" title="Anular" href="#" data-id="{{$abono->id_enc}}"><i class="fa fa-ban"></i></a>
                                        @endif
                                    @endcan
                                    @can('Eliminar Abonos')
                                        <a class="btn btn-outline-dark btnEliminarAbono" title="Eliminar" href="#" data-id="{{$abono->id_enc}}"><i class="fa fa-trash"></i></a>
                                    @endcan
                                </div>
                            </td>
                        </tr>

                    @endforeach
                    </tbody>
                </table>
                {{$abonos->appends(request()->all())->links()}}
            </div>
        </div>
    </div>

    @can('Anular Abonos')
    <div class="modal fade" id="modalAbono" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                {{html()->form('POST')->id('frmAbono')->open()}}
                <div class="modal-header">
                    <h5 class="modal-title">Anular Abono</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="motivo"><b>Motivo Anulación:</b></label>
                                <textarea name="detalle_anulado" class="form-control" rows="5" id="motivo"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Anular</button>
                </div>
                {{html()->form()->close()}}
            </div>
        </div>
    </div>
    @endcan

    @can('Eliminar Abonos')
    <form id="frmEliminarAbono" method="POST" action="">
        @csrf
        @method('DELETE')
    </form>
    @endcan

@endsection
@section('script')
    <script>
        let debounceTimer;
        const buscarInput = document.getElementById('buscarAbono');
        const cobradorSelect = document.getElementById('cobrador');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const resultadosContador = document.getElementById('resultadosContador');
        const tablaContainer = document.getElementById('tablaAbonosContainer');

        // Búsqueda en vivo con debounce
        buscarInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            loadingSpinner.style.display = 'inline-block';
            
            debounceTimer = setTimeout(() => {
                realizarBusqueda(1);
            }, 500);
        });

        // Filtro por cobrador
        $("#cobrador").on('change', function () {
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
                const cobrador = cobradorSelect.value;
                
                const response = await axios.get('{{route('abonos.index')}}', {
                    params: {
                        buscar: buscar,
                        cobrador: cobrador,
                        page: page
                    }
                });
                
                const parser = new DOMParser();
                const doc = parser.parseFromString(response.data, 'text/html');
                const nuevaTabla = doc.querySelector('#tablaAbonosContainer');
                
                if (nuevaTabla) {
                    tablaContainer.innerHTML = nuevaTabla.innerHTML;
                    
                    const filas = nuevaTabla.querySelectorAll('tbody tr');
                    if (buscar) {
                        resultadosContador.textContent = `${filas.length} resultado(s) encontrado(s)`;
                    } else {
                        resultadosContador.textContent = '';
                    }
                    
                    inicializarBotonesAnular();
                }
                
                loadingSpinner.style.display = 'none';
            } catch (error) {
                console.error('Error en la búsqueda:', error);
                loadingSpinner.style.display = 'none';
                Swal.fire('Error', 'No se pudo realizar la búsqueda', 'error');
            }
        }

        function inicializarBotonesAnular() {
            document.querySelectorAll('.btnAnular').forEach((button) => {
                button.addEventListener('click', anularAbono)
            })

            document.querySelectorAll('.btnEliminarAbono').forEach((button) => {
                button.addEventListener('click', function(e) {
                    let id = e.target.closest('a').dataset.id
                    Swal.fire({
                        title: '¿Eliminar permanentemente?',
                        text: "Esta acción eliminará el abono de forma definitiva y no se podrá recuperar.",
                        icon: 'warning',
                        showCancelButton: true,
                        cancelButtonText: 'Cancelar',
                        cancelButtonColor: '#3085d6',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let url = '{{route('abonos.destroy','?')}}'
                            url = url.replace('?', id)
                            document.getElementById('frmEliminarAbono').action = url
                            document.getElementById('frmEliminarAbono').submit()
                        }
                    })
                })
            })
        }

        function closeModal(modalId){
            const modalElement = document.getElementById(modalId);
            if (!modalElement) return;
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.hide();
        }

        function openModal(modalId){
            const modalElement = document.getElementById(modalId);
            if (!modalElement) return;
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement, {
                keyboard: false
            });
            modal.show();
        }

        function anularAbono(e){
            document.getElementById('frmAbono').action = ''
            let url = '{{route('abonos.anularAbono','?')}}'
            url = url.replace('?',e.target.dataset.id)

            console.log(url)
            document.getElementById('frmAbono').action = url
            openModal('modalAbono')
        }

        // Inicializar botones al cargar la página
        inicializarBotonesAnular();
    </script>
@endsection
