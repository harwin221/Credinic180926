@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('configuracion.index')])
    Tipos de Negocios
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <a href="#" id="btnNuevo" class="btn btn-sm btn-primary">Nuevo <i class="fa fa-plus"></i></a>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table tabla table-sm table-striped">
                    <thead class="table-info">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th class="text-end">Acción</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($tiposNegocios as $tipo)
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$tipo->nombre}}</td>
                            <td class="text-center">
                                <x-actionDropdown>
                                    <li><a class="dropdown-item text-primary btnEditar" href="#" data-id="{{$tipo->id_enc}}"><i class="fa fa-edit"></i> Editar</a></li>
                                    <li><a class="dropdown-item text-danger btnEliminar" href="#" data-id="{{$tipo->id_enc}}"><i class="fa fa-trash"></i> Eliminar</a></li>
                                </x-actionDropdown>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{$tiposNegocios->links()}}
            </div>
        </div>
    </div>

    @include('configuracion.tiposNegocios.modalTipoNegocios')
    {{html()->form('post')->id('frmDelete')->open()}}
    @method('DELETE')
    {{html()->form()->close()}}

@endsection
@section('script')
    <script>
        const btnNuevo = document.getElementById('btnNuevo');
        const modalTipo = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTipoNegocio'))
        const btnEditar = document.querySelectorAll('.btnEditar');
        const btnEliminar = document.querySelectorAll('.btnEliminar');
        const method = document.querySelector("[name=_method]");

        function limpiarControles()
        {
            document.getElementById('nombre').value = null
        }

        btnNuevo.addEventListener('click', function () {
            limpiarControles()
            frmTipo.action = '{{route('configuracion.tiposNegocios.store')}}'
            method.value = 'POST'
            modalTipo.show()
        })

        btnEditar.forEach(button => {
            button.addEventListener('click',async (event)=>{

                if (event.target.classList.contains('btnEditar')) {
                    let url = '{{route('configuracion.tiposNegocios.update','?')}}'
                    url = url.replace('?', event.target.dataset.id)

                    let urlGet = '{{route('configuracion.tiposNegocios.getDatos','?')}}'
                    urlGet = urlGet.replace('?', event.target.dataset.id)

                    let {data} = await axios.get(urlGet)
                    document.getElementById('nombre').value = data.nombre

                    frmTipo.action = url
                    method.value = 'PUT'
                    modalTipo.show()
                }
            })
        })

        btnEliminar.forEach(button => {
            button.addEventListener('click',(event)=>{
                if (event.target.classList.contains('btnEliminar')) {
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
                            let url = '{{route('configuracion.tiposNegocios.destroy','?')}}'
                            url=url.replace('?',event.target.dataset.id)

                            frmEliminar.action = url
                            frmEliminar.submit();
                        }
                    })
                }
            })
        })
    </script>
@endsection
