<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema Inhabilitado</title>
  <!-- Bootstrap 5 CSS -->

    <!-- Vendor CSS Files -->
    <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/boxicons/css/boxicons.min.css')}}" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="{{asset('assets/css/style.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/fontawesome-free-6.4.0-web/css/all.min.css')}}" rel="stylesheet">
</head>
<body>
  <div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="text-center">
      <div class="alert alert-warning" role="alert">
          <img src="{{asset('assets/img/LogoCrediNica.png')}}" style="width: 200px" alt="">
        <h1 class="display-3">¡Sistema Inhabilitado!</h1>
        <p class="lead">El sistema está fuera de servicio en este momento. Por favor, intenta acceder en el horario programado.</p>
        <hr>
        <p class="mb-0">El sistema estará disponible entre las {{date('h:i a',strtotime($config->hora_inicio_activacion))}} y las {{date('h:i a',strtotime($config->hora_fin_activacion))}} de Lunes a Sábado.</p>
        <p class="mb-0">¡Gracias por tu comprensión!</p>
      </div>
      @if(Auth::check())
        {{html()->form('post',route('logout'))->class('d-inline')->open()}}
        <button type="submit" class="btn btn-primary" href="#">
          <i class="bi bi-box-arrow-right"></i>
          <span>Volver a la página principal</span>
        </button>
        {{html()->form()->close()}}
      @else
        <a href="{{route('login')}}" class="btn btn-primary mt-4">Volver a la página principal</a>
      @endif
    </div>
  </div>

  <!-- Bootstrap 5 JS y dependencias -->
<script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

</body>
</html>
