@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('home')])
    Gestión de Usuarios
@endsection
@section('content')
    <div class="row">
        @can('Ver Administrativos')
        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
            <a href="{{route('user.index')}}">

                <div class="small-box" style="background: #0f7181">
                    <div class="inner" style="color:white">
                        <h3>&nbsp;</h3>
                        <p>Administrativos</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users"></i>
                    </div>
                </div>
            </a>
        </div>
        @endcan
            @can('Ver Clientes')
                <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
                    <a href="{{route('user.clientes.index')}}">

                        <div class="small-box" style="background: #0f7181">
                            <div class="inner" style="color:white">
                                <h3>&nbsp;</h3>
                                <p>Clientes</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-users"></i>
                            </div>
                        </div>
                    </a>
                </div>
            @endcan
            @can('Ver Agentes')
                <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
                    <a href="{{route('user.agentes.index')}}">

                        <div class="small-box" style="background: #0f7181">
                            <div class="inner" style="color:white">
                                <h3>&nbsp;</h3>
                                <p>Agentes</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-users"></i>
                            </div>
                        </div>
                    </a>
                </div>
            @endcan
    </div>
@endsection
