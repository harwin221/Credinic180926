@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('reportes.index')])
    Cartera Vencida Histórica
@endsection
@section('content')
<div class="card shadow-sm border-0 rounded-lg">
    <div class="card-body p-4">
        <p class="text-muted mb-4">
            <i class="fas fa-info-circle text-primary me-1"></i>
            Muestra todos los créditos vencidos con el <strong>gestor original</strong> que los tenía asignados,
            el saldo cuando fueron reasignados y el saldo real al día de hoy.
            Útil para el cálculo de comisiones y seguimiento de cartera.
        </p>

        {{html()->form('GET', route('reportes.carteraVencidaHistorica.html'))->open()}}
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Vencimiento desde:</label>
                <input type="date" name="desde" class="form-control rounded-pill"
                       value="{{ request('desde') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Vencimiento hasta:</label>
                <input type="date" name="hasta" class="form-control rounded-pill"
                       value="{{ request('hasta', date('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Gestor (original):</label>
                {{ html()->select('cobrador[]', ['' => 'Todos los gestores'] + $listaCobradores, request('cobrador'))
                    ->class('form-control select2 rounded-pill')
                    ->style(['width' => '100%'])
                    ->attribute('multiple', false) }}
            </div>
        </div>

        <div class="row mt-4 justify-content-center">
            <div class="col-md-4 d-grid">
                <button type="submit" class="btn btn-primary rounded-pill py-2 shadow-sm">
                    <i class="fas fa-eye me-2"></i> Ver Reporte HTML
                </button>
            </div>
            <div class="col-md-4 d-grid">
                <button type="submit" name="excel" value="1" class="btn btn-success rounded-pill py-2 shadow-sm">
                    <i class="fas fa-file-excel me-2"></i> Exportar Excel
                </button>
            </div>
        </div>
        {{html()->form()->close()}}
    </div>
</div>
@endsection
