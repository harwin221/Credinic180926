@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('reportes.index')])
    Filtro de Créditos Vencidos
@endsection
@section('content')
    <div class="card shadow-sm border-0 rounded-lg">
        <div class="card-body p-4">
            {{html()->form('GET',route('reportes.creditosVencidos.html'))->open()}}
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="desde" class="form-label font-weight-bold">Vencimiento desde:</label>
                        <input type="date" name="desde" class="form-control rounded-pill" value="{{request('desde')}}" id="desde">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="hasta" class="form-label font-weight-bold">Vencimiento hasta:</label>
                        <input type="date" name="hasta" class="form-control rounded-pill" value="{{request('hasta', date('Y-m-d'))}}" id="hasta">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="cobrador" class="form-label font-weight-bold">Cobrador:</label>
                        {{html()->select('cobrador',[0=>'Todos']+$listaCobradores,request('cobrador'))->class('form-control select2 rounded-pill')->style(['width'=>'100%'])->id('cobrador')}}
                    </div>
                </div>
            </div>
            
            <div class="row mt-4 justify-content-center">
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary btn-block rounded-pill py-2 shadow-sm">
                        <i class="fas fa-eye mr-2"></i> Generar Reporte HTML
                    </button>
                </div>
            </div>
            {{html()->form()->close()}}
        </div>
    </div>
@endsection
