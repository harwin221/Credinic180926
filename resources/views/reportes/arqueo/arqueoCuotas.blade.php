<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Detalle de Cuotas</title>
</head>
<body style="font-size: 12px">
<div class="div" style="width: 100%;border: solid 1px black">
    <table style="width: 100%;border-collapse: collapse;border: 1px">
        <tr>
            <th style="width: 150px"><img src="{{asset('assets/img/LogoCrediNica.png')}}" style="width: 100%" alt=""></th>
            <th>
                <h3>Detalle de Cuotas: <span><br> <b>{{$fecha}}</b></span></h3>
                <h3>Cobrador: <br> {{$cobradorObj->full_name}}</h3>
            </th>
        </tr>
    </table>
</div>
<br>
<div>
    <h3>Total Recuperado: <b>{{$arqueo->efectivo}}</b></h3>
    <h3>Tipo de Cambio: <b>{{$arqueo->tipocambio}}</b></h3>
    <h3>Total córdobas: <b>{{$arqueo->total_cordoba?$arqueo->total_cordoba:'0'}}</b></h3>
    <h3>Total dólares: {{$arqueo->total_dolar?$arqueo->total_dolar:'0'}} | {{$arqueo->total_dolar?($arqueo->total_dolar * $arqueo->tipocambio):'0'}} </h3>
    <h3>Subtotal: {{$arqueo->total_cordoba + ($arqueo->total_dolar * $arqueo->tipocambio)}}</h3>
    <h3>Diferencia: {{$arqueo->efectivo - ($arqueo->total_cordoba + ($arqueo->total_dolar * $arqueo->tipocambio))}}</h3>
</div>
<br>
<div>
    <table style="width: 100%;border-collapse: collapse;border: 1px black;text-align: left">
        <tr style="border: 1px black;background: silver">
            <th>#</th>
            <th>Consecutivo</th>
            <th>Cliente</th>
            <th>Capital</th>
            <th>Interes</th>
            <th>Total Abonado</th>
        </tr>
            <?php $suma = 0; ?>
        @foreach($abonos as $ab)
            <?php $suma += $ab->total_abonado?>

            <tr>
                <td>{{$loop->index+1}}</td>
                <td>{{$ab->prestamo->consecutivo}}</td>
                <td>{{$ab->prestamo->cliente->full_name}}</td>
                <td>{{$ab->total_abonado_capital}}</td>
                <td>{{$ab->total_abonado_interes}}</td>
                <td>{{$ab->total_abonado}}</td>
            </tr>
        @endforeach
        <tr style="background: lightgrey">
            <th colspan="4" style="text-align: right;margin-right: 10px">Total</th>
            <th></th>
            <th>{{number_format($suma,2)}}</th>
        </tr>
    </table>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <table style="text-align: center;width: 100%">
        <tr>
            <td>________________________________________</td>
        </tr>
        <tr>
            <td>{{$cobradorObj->full_name}}</td>
        </tr>
    </table>
</div>

</body>
</html>
