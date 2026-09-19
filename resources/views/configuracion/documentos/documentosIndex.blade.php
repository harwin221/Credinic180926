@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('configuracion.index')])
    Tipo de Documentos
@endsection
@section('content')
    @can('Crear Documentos')
        <div class="row">
            <div class="col-md-12">
                <a href="#" id="btnNuevo" class="btn btn-sm btn-primary">Nuevo <i class="fa fa-plus"></i></a>
            </div>
        </div>
    <br>
    @endcan
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table tabla table-sm table-striped">
                    <thead class="table-info">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Tipo Documento</th>
                        <th class="text-end">Acción</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($documentos as $docs)
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$docs->nombre}}</td>
                            <td>{{($docs->tipo_documento==1?'Foto':'Documento')}}</td>
                            <td class="text-center">
                                <x-actionDropdown>
                                    @can('Editar Documentos')
                                        <li><a class="dropdown-item text-primary btnEditar" href="#" data-id="{{$docs->id_enc}}"><i class="fa fa-edit"></i> Editar</a></li>
                                    @endcan
                                    @can('Eliminar Documentos')
                                        <li><a class="dropdown-item text-danger btnEliminar" href="#" data-id="{{$docs->id_enc}}"><i class="fa fa-trash"></i> Eliminar</a></li>
                                    @endcan
                                </x-actionDropdown>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('configuracion.documentos.modalDocumento')
        @can('Eliminar Documentos')
        {{html()->form('post')->id('frmDelete')->open()}}
        @method('DELETE')
        {{html()->form()->close()}}
    @endcan
@endsection
@section('script')
    <script>
        const btnNuevo = document.getElementById('btnNuevo');
        const modalDocumentos = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDocumento'))
        const btnEditar = document.querySelectorAll('.btnEditar');
        const btnEliminar = document.querySelectorAll('.btnEliminar');
        const method = document.querySelector("[name=_method]");

        function limpiarControles()
        {
            document.getElementById('nombre').value = null
            document.getElementById('tipo').value = 1
        }

        btnNuevo.addEventListener('click', function () {
            limpiarControles()
            frmDocumento.action = '{{route('configuracion.documentos.store')}}'
            method.value = 'POST'
            modalDocumentos.show()
        })

        btnEditar.forEach(button => {
           button.addEventListener('click',async (event)=>{

               let id = event.target.closest('a', '.btnEditar').dataset.id

               let url = '{{route('configuracion.documentos.update','?')}}'
               url = url.replace('?', id)

               let urlGet = '{{route('configuracion.documentos.getDatos','?')}}'
               urlGet = urlGet.replace('?', id)

               let {data} = await axios.get(urlGet)
               document.getElementById('nombre').value = data.nombre
               document.getElementById('tipo').value = data.tipo

               frmDocumento.action = url
               method.value = 'PUT'
               modalDocumentos.show()
           })
        })

        btnEliminar.forEach(button => {
            button.addEventListener('click',(event)=>{
                let id = event.target.closest('a', '.btnEditar').dataset.id

                Swal.fire({
                        title: '¿Está seguro?',
                        text: "¿Desea eliminar el item?",
                        icon: 'warning',
                        showCancelButton: true,
                        cancelButtonColor: '#3085d6',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Eliminar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let url = '{{route('configuracion.documentos.destroy','?')}}'
                            url=url.replace('?',id)

                            frmEliminar.action = url
                            frmEliminar.submit();
                        }
                    })
            })
        })
    </script>
@endsection
