@extends('layouts.app')
@section('tituloPagina')
    @include('utlisComponents.btnBack',['url'=>route('reportes.index')])
    Préstamos Vencidos
@endsection
@section('content')
    {{html()->form('GET',route('reportes.prestamosVencidos.index'))->open()}}
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
         <div class="col-md-6">
            <button type="submit" name="excel" value="excel" class="btn btn-success w-100">Exportar a EXCEL <i class="fa fa-file-excel"></i></button>
         </div>
     </div>
    <br>
    {{html()->form()->close()}}
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                    <tr class="table-success">
                        <th># Préstamo</th>
                        <th>Cliente</th>
                        <th>Cobrador</th>
                        <th>Frecuencia</th>
                        <th>Monto Préstamo</th>
                        <th>Financiado</th>
                        <th>Abonado</th>
                        <th>Pendiente</th>
                        <th>Fecha Préstamo</th>
                        <th>Fecha Última Cuota</th>
                        <th>Acción</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($prestamosVencidos as $prestamo)
                        <tr>
                            <td>{{$prestamo->consecutivo}}</td>
                            <td>{{$prestamo->cliente->full_name}}</td>
                            <td>{{$prestamo->agente->full_name}}</td>
                            <td>{{$prestamo->forma_pago}}</td>
                            <td>{{$prestamo->moneda." ". number_format($prestamo->monto_prestamo,2)}}</td>
                            <td>{{$prestamo->moneda." ". number_format($prestamo->monto_financiado,2)}}</td>
                            <td>{{number_format($prestamo->suma_abonos,2)}}</td>
                            <td>{{number_format(($prestamo->monto_financiado - $prestamo->suma_abonos),2)}}</td>
                            <td>{{fecha_d_m_Y($prestamo->fecha_prestamo)}}</td>
                            <td>{{$prestamo->cuotas()->orderBy('id','desc')->first() ? fecha_d_m_Y($prestamo->cuotas()->orderBy('id','desc')->first()->fecha_cuota):"-"}}</td>
                            <td><a href="{{route('prestamos.show',$prestamo->id_enc)}}" target="_blank" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{$prestamosVencidos->appends(request()->all())->links()}}
            </div>
        </div>
    </div>
@endsection
