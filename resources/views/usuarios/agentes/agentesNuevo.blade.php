@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('user.agentes.index')])
    Nuevo Agente
@endsection

@section('content')
    {{html()->form('POST',route('user.agentes.store'))->acceptsFiles()->open()}}
    @include('usuarios.agentes.formAgentes')
    <br>
    <br>
        <div class="row justify-content-center">
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Guardar datos del agente <i class="fa fa-save"></i> </button>
            </div>
        </div>
    {{html()->form()->close()}}
@endsection

