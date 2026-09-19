<ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('agentes.homeAgentes') ? '' : 'collapsed' }}" href="{{route('agentes.homeAgentes')}}">
            <i class="bi bi-grid"></i>
            <span>Inicio</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('agentes.recaudo') ? '' : 'collapsed' }}" href="{{route('agentes.recaudo')}}">
            <i class="bi bi-wallet2"></i>
            <span>Recaudo</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('agentes.misClientes') || request()->routeIs('agentes.prestamosClientes') ? '' : 'collapsed' }}" href="{{route('agentes.misClientes')}}">
            <i class="bi bi-person"></i>
            <span>Mis Clientes</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('agentes.user.clientes.*') ? '' : 'collapsed' }}" href="{{route('agentes.user.clientes.index')}}">
            <i class="bi bi-person"></i>
            <span>Solicitud Clientes</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('agentes.simulador') ? '' : 'collapsed' }}" href="{{route('agentes.simulador')}}">
            <i class="bi-currency-dollar"></i>
            <span>Simulador</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('agentes.abonos.*') ? '' : 'collapsed' }}" href="{{route('agentes.abonos.index')}}">
            <i class="bi bi-person"></i>
            <span>Abonos</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('agentes.reportes.*') ? '' : 'collapsed' }}" href="{{route('agentes.reportes.index')}}">
            <i class="bi bi-file-text"></i>
            <span>Reportes</span>
        </a>
    </li>

</ul>
