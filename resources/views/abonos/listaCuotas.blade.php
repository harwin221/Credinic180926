
<div class="row">
    <div class="col-md-3 text-center">
        <h3>Total Pendiente: <br><strong>{{$prestamo->moneda." ". number_format( $prestamo->suma_cuotas - $prestamo->suma_abonos,2)}}</strong></h3>
    </div>

    <div class="col-md-3 text-center">
        <h3>Total Abonado: <br><strong>{{$prestamo->moneda." ". number_format($prestamo->suma_abonos,2)}}</strong></h3>
    </div>

    <div class="col-md-3 text-center">
        <h3>Total Vencido <br><strong>{{$prestamo->moneda." ". number_format($prestamo->suma_pendiente_cuotas_vencidas,2)}}</strong></h3>
    </div>

    <div class="col-md-3 text-center">
        <h3>% Abonado: <br><strong>{{number_format(($prestamo->suma_abonos / $prestamo->suma_cuotas)*100,2)}}</strong></h3>
    </div>


</div>
<br>
<div class="row">
{{--    <div class="col-md-6 text-center">--}}
{{--        <a href="javascript:void(0)" class="btn btn-success" id="btnAbonarCuota">Abonar Cuotas Seleccionadas <i class="fa fa-cash-register"></i></a>--}}
{{--    </div>--}}

    <div class="col-md-12 text-center">
        <a href="javascript:void(0)" class="btn btn-info" id="btnAbonarOtroMonto">Abonar al Desembolso <i class="fa fa-cash-register"></i></a>
    </div>
</div>
<br>
<table class="table table-sm">
    <thead class="table-info">
    <tr>
        <th style="width: 50px">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="chkSelAll">
                <label class="form-check-label fw-bold text-uppercase" for="chkSelAll"></label>
            </div>
        </th>
        <th>#</th>
        <th>Fecha Cuota</th>
        <th>Monto Cuota</th>
        <th>Interes</th>
        <th>Capital</th>
        <th>Mora</th>
        <th>Abonado</th>
    </tr>
    </thead>

    <tbody>
    @foreach($prestamoCuotas as $cuota)
        @if(($cuota->monto_pendiente_cuota) > 0)
        <tr @if(\Carbon\Carbon::createFromFormat('Y-m-d',$cuota->fecha_cuota)->toDateString() < \Carbon\Carbon::now()->toDateString()) class="table-danger" @endif>
            <td>
                <div class="form-check form-switch">
                    <input class="form-check-input chkCuota" name="chkCuotas" type="checkbox" id="{{$cuota->id_enc}}" value="{{$cuota->id_enc}}">
                    <label class="form-check-label fw-bold text-uppercase"></label>
                </div>
            </td>
            <td>{{$cuota->numero_cuota}}</td>
            <td>{{fecha_d_m_Y($cuota->fecha_cuota)}}</td>
            <td>{{$cuota->prestamo->moneda." ". number_format( ($cuota->monto_pendiente_cuota),2)}}</td>
            <td>{{$cuota->prestamo->moneda." ". number_format( ($cuota->total_pendiente_interes_cuota),2)}}</td>
            <td>{{$cuota->prestamo->moneda." ". number_format( ($cuota->total_pendiente_capital_cuota),2)}}</td>
            <td>{{$cuota->prestamo->moneda." ". number_format( ($cuota->total_pendiente_mora_cuota),2)}}</td>
            <td>{{$cuota->prestamo->moneda." ". number_format( ($cuota->suma_abonos),2)}}</td>
        </tr>
        @endif
    @endforeach
    </tbody>
</table>
