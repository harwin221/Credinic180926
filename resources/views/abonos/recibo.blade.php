<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Recibo</title>

    <style>
        @media print {
            body {
                width: 80mm;
                margin: 0;
                font-family: Arial, sans-serif;
            }

            #imprimir, #btnVolver {
                display: none;
            }
        }

        .ticket {
            text-align: left;
            padding: 10px;
            border: solid 1px black;
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }

        .monto-recibido {
            padding: 12px 0;
            margin: 12px 0;
            text-align: center;
        }

        .monto-recibido-titulo {
            font-size: 15px;
            font-weight: bold;
            margin: 0 0 5px 0;
        }

        .monto-recibido-valor {
            font-size: 28px;
            font-weight: bold;
            margin: 0;
        }

        .linea-separador {
            border-top: 1px dashed #000;
            margin: 10px 0;
            height: 0;
        }

        .linea-separador-punteada {
            border-top: 1px dotted #000;
            margin: 10px 0;
            height: 0;
        }

        .seccion-titulo {
            font-weight: bold;
            text-align: center;
            margin: 12px 0 6px 0;
            text-decoration: underline;
        }

        .fila-dato {
            display: flex;
            justify-content: space-between;
            margin: 6px 0;
            font-size: 15px;
            line-height: 1.5;
        }

        .mensaje-final {
            text-align: center;
            margin: 18px 0 6px 0;
            font-weight: bold;
            font-size: 16px;
        }

        .mensaje-secundario {
            text-align: center;
            margin: 5px 0;
            font-size: 14px;
            font-style: italic;
        }
    </style>
</head>
<body>
<?php
    // Usar la fecha del abono para todos los cálculos (no la fecha actual)
    $fechaReferencia = \Carbon\Carbon::parse($abono->fecha_abono);

    // Calcular cuota del día (del día que se hizo el pago)
    $cuotaDelDia = $abono->prestamo->cuotas()
        ->whereDate('fecha_cuota', $fechaReferencia->toDateString())
        ->first();
    $montoCuotaDia = $cuotaDelDia ? $cuotaDelDia->monto_cuota : 0;

    // Calcular mora/atraso (cuotas vencidas ANTES de la fecha del abono)
    // Necesitamos calcular cuánto estaba pendiente EN EL MOMENTO de este abono
    $cuotasVencidas = $abono->prestamo->cuotas()
        ->whereDate('fecha_cuota', '<', $fechaReferencia->toDateString())
        ->get();
    
    $montoAtraso = 0;
    foreach($cuotasVencidas as $cuota) {
        // Calcular cuánto se había abonado a esta cuota ANTES de este abono
        $abonadoAntes = \App\Models\prestamoCuotaAbonoModel::where('prestamo_cuota_id', $cuota->id)
            ->whereHas('abono', function($query) use ($abono) {
                $query->where('id', '<', $abono->id)
                      ->where('estado', 1);
            })
            ->where('estado', 1)
            ->sum('monto_abono');
        
        // Calcular cuánto estaba pendiente de esta cuota
        $pendienteEnEseMomento = $cuota->monto_cuota - $abonadoAntes;
        
        // Si había algo pendiente, sumarlo al atraso
        if($pendienteEnEseMomento > 0) {
            $montoAtraso += $pendienteEnEseMomento;
        }
    }

    // Calcular días de mora (desde la cuota más antigua vencida hasta la fecha del abono)
    $cuotaMasAntigua = $cuotasVencidas->filter(function($cuota) use ($abono) {
        // Solo considerar cuotas que tenían saldo pendiente antes de este abono
        $abonadoAntes = \App\Models\prestamoCuotaAbonoModel::where('prestamo_cuota_id', $cuota->id)
            ->whereHas('abono', function($query) use ($abono) {
                $query->where('id', '<', $abono->id)
                      ->where('estado', 1);
            })
            ->where('estado', 1)
            ->sum('monto_abono');
        
        return $abonadoAntes < $cuota->monto_cuota;
    })->sortBy('fecha_cuota')->first();
    
    $diasMora = $cuotaMasAntigua ? $fechaReferencia->diffInDays(\Carbon\Carbon::parse($cuotaMasAntigua->fecha_cuota)) : 0;

    // Total a pagar para ponerse al día (en el momento del abono)
    $totalAPagar = $montoCuotaDia + $montoAtraso;

    // Saldo anterior = total del plan - lo abonado hasta ANTES de este abono
    // Saldo nuevo    = total del plan - lo abonado hasta ESTE abono (inclusive)
    // Usamos JOIN con cuotas activas (igual que suma_abonos) para ignorar registros basura

    $abonadoHastaAntesDeEste = \Illuminate\Support\Facades\DB::table('prestamos')
        ->join('prestamo_coutas as PC', 'PC.prestamo_id', 'prestamos.id')
        ->join('prestamo_cuota_abono as PCA', 'PCA.prestamo_cuota_id', 'PC.id')
        ->join('abonos as A', 'A.id', 'PCA.abono_id')
        ->where('prestamos.id', $abono->prestamo_id)
        ->where('PCA.estado', 1)
        ->where('A.estado', 1)
        ->where('A.id', '<', $abono->id)
        ->sum('PCA.monto_abono');

    $abonadoHastaEsteAbono = \Illuminate\Support\Facades\DB::table('prestamos')
        ->join('prestamo_coutas as PC', 'PC.prestamo_id', 'prestamos.id')
        ->join('prestamo_cuota_abono as PCA', 'PCA.prestamo_cuota_id', 'PC.id')
        ->join('abonos as A', 'A.id', 'PCA.abono_id')
        ->where('prestamos.id', $abono->prestamo_id)
        ->where('PCA.estado', 1)
        ->where('A.estado', 1)
        ->where('A.id', '<=', $abono->id)
        ->sum('PCA.monto_abono');

    $saldoAnterior = $abono->prestamo->suma_cuotas - $abonadoHastaAntesDeEste;
    $nuevoSaldo    = $abono->prestamo->suma_cuotas - $abonadoHastaEsteAbono;

    // Nunca mostrar negativo (centavos de redondeo)
    if ($nuevoSaldo < 0)    $nuevoSaldo    = 0;
    if ($saldoAnterior < 0) $saldoAnterior = 0;
