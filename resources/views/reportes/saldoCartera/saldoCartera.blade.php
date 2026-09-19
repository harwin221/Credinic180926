@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('reportes.index')])
    Saldo Cartera
@endsection
@section('content')
    {{html()->form('GET',route('reportes.saldoCartera'))->open()}}
    <div class="row g-2">
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

        <div class="col-md-3">
            <div class="form-group">
                <label for="estado"><strong>Estado del Préstamo:</strong></label>
                {{html()->select('estado',['0'=>'Todos','1'=>'Pendientes / Activos','2'=>'Cancelados','3'=>'Vencidos','4'=>'Anulados','5'=>'Saneados'],request('estado'))->class('form-control')->id('estado')}}
            </div>
        </div>
    </div>

    <div class="row g-2 mt-1">
        <div class="col-md-3">
            <div class="form-group">
                <label for="fecha_fin"><strong>Fecha de Corte:</strong></label>
                <input type="date" id="fecha_fin" name="fin" required value="{{request('fin')}}" class="form-control">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="tipo_vista"><strong>Tipo de Vista:</strong></label>
                <select name="tipo_vista" id="tipo_vista" class="form-control">
                    <option value="detallado" {{ request('tipo_vista','detallado') == 'detallado' ? 'selected' : '' }}>
                        Detallado (todos los préstamos)
                    </option>
                    <option value="resumido" {{ request('tipo_vista') == 'resumido' ? 'selected' : '' }}>
                        Resumido (resumen por cobrador)
                    </option>
                </select>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-12">
            <p style="color: blue"><b><small>*No se recomienda usar intervalos de fechas muy grandes*</small></b></p>
        </div>
    </div>

    <div class="row g-2 mt-1">
        <div class="col-md-4">
            <button type="submit" name="html" value="html" class="btn btn-info w-100" formtarget="_blank">
                <i class="fa fa-table"></i> Ver Reporte HTML
            </button>
        </div>
        <div class="col-md-4">
            <button type="submit" name="pdf" value="pdf" class="btn btn-primary w-100" formtarget="_blank">
                <i class="fa fa-file-pdf"></i> Generar PDF
            </button>
        </div>
        <div class="col-md-4">
            <button type="submit" name="excel" value="excel" class="btn btn-success w-100">
                <i class="fa fa-file-excel"></i> Exportar a Excel
            </button>
        </div>
    </div>
    {{html()->form()->close()}}

@endsection
