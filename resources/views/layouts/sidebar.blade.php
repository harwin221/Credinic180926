<ul class="sidebar-nav" id="sidebar-nav">

    <!-- 1. Inicio -->
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('home') ? '' : 'collapsed' }}" href="{{route('home')}}">
            <i class="bi bi-grid"></i>
            <span>Inicio</span>
        </a>
    </li>

    <!-- 2. Clientes -->
    @can('Ver Clientes')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('user.clientes.*') || request()->routeIs('clientes.*') ? '' : 'collapsed' }}" href="{{route('user.clientes.index')}}">
            <i class="bi-person-plus"></i>
            <span>Clientes</span>
        </a>
    </li>
    @endcan

    <!-- 3. Desembolsos -->
    @can('Ver Desembolsos')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('prestamos.*') && !request()->routeIs('prestamos.solicitudes.*') ? '' : 'collapsed' }}" href="{{route('prestamos.index')}}">
            <i class="bi-currency-dollar"></i>
            <span>Desembolsos</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('prestamos.solicitudes.*') ? '' : 'collapsed' }}" href="{{route('prestamos.solicitudes.indexSolicitudes')}}">
            <i class="bi-file-earmark-text"></i>
            <span>Solicitudes <span class="badge bg-info">{{\App\Models\prestamosModel::where('estado_aprobacion',1)->count()}}</span></span>
        </a>
    </li>
    @endcan

    <!-- 4. Arqueos -->
    @can('Reporte Arqueo (6)')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('reportes.arqueo.*') ? '' : 'collapsed' }}" href="{{route('reportes.arqueo.list')}}">
            <i class="fas fa-cash-register"></i>
            <span>Arqueos</span>
        </a>
    </li>
    @endcan

    <!-- 5. Abonos -->
    @can('Ver Abonos')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('abonos.*') ? '' : 'collapsed' }}" href="{{route('abonos.index')}}">
            <i class="bi-cash-coin"></i>
            <span>Abonos</span>
        </a>
    </li>
    @endcan

    <!-- 6. Reportes -->
    @can('Ver Reportes')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('reportes.*') && !request()->routeIs('reportes.arqueo.*') ? '' : 'collapsed' }}" href="{{route('reportes.index')}}">
            <i class="bi-bar-chart"></i>
            <span>Reportes</span>
        </a>
    </li>
    @endcan

    <!-- 7. Configuración -->
    @canany(['Ver Roles','Ver Documentos','Ver Administrativos','Ver Agentes'])
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('configuracion.*') || request()->routeIs('roles.*') || request()->routeIs('permisos.*') || request()->routeIs('user.*') || request()->routeIs('sucursales.*') ? '' : 'collapsed' }}" href="{{route('configuracion.index')}}">
            <i class="bi-gear"></i>
            <span>Configuración</span>
        </a>
    </li>
    @endcanany

    <!-- 8. Simulador -->
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('prestamo.simulador') ? '' : 'collapsed' }}" href="{{route('prestamo.simulador')}}">
            <i class="bi-calculator"></i>
            <span>Simulador</span>
        </a>
    </li>

    <!-- Extra: Usuarios Online (Solo Super Admin) -->
    @if(Auth::user()->id==1)
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('configuracion.onlineUsers.*') ? '' : 'collapsed' }}" href="{{route('configuracion.onlineUsers.index')}}">
            <i class="bi-people"></i>
            <span>Usuarios Online</span>
        </a>
    </li>
    @endif

</ul>