?>

<div class="ticket" style="width: 80mm">
    <div style="width: 100%;text-align: center;margin-top: 10px">
        <img src="{{asset('assets/img/LogoCrediNica.png')}}" style="width: 140px;margin: auto" alt="">
        <p style="margin: 8px 0;font-size: 15px;line-height: 1.5"><strong>Fecha Impresión: </strong>{{date('d-m-Y')}}</p>
        
        <div class="linea-separador"></div>
        
        <p style="margin: 5px 0;font-size: 15px;line-height: 1.5"><strong>No. Recibo: </strong>REC-{{str_pad($abono->id, 6, '0', STR_PAD_LEFT)}}</p>
        <p style="margin: 5px 0;font-size: 15px;line-height: 1.5"><strong>No. Crédito: </strong>{{$abono->prestamo->consecutivo}}</p>
        <p style="margin: 5px 0;font-size: 15px;line-height: 1.5"><strong>Fecha Pago: </strong>{{date('d-m-Y h:ia', strtotime($abono->created_at))}}</p>
        
        <div class="linea-separador"></div>
        
        <p style="margin: 5px 0;font-size: 14px;text-align: center;line-height: 1.5">CLIENTE:</p>
        <p style="margin: 5px 0;font-size: 17px;font-weight: bold;text-align: center;line-height: 1.4">{{strtoupper($abono->prestamo->cliente->full_name)}}</p>
        
        <div class="linea-separador"></div>
        
        <div style="text-align: left;padding: 0 10px">
            <div class="fila-dato">
                <span><strong>Cuota del Día:</strong></span>
                <span><strong>C$ {{number_format($montoCuotaDia, 2)}}</strong></span>
            </div>
            <div class="fila-dato">
                <span><strong>Mora/Atraso:</strong></span>
                <span><strong>C$ {{number_format($montoAtraso, 2)}}</strong></span>
            </div>
            <div class="fila-dato">
                <span><strong>Días Mora:</strong></span>
                <span><strong>{{$diasMora}}</strong></span>
            </div>
            
            <div class="linea-separador-punteada"></div>
            
            <div class="fila-dato" style="font-size: 16px;margin-top: 8px">
                <span><strong>Total a pagar:</strong></span>
                <span><strong>C$ {{number_format($totalAPagar, 2)}}</strong></span>
            </div>
        </div>
        
        <div class="linea-separador"></div>
        
        <div class="monto-recibido">
            <p class="monto-recibido-titulo">MONTO RECIBIDO</p>
            <p class="monto-recibido-valor">C$ {{number_format($abono->total_abonado, 2)}}</p>
        </div>
        
        <p style="margin: 8px 0;font-size: 14px;font-style: italic;text-align: center;line-height: 1.5">CONCEPTO: ABONO DE CRÉDITO</p>
        
        <div style="text-align: left;padding: 0 10px;margin-top: 12px">
            <div class="fila-dato">
                <span>Saldo Anterior:</span>
                <span>C$ {{number_format($saldoAnterior, 2)}}</span>
            </div>
            <div class="fila-dato" style="font-weight: bold;font-size: 16px">
                <span>Nuevo Saldo:</span>
                <span>C$ {{number_format($nuevoSaldo, 2)}} @if($nuevoSaldo == 0 && $saldoAnterior > 0)<span style="color: green">✓</span>@endif</span>
            </div>
            @if($nuevoSaldo == 0 && $saldoAnterior > 0)
                <div style="background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; padding: 12px; margin-top: 10px; text-align: center;">
                    <p style="margin: 0; font-size: 16px; color: #155724; font-weight: bold; line-height: 1.4;">✓ CRÉDITO CANCELADO</p>
                </div>
            @endif
        </div>
        
        <div class="linea-separador"></div>
        
        <p class="mensaje-final">¡GRACIAS POR SU PAGO!</p>
        <p class="mensaje-secundario">CONSERVE ESTE DOCUMENTO</p>
        
        <br>
        <p style="margin: 8px 0;font-size: 15px;line-height: 1.5"><strong>Agente: </strong>{{$abono->user_create->full_name}}</p>
        
        @if(request()->get('reimpresion'))
            <div class="linea-separador"></div>
            <p class="mensaje-secundario">Reimpresión</p>
            <p class="mensaje-secundario">{{date('d-m-Y h:i:s a')}}</p>
            <div class="linea-separador"></div>
        @endif
        
        @if($abono->estado===2)
            <div class="linea-separador"></div>
            <p style="margin: 5px 0;text-align: center;font-weight: bold;color: red">ANULADO</p>
            <div class="linea-separador"></div>
        @endif
    </div>
</div>
<br>
<div style="text-align: center; margin: 10px 0;">
    <button onclick="cerrarVentana();" id="btnVolver" style="padding: 10px 20px; margin-right: 10px; background-color: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">
        ← Volver
    </button>
    <button onclick="window.print();" id="imprimir" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">
        🖨️ Guardar Recibo
    </button>
</div>

<script>
function cerrarVentana() {
    // Si fue abierto con window.open, cierra la ventana
    if (window.opener) {
        window.close();
    } else {
        // Si no, intenta volver atrás
        if (window.history.length > 1) {
            window.history.back();
        } else {
            // Si no hay historial, cierra la pestaña (funciona en móvil)
            window.close();
        }
    }
}

// Detectar si es móvil y ajustar comportamiento
if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
    // En móvil, hacer que el botón volver sea más visible
    document.getElementById('btnVolver').style.fontSize = '16px';
    document.getElementById('btnVolver').style.padding = '12px 24px';
}
</script>
</body>
</html>
