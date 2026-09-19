@extends('layouts.app')
@section('content')
    {{html()->form('GET',route('reportes.cartera_diaria'))->open()}}
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="cobrador"><b>Cobrador:</b></label>
                {{html()->select('cobrador',$listaCobradores)->class('form-control select2')->required()}}
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="fecha"><b>Fecha:</b></label>
                <input type="date" name="fecha" class="form-control" id="fecha" required>
            </div>
        </div>

    </div>
    <br>
    <div class="row justify-content-center">
        <div class="col-md-4">
            <button type="submit" name="pdf" value="pdf" class="btn btn-primary w-100" formtarget="_blank">Generar Reporte PDF <i class="fa fa-file-pdf"></i></button>
        </div>
        <div class="col-md-4">
            <button type="submit" name="excel" value="excel" class="btn btn-success w-100">Exportar a EXCEL <i class="fa fa-file-excel"></i></button>
        </div>
    </div>
    {{html()->form()->close()}}
@endsection
