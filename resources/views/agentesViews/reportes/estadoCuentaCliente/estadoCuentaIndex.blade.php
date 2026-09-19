@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    Estado Cuenta Cliente
@endsection
@section('content')
    {{html()->form('GET',route('agentes.reportes.estadoCuentaCliente'))->open()}}
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="cliente"><strong>Cliente:</strong></label>
                {{html()->select('cliente',[''=>'-- Seleccione --']+$listaClientes,request('cliente'))->class('form-control select2')->style(['width'=>'100%'])->id('cliente')->required()}}
            </div>
        </div>
    </div>
    <br>
     <div class="row">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary w-100">Generar Lista <i class="fa fa-cog"></i></button>
        </div>
    </div>
    {{html()->form()->close()}}
    <br>

@endsection
