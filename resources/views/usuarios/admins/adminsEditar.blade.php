@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('user.index')])
     Editar Administrativo
@endsection

@section('content')
    @can('Cambiar Contraseña Administrativos')
    <div class="row">
        <div class="col-md-12">
                <a href="#" class="btn btn-sm btn-warning" data-bs-target="#modalCambiarContrasenya" data-bs-toggle="modal">Cambiar Contraseña <i class="fa fa-key"></i></a>
                <div class="modal fade" id="modalCambiarContrasenya" data-bs-backdrop="static" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                        <div class="modal-content">
                            {{html()->form()->method('POST')->action(route('user.cambiarContrasenyaUser',$user->id_enc))->open()}}
                            <div class="modal-header">
                                <h5 class="modal-title">Cambiar Contraseña</h5>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="contrasenya">Nueva Contraseña:</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" name="contrasenya" required id="contrasenya">
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="fa fa-eye" id="eyeIcon"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="confirmContrasenya">Confirmar Contraseña:</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" name="confirm_contrasenya" required id="confirmContrasenya">
                                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                                <i class="fa fa-eye" id="eyeIconConfirm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="submit" class="btn btn-primary">Actualizar</button>
                            </div>
                            {{html()->form()->close()}}
                        </div>
                    </div>
                </div>
        </div>
    </div>
    @endcan
    <br>

    {{html()->modelForm($user,'POST',route('user.update',$user->id_enc))->acceptsFiles()->open()}}
    @method('PUT')
    @include('usuarios.admins.formAdmin')
    <br>
    <br>
    @can('Editar Administrativos')
        <div class="row justify-content-center">
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Actualizar <i class="fa fa-save"></i> </button>
            </div>
        </div>
    @endcan
    {{html()->closeModelForm()}}
@endsection
@section('script')
    <script>
        // Toggle para mostrar/ocultar Nueva Contraseña
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('contrasenya');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });

        // Toggle para mostrar/ocultar Confirmar Contraseña
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const confirmPasswordInput = document.getElementById('confirmContrasenya');
            const eyeIconConfirm = document.getElementById('eyeIconConfirm');
            
            if (confirmPasswordInput.type === 'password') {
                confirmPasswordInput.type = 'text';
                eyeIconConfirm.classList.remove('fa-eye');
                eyeIconConfirm.classList.add('fa-eye-slash');
            } else {
                confirmPasswordInput.type = 'password';
                eyeIconConfirm.classList.remove('fa-eye-slash');
                eyeIconConfirm.classList.add('fa-eye');
            }
        });

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
