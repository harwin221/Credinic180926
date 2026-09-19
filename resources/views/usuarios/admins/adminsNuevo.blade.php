@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('user.index')])
    Nuevo Administrativo
@endsection

@section('content')
    {{html()->form('POST',route('user.store'))->acceptsFiles()->open()}}
    @include('usuarios.admins.formAdmin')
    <br>
    <br>
    @can('Crear Administrativos')
        <div class="row justify-content-center">
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Guardar <i class="fa fa-save"></i> </button>
            </div>
        </div>
    @endcan
    {{html()->form()->close()}}
@endsection
