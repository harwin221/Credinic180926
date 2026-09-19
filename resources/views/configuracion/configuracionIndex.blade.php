@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('home')])
    Configuración
@endsection

@section('content')
    <style>
        .config-card {
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            height: 100%;
            text-decoration: none;
            display: block;
        }
        
        .config-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.12);
            text-decoration: none;
        }
        
        .config-card-body {
            padding: 15px 12px;
            text-align: center;
            position: relative;
        }
        
        .config-icon {
            font-size: 32px;
            margin-bottom: 8px;
            color: #2c3e50;
        }
        
        .config-title {
            font-size: 12px;
            font-weight: 700;
            margin: 0;
            color: #2c3e50;
            line-height: 1.3;
        }
        
        .config-card-body::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.4);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #a8edea;
        }
    </style>

    {{-- Sección Roles --}}
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="section-title"><i class="fas fa-shield-alt"></i> Roles y Permisos</h4>
        </div>
        @can('Ver Roles')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <a href="{{route('roles.index')}}" class="config-card" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);">
                    <div class="config-card-body">
                        <div class="config-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h5 class="config-title">Roles y Permisos</h5>
                    </div>
                </a>
            </div>
        @endcan
    </div>

    {{-- Sección Usuarios --}}
    @canany(['Ver Administrativos','Ver Agentes'])
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="section-title"><i class="fas fa-users"></i> Usuarios</h4>
        </div>
        @can('Ver Administrativos')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <a href="{{route('user.index')}}" class="config-card" style="background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);">
                    <div class="config-card-body">
                        <div class="config-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h5 class="config-title">Administrativos</h5>
                    </div>
                </a>
            </div>
        @endcan
        
        @can('Ver Agentes')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <a href="{{route('user.agentes.index')}}" class="config-card" style="background: linear-gradient(135deg, #d4fc79 0%, #96e6a1 100%);">
                    <div class="config-card-body">
                        <div class="config-icon">
                            <i class="fas fa-user-tag"></i>
                        </div>
                        <h5 class="config-title">Agentes</h5>
                    </div>
                </a>
            </div>
        @endcan

        @can('Ver Sucursales')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <a href="{{route('sucursales.index')}}" class="config-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="config-card-body">
                        <div class="config-icon" style="color:#fff;">
                            <i class="fas fa-building"></i>
                        </div>
                        <h5 class="config-title" style="color:#fff;">Sucursales</h5>
                    </div>
                </a>
            </div>
        @endcan
    </div>
    @endcanany

    {{-- Sección Parámetros --}}
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="section-title"><i class="fas fa-cogs"></i> Parámetros del Sistema</h4>
        </div>
        @can('Ver Documentos')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <a href="{{route('configuracion.documentos.index')}}" class="config-card" style="background: linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%);">
                    <div class="config-card-body">
                        <div class="config-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h5 class="config-title">Tipos de Documentos</h5>
                    </div>
                </a>
            </div>
        @endcan

        @can('Establecer Feriados')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <a href="{{route('configuracion.feriados.indexFeriados')}}" class="config-card" style="background: linear-gradient(135deg, #fdcbf1 0%, #e6dee9 100%);">
                    <div class="config-card-body">
                        <div class="config-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <h5 class="config-title">Feriados</h5>
                    </div>
                </a>
            </div>
        @endcan

        @can('Establecer Horas Activas Sistema')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <a href="{{route('configuracion.configuracion.activacion_sistema')}}" class="config-card" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                    <div class="config-card-body">
                        <div class="config-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h5 class="config-title">Horas de Activación</h5>
                    </div>
                </a>
            </div>
        @endcan
        
        @can('Ver Tipo Negocios')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <a href="{{route('configuracion.tiposNegocios.index')}}" class="config-card" style="background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);">
                    <div class="config-card-body">
                        <div class="config-icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <h5 class="config-title">Tipos de Negocios</h5>
                    </div>
                </a>
            </div>
        @endcan
    </div>
@endsection
