@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('user.clientes.edit',encode($user->fiador_cliente->user_id))])
    Editar Fiador
@endsection

@section('content')

    {{html()->modelForm($user,'POST',route('fiador.update',$user->id_enc))->acceptsFiles()->open()}}
    @method('PUT')
    @include('usuarios.fiadores.formFiador')
    <br>
    <br>
        <div class="row justify-content-center">
            @can('Editar Fiadores')
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Actualizar datos del fiador <i class="fa fa-save"></i> </button>
            </div>
            @endcan
        </div>
    {{html()->closeModelForm()}}


@endsection
@section('script')
    <script>
        document.querySelectorAll('.btnEliminarFoto').forEach((button)=>{
            button.addEventListener('click',function (event){
                Swal.fire({
                    title: '¿Está seguro?',
                    text: "¿Desea eliminar la imagen seleccionada?",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#3085d6',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let url = '{{route('user.clientes.eliminarFoto','?')}}'
                        url = url.replace('?',event.target.closest('a, .btnEliminarFoto').dataset.id)
                        window.location = url
                    }
                })
            })
        })
    </script>
@endsection
