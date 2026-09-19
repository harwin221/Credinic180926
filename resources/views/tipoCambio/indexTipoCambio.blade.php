@extends('layouts.app')
@section('tituloPagina')
    Tipo de Cambio
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            {{html()->form('POST',route('configuracion.tipoCambio.import'))->acceptsFiles()->open()}}
            <input type="file" name="tipoCambio" required>
            <button type="submit" class="btn btn-primary btn-sm">Importar Tipo de Cambio</button>
            {{html()->form()->close()}}
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead class="table-success">
                    <tr>
                        <th>#</th>
                        <th>Dia</th>
                        <th>Valor</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($tipoCambioDia as $cambio)
                        <tr>
                            <td style="width: 50px">{{$loop->index+1}}</td>
                            <td>{{$cambio->fecha}}</td>
                            <td>{{$cambio->valor}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
