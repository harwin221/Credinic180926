@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('agentes.homeAgentes')])
    Mis Clientes
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-6">
            <div class="input-group mb-3">
                <input type="text" id="buscarCliente" class="form-control" placeholder="Buscar Cliente (nombre, apellido o cédula)" aria-label="Buscar cliente">
                <span class="input-group-text">
                    <i class="fa fa-search"></i>
                </span>
            </div>
            <small class="text-muted" id="resultadosInfo"></small>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div id="loadingSpinner" style="display: none; text-align: center; padding: 20px;">
                <i class="fa fa-spinner fa-spin fa-2x"></i>
                <p>Buscando...</p>
            </div>
            <div class="table-responsive" id="tablaClientes">
                <table class="table table-sm table-striped">
                    <thead>
                    <tr class="table-success">
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Cédula</th>
                        <th>Teléfonos</th>
                        <th>Acción</th>
                    </tr>
                    </thead>

                    <tbody id="clientesBody">
                    @foreach($clientes as $cliente)
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$cliente->full_name}}</td>
                            <td>{{$cliente->cedula}}</td>
                            <td>{{$cliente->telefono1." / ".$cliente->telefono2}}</td>

                            <td><a href="{{route('agentes.prestamosClientes',$cliente->id_enc)}}" class="btn btn-sm btn-primary">Ver Detalle <i class="fa fa-eye"></i></a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div id="paginacion">
                    {{$clientes->appends(request()->all())->links()}}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    let timeoutId = null;
    const inputBuscar = document.getElementById('buscarCliente');
    const clientesBody = document.getElementById('clientesBody');
    const paginacion = document.getElementById('paginacion');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const resultadosInfo = document.getElementById('resultadosInfo');

    // Búsqueda en tiempo real
    inputBuscar.addEventListener('input', function() {
        // Cancelar la búsqueda anterior si el usuario sigue escribiendo
        clearTimeout(timeoutId);
        
        const buscar = this.value.trim();
        
        // Esperar 500ms después de que el usuario deje de escribir
        timeoutId = setTimeout(() => {
            buscarClientes(buscar);
        }, 500);
    });

    async function buscarClientes(buscar) {
        try {
            // Mostrar spinner
            loadingSpinner.style.display = 'block';
            clientesBody.style.opacity = '0.5';
            
            // Hacer la petición AJAX
            const response = await axios.get('{{route('agentes.misClientes')}}', {
                params: { buscar: buscar }
            });
            
            // Actualizar la tabla con los resultados
            const parser = new DOMParser();
            const doc = parser.parseFromString(response.data, 'text/html');
            
            const nuevoBody = doc.getElementById('clientesBody');
            const nuevaPaginacion = doc.getElementById('paginacion');
            
            if (nuevoBody) {
                clientesBody.innerHTML = nuevoBody.innerHTML;
            }
            
            if (nuevaPaginacion) {
                paginacion.innerHTML = nuevaPaginacion.innerHTML;
            }
            
            // Mostrar información de resultados
            const filas = clientesBody.querySelectorAll('tr').length;
            if (buscar) {
                resultadosInfo.textContent = `${filas} resultado(s) encontrado(s)`;
            } else {
                resultadosInfo.textContent = '';
            }
            
            // Ocultar spinner
            loadingSpinner.style.display = 'none';
            clientesBody.style.opacity = '1';
            
        } catch (error) {
            console.error('Error al buscar clientes:', error);
            loadingSpinner.style.display = 'none';
            clientesBody.style.opacity = '1';
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo realizar la búsqueda'
            });
        }
    }

    // Manejar clicks en la paginación
    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            const url = e.target.closest('.pagination a').href;
            const buscar = inputBuscar.value.trim();
            
            cargarPagina(url, buscar);
        }
    });

    async function cargarPagina(url, buscar) {
        try {
            loadingSpinner.style.display = 'block';
            clientesBody.style.opacity = '0.5';
            
            const response = await axios.get(url, {
                params: { buscar: buscar }
            });
            
            const parser = new DOMParser();
            const doc = parser.parseFromString(response.data, 'text/html');
            
            const nuevoBody = doc.getElementById('clientesBody');
            const nuevaPaginacion = doc.getElementById('paginacion');
            
            if (nuevoBody) {
                clientesBody.innerHTML = nuevoBody.innerHTML;
            }
            
            if (nuevaPaginacion) {
                paginacion.innerHTML = nuevaPaginacion.innerHTML;
            }
            
            loadingSpinner.style.display = 'none';
            clientesBody.style.opacity = '1';
            
            // Scroll hacia arriba
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
        } catch (error) {
            console.error('Error al cargar página:', error);
            loadingSpinner.style.display = 'none';
            clientesBody.style.opacity = '1';
        }
    }
</script>
@endsection
