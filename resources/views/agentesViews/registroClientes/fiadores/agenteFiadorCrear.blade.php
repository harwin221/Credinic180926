@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('agentes.user.clientes.edit',$cliente->id_enc)])
    Nuevo Fiador Cliente: <span style="color: blue">{{$cliente->full_name}}</span>
@endsection

@section('content')

    {{html()->form('POST',route('agentes.user.fiador.store'))->acceptsFiles()->open()}}
    <input type="hidden" name="clienteId" value="{{$cliente->id_enc}}">
    @include('agentesViews.registroClientes.fiadores.agenteFormFiador')
    <br>
    <br>
    <div class="row justify-content-center">
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">Guardar Fiador<i class="fa fa-save"></i> </button>
        </div>
    </div>
    {{html()->form()->close()}}


@endsection
