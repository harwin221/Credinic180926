@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('reportes.index')])
    Detalle de Colocación V2
@endsection
@section('content')
    {{html()->form('GET',route('reportes.colocacionV2'))->open()}}
    <div class="row">

        <div class="col-md-3">
            <div class="form-group">
                <label for="anyos"><strong>Año:</strong></label>
                {{html()->select('anio',[''=>'* Seleccione *']+$anios,[])->class('form-control')->id('anyos')->required()}}
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="mes"><strong>Desembolsos creados en:</strong></label>
                {{html()->select('mes',[''=>'* Seleccione *']+$meses,[])->class('form-control')->id('mes')->required()}}
            </div>
        </div>

{{--        <div class="col-md-4">--}}
{{--            <div class="form-group">--}}
{{--                <label for="cobrador"><strong>Cobrador:</strong></label>--}}
{{--                {{html()->select('cobrador',[0=>'Todos']+$listaCobradores,request('cobrador'))->class('form-control select2')->style(['width'=>'100%'])->id('cobrador')}}--}}
{{--            </div>--}}
{{--        </div>--}}
    </div>
    <br>

    <div class="row">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary w-100">Generar Lista <i class="fa fa-cog"></i></button>
        </div>
    </div>
    {{html()->form()->close()}}
@endsection
