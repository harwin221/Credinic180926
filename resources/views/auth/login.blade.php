@extends('layouts.loginapp')

@section('content')
    <main>
        <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="pt-4 pb-4">
                                    <div class="d-flex justify-content-center">
                                        <img src="{{asset('assets/img/LogoCrediNica.png')}}" height="75" alt="">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        @include('utlisComponents.flashMessage')
                                    </div>
                                </div>

                                <form class="row g-3 text-center needs-validation" action="{{route('login2')}}"
                                      method="POST">
                                    @csrf
                                    <div class="col-12">
                                        <label for="yourUsername" class="form-label"><strong>Nombre de Usuario:</strong></label>
                                        <div class="input-group has-validation">
                                            <input type="text" name="username" autocomplete="off"
                                                   class="form-control @error('datosIncorrectos') is-invalid @enderror"
                                                   id="yourUsername" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="yourPassword" class="form-label"><strong>Contraseña:</strong></label>
                                        <div class="input-group">
                                            <input type="password" name="password" autocomplete="off" class="form-control @error('datosIncorrectos') is-invalid @enderror" id="yourPassword" required>
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility()">
                                                <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                            </button>
                                        </div>
                                        @error('datosIncorrectos')
                                        <span class="invalid-feedback text-center" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                        @enderror
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for=""><strong>Tipo de Sistema:</strong></label>
                                            {{html()->select('sistema',['1'=>'Administrativo','2'=>'Cobradores'])->class('form-control')}}
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <button class="btn btn-primary w-100" type="submit">Iniciar Sesión</button>
                                    </div>
                                </form>

                            </div>
                        </div>

                        <div class="credits text-center fw-bold">
                            <!-- All the links in the footer should remain intact. -->
                            <!-- You can delete the links only if you purchased the pro version. -->
                            <!-- Licensing information: https://bootstrapmade.com/license/ -->
                            <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/ -->
                            Plantilla Por <a href="https://bootstrapmade.com/">BootstrapMade</a><br>
                            Desarrollado Por <a href="mailto:heynermartinez8@gmail.com">Ing. Heyner Nuñez</a>
                        </div>

                    </div>
                </div>
            </div>

        </section>
    </main>
@endsection
@section('script')
    <script>
           function togglePasswordVisibility() {
               const passwordField = document.getElementById('yourPassword');
               const togglePasswordIcon = document.getElementById('togglePasswordIcon');
               if (passwordField.type === 'password') {
                   passwordField.type = 'text';
                   togglePasswordIcon.classList.remove('fa-eye');
                   togglePasswordIcon.classList.add('fa-eye-slash');
               } else {
                   passwordField.type = 'password';
                   togglePasswordIcon.classList.remove('fa-eye-slash');
                   togglePasswordIcon.classList.add('fa-eye');
               }
           }
    </script>
@endsection
