<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Estado Cuenta Cliente</title>
</head>
<body style="padding: 10px">
<div class="div" style="width: 100%;border: solid 1px black">
    <table style="width: 100%;border-collapse: collapse;border: 1px">
        <tr>
            <th style="width: 200px"><img src="{{asset('assets/img/LogoCrediNica.png')}}" style="width: 100%" alt=""></th>
            <th>
                <h2>Estado Cuenta Cliente: <br> {{$cliente->full_name}}</h2>
            </th>
        </tr>
    </table>
</div>
<br>
<div>
    <table style="width: 100%;border-collapse: collapse;border: 1px;text-align: left">
        <tr>
            <th>Cliente:</th>
            <td>{{$cliente->full_name}}</td>
            <th>Cédula</th>
            <td>{{$cliente->cedula}}</td>
            <th>Teléfono 1</th>
            <td>{{$cliente->telefono1}}</td>
            <th>Teléfono 2</th>
            <td>{{$cliente->telefono2}}</td>
        </tr>

        <tr>
            <th>Dirección:</th>
            <td>{{$cliente->direccion}}</td>
        </tr>
    </table>

    @foreach($prestamosCliente as $prestamo)
        <hr style="border-style: dashed">
        <h3>Préstamos</h3>
        <table style="width: 100%;border-collapse: collapse;border: 1px;text-align: left">
            <tr>
                <th>Fecha Préstamo:</th>
                <td>{{fecha_d_m_Y($prestamo->fecha_prestamo)}}</td>
                <th>Monto Préstamo:</th>
                <td>{{$prestamo->monto_prestamo}}</td>
                <th>Plazo (meses):</th>
                <td>{{$prestamo->plazo_pago}}</td>
                <th>Tasa:</th>
                <td>{{$prestamo->tasa_prestamo}}</td>
                <th>Forma de Pago:</th>
                <td>{{$prestamo->forma_pago}}</td>

            </tr>

            <tr>
                <th>Total Financiado:</th>
                <td>{{$prestamo->monto_financiado}}</td>
                <th>Total Intereses:</th>
                <td>{{$prestamo->interes_total_pagar}}</td>

                <th>Fecha Primer Pago:</th>
                <td>{{fecha_d_m_Y($prestamo->fecha_primer_pago)}}</td>

                <th>Total Último Pago:</th>
                <td>{{$prestamo->cuotas()->orderBy('id', 'desc')->first() ? fecha_d_m_Y($prestamo->cuotas()->orderBy('id', 'desc')->first()->fecha_cuota) : "-"}}</td>
            </tr>

            <tr>
                <th>Total Pagado:</th>
                <td>{{$prestamo->suma_abonos}}</td>
                <th>Total Pendiente:</th>
                <td>{{$prestamo->pendiente_abono}}</td>
                <th>Estado:</th>
                <td>{{$prestamo->estado_prestamo}}</td>
                <th>Tipo Desembolso:</th>
                <td>{{$prestamo->tipo_prestamo}}</td>
                <th>Tipo Destino:</th>
                <td>{{$prestamo->tipo_destino_prestamo}}</td>
            </tr>
            <tr>
                <th>Total Pendiente Capital</th>
                <td>{{$prestamo->total_pendiente_capital}}</td>
                <th>Total Pendiente Interes</th>
                <td>{{$prestamo->total_pendiente_interes}}</td>
            </tr>
        </table>
        <br>
        <h3>Detalle Abonos</h3>
        <hr>
        @foreach($prestamo->abonos as $abono)
            <table style="width: 50%;border-collapse: collapse;border: 1px;text-align: left">
                <tr>
                    <th>Fecha Abono:</th>
                    <td>{{fecha_d_m_Y($abono->fecha_abono)}}</td>
                    <th>Monto Abono:</th>
                    <td>{{$abono->total_abonado}}</td>
                </tr>
            </table>
        @endforeach

        <hr>
    @endforeach


    {{--    <table style="width: 100%;border-collapse: collapse;border: 1px black;text-align: left">--}}
    {{--        <tr style="border: 1px black;background: silver">--}}
    {{--            <th>#</th>--}}
    {{--            <th>Consecutivo</th>--}}
    {{--            <th>Cédula</th>--}}
    {{--            <th>Cliente</th>--}}
    {{--            <th>Forma Pago</th>--}}
    {{--            <th>Fecha Abono</th>--}}
    {{--            <th>Agente</th>--}}
    {{--            <th>Abono Capital</th>--}}
    {{--            <th>Abono Interes</th>--}}
    {{--            <th>Total Abonado</th>--}}
    {{--        </tr>--}}
    {{--        @foreach($abonosDia as $abonos)--}}
    {{--            <tr>--}}
    {{--                <td>{{$loop->index+1}}</td>--}}
    {{--                <td>{{$abonos->prestamo->consecutivo}}</td>--}}
    {{--                <td>{{$abonos->prestamo->cliente->cedula}}</td>--}}
    {{--                <td>{{$abonos->prestamo->cliente->full_name}}</td>--}}
    {{--                <td>{{$abonos->prestamo->forma_pago}}</td>--}}
    {{--                <td>{{fecha_d_m_Y($abonos->fecha_abono)}}</td>--}}
    {{--                <td>{{$abonos->prestamo->agente->full_name}}</td>--}}
    {{--                <td>{{$abonos->total_abonado_capital}}</td>--}}
    {{--                <td>{{$abonos->total_abonado_interes}}</td>--}}
    {{--                <td>{{$abonos->total_abonado}}</td>--}}
    {{--            </tr>--}}
    {{--        @endforeach--}}
    {{--    </table>--}}
</div>

</body>
</html>
