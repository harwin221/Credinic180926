@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('reportes.index')])
    Antiguedad de Saldos
@endsection
@section('content')
    {{html()->form('GET',route('reportes.antiguedad_saldos'))->open()}}
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="cliente"><strong>Cliente:</strong></label>
                {{html()->select('cliente',[0=>'Todos']+$listaClientes,request('cliente'))->class('form-control select2')->style(['width'=>'100%'])->id('cliente')}}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="cobrador"><strong>Cobrador:</strong></label>
                {{html()->select('cobrador',[0=>'Todos']+$listaCobradores,request('cobrador'))->class('form-control select2')->style(['width'=>'100%'])->id('cobrador')}}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="frecuencia"><strong>Frecuencia:</strong></label>
                {{html()->select('frecuencia',['0'=>'Todos','1'=>'Diario','2'=>'Semanal','3'=>'Quincenal','4'=>'Mensual','5'=>'Trimestral','6'=>'Bimestral','7'=>'Catorcenal'],request('frecuencia'))->class('form-control')->id('frecuencia')}}
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-6">
            <button type="submit" name="pdf" value="pdf" class="btn btn-primary w-100" formtarget="_blank">Generar Reporte PDF <i class="fa fa-file-pdf"></i></button>
        </div>
        <div class="col-md-6 text-center">
            @if(count($prestamos))
                <button type="submit" name="excel" value="excel" class="btn btn-success w-100">Exportar a EXCEL <i class="fa fa-file-excel"></i></button>
            @else
                <p style="color: red"><b>Selecciona los filtros y genera el reporte</b></p>
            @endif
        </div>
    </div>
    {{html()->form()->close()}}

@endsection
