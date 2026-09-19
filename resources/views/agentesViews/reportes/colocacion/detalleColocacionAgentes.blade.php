@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('agentes.reportes.index')])
    Detalle de Recuperación
@endsection
@section('content')
    {{html()->form('GET',route('agentes.reportes.recuperacion'))->open()}}
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="inicio"><strong>Fecha desde:</strong></label>
                <input type="date" name="desde" class="form-control" value="{{request('desde')}}" id="desde">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="inicio"><strong>Fecha hasta:</strong></label>
                <input type="date" name="hasta" class="form-control" value="{{request('hasta')}}" id="hasta">
            </div>
        </div>
    </div>
    <br>

    <div class="row">
        <div class="col-md-8">
            <button type="submit" class="btn btn-primary w-100">Generar Lista <i class="fa fa-cog"></i></button>
        </div>
        <div class="col-md-2">
            <button type="submit" disabled name="pdf" value="pdf" class="btn btn-danger w-100">PDF <i class="fa fa-file-pdf"></i></button>
        </div>
        <div class="col-md-2">
            <button type="submit" disabled name="excel" value="excel" class="btn btn-success w-100">EXCEL <i class="fa fa-file-excel"></i></button>
        </div>
    </div>
    {{html()->form()->close()}}
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                    <tr class="table-success">
                        <th>#</th>
                        <th>Día</th>
                        <th>Monto C$</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $suma = 0;?>
                    @foreach($sumaAbonoDia as  $ind=> $abonos)
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$ind}}</td>
                            <td>{{number_format($abonos,2)}}</td>
                        </tr>
                            <?php $suma += $abonos;?>
                    @endforeach
                    <tr class="table-success">
                        <th colspan="2" style="text-align: right;font-size: 22px">Total</th>
                        <th style="font-size: 22px">{{number_format($suma,2)}}</th>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
