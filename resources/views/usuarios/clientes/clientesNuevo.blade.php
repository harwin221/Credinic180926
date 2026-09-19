@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('user.clientes.index')])
    Nuevo Cliente
@endsection

@section('content')
    {{html()->form('POST',route('user.clientes.store'))->acceptsFiles()->open()}}
    @include('usuarios.clientes.formClientes')
    <br>
    <br>
    @can('Crear Clientes')
        <div class="row justify-content-center">
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Guardar datos del cliente <i class="fa fa-save"></i> </button>
            </div>
        </div>
    @endcan

    {{html()->form()->close()}}
@endsection
@section('script')

@endsection
