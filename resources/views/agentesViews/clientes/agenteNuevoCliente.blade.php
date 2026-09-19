@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('agentes.registroClientes')])
    Registro de Nuevo Cliente
@endsection

@section('content')
    {{html()->form('POST',route('agentes.storeNuevoCliente'))->acceptsFiles()->open()}}
    @include('agentesViews.clientes.agenteFormClientes')
    <br>
    <br>
        <div class="row justify-content-center">
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Guardar datos del cliente <i class="fa fa-save"></i> </button>
            </div>
        </div>
    {{html()->form()->close()}}
@endsection
@section('script')

@endsection
