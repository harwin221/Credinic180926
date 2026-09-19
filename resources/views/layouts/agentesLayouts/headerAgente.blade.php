<div class="d-flex align-items-center">
    <a href="{{route('home')}}" class="logo  text-center">
        <img src="{{asset('assets/img/LogoCrediNica.png')}}" style="width: 45%" alt="">
        {{--        <span class="d-none d-lg-block mx-auto">CrediNica</span>--}}
    </a>
    <i class="bi bi-list toggle-sidebar-btn"></i>
</div>

<nav class="header-nav ms-auto">
    <ul class="d-flex align-items-center">
        <li class="dropdown-content">
            <a href="#" data-bs-target="#calc" data-bs-toggle="modal" class="btn btn-success me-2"><i class="fa fa-calculator" style=""></i></a>
        </li>
        <li class="nav-item dropdown pe-3">

            <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                <img src="{{\Auth::user()->user_image}}" alt="Profile" class="rounded-circle">
                <span class="d-none d-md-block dropdown-toggle ps-2">{{\Auth::user()->nombres}}</span>
            </a><!-- End Profile Iamge Icon -->

            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                <li class="dropdown-header">
                    <h6>{{\Auth::user()->nombres}}</h6>
                    <span>{{\Auth::user()->email}}</span>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>

{{--                <li>--}}
{{--                    <a class="dropdown-item d-flex align-items-center" href="users-profile.html">--}}
{{--                        <i class="bi bi-person"></i>--}}
{{--                        <span>Mi Perfil Agente</span>--}}
{{--                    </a>--}}
{{--                </li>--}}
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    {{html()->form('post',route('logout'))->open()}}
                        <button type="submit" class="dropdown-item d-flex align-items-center" href="#">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Cerrar Sesión</span>
                        </button>
                    {{html()->form()->close()}}
                </li>

            </ul>
        </li>

    </ul>
</nav>
