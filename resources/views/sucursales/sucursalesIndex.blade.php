@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack', ['url' => route('home')])
    Sucursales
@endsection
@section('content')
    @can('Crear Sucursales')
        <div class="row mb-3">
            <div class="col-md-12">
                <a href="{{ route('sucursales.create') }}" class="btn btn-sm btn-primary">
                    Nuevo <i class="fa fa-plus"></i>
                </a>
            </div>
        </div>
    @endcan

    @can('Eliminar Sucursales')
        {{ html()->form('post')->class('d-inline')->id('frmEliminar')->open() }}
        @method('DELETE')
        {{ html()->form()->close() }}
    @endcan

    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-striped" style="font-size: 14px">
                    <thead class="table-info">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Dirección</th>
                            <th>Teléfono</th>
                            <th>Admins Asignados</th>
                            <th>Estado</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sucursales as $sucursal)
                            <tr>
                                <td>{{ $loop->index + 1 }}</td>
                                <td><strong>{{ $sucursal->nombre }}</strong></td>
                                <td>{{ $sucursal->direccion ?? '-' }}</td>
                                <td>{{ $sucursal->telefono ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $sucursal->admins()->count() }}</span>
                                </td>
                                <td>
                                    @if($sucursal->estado == 1)
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Inactiva</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @can('Editar Sucursales')
                                        <a href="{{ route('sucursales.edit', $sucursal->id_enc) }}"
                                           class="btn btn-sm btn-primary" title="Editar">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('Eliminar Sucursales')
                                        <button type="button"
                                                class="btn btn-sm btn-danger btnEliminar"
                                                data-target="{{ route('sucursales.destroy', $sucursal->id_enc) }}"
                                                title="Eliminar">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $sucursales->links() }}
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    function inicializarBotonesEliminar() {
        const btnEliminar = document.querySelectorAll('.btnEliminar');
        btnEliminar.forEach(button => {
            button.addEventListener('click', (event) => {
                Swal.fire({
                    title: '¿Está seguro?',
                    text: 'No se puede eliminar si tiene administrativos asignados.',
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
                });
            });
        });
    }

    inicializarBotonesEliminar();
</script>
@endsection
