@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('user.clientes.edit',$cliente->id_enc)])
    Nuevo Fiador Cliente: <span style="color: blue">{{$cliente->full_name}}</span>
@endsection

@section('content')

    {{html()->form('POST',route('fiador.store'))->acceptsFiles()->open()}}
    <input type="hidden" name="clienteId" value="{{$cliente->id_enc}}">
    @include('usuarios.fiadores.formFiador')
    <br>
    <br>
    <div class="row justify-content-center">
        @canany(['Editar Fiadores','Crear Fiadores'])
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">Guardar Fiador<i class="fa fa-save"></i> </button>
        </div>
        @endcanany
    </div>
    {{html()->form()->close()}}


@endsection
