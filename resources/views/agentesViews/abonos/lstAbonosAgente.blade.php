@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('agentes.homeAgentes')])
    Listado de Abonos
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <a href="{{route('agentes.abonos.createAbono')}}" class="btn btn-sm btn-success">Nuevo Abono <i class="fa fa-plus"></i></a>
        </div>
    </div>
    <br>
    <div class="col-sm-12 col-md-6">
        <label for="buscarAbono"><b>Buscar:</b></label>
        <div class="input-group mb-3">
            <input type="text" id="buscarAbono" class="form-control" placeholder="Buscar por cliente, número de préstamo..." autocomplete="off">
            <span class="input-group-text" id="loadingSpinner" style="display: none;">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            </span>
        </div>
        <small class="text-muted" id="resultadosContador"></small>
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
                        <th>Creado por</th>
                        <th>Estado</th>
                        <th class="text-center">Acción</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($abonos as $abono)

                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$abono->prestamo->consecutivo}}</td>
                            <td>{{$abono->prestamo->cliente->full_name}}</td>
                            <td>{{fecha_d_m_Y_h_i($abono->created_at)}}</td>
                            <td>{{$abono->prestamo->moneda." ".$abono->abono_detalle->sum('monto_abono')}}</td>
                            <td>{{$abono->user_create->full_name}}</td>
                            <td>
                                @if($abono->estado==1)
                                    <span class="badge bg-info">{{$abono->estado_abono}}</span>
                                @elseif($abono->estado==2)
                                    <span class="badge bg-danger">{{$abono->estado_abono}}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a class="btn btn-outline-primary" title="Ver" href="{{route('agentes.abonos.show',$abono->id_enc)}}"><i class="fa fa-eye"></i></a>
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

        <div class="modal fade" id="modalAbono" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
             aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
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
                                    <textarea name="detalleAnulado" class="form-control" rows="5"
                                              id="motivo"></textarea>
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
@endsection
@section('script')
    <script>
        let debounceTimer;
        const buscarInput = document.getElementById('buscarAbono');
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
                
                const response = await axios.get('{{route('agentes.abonos.index')}}', {
                    params: {
                        buscar: buscar,
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
                }
                
                loadingSpinner.style.display = 'none';
            } catch (error) {
                console.error('Error en la búsqueda:', error);
                loadingSpinner.style.display = 'none';
                Swal.fire('Error', 'No se pudo realizar la búsqueda', 'error');
            }
        }

        function closeModal(modalId) {
            const modalElement = document.getElementById(modalId);
            if (!modalElement) return;
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.hide();
        }

        function openModal(modalId) {
            const modalElement = document.getElementById(modalId);
            if (!modalElement) return;
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement, {
                keyboard: false
            });
            modal.show();
        }
    </script>
@endsection
