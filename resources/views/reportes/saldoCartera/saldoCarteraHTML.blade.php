<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saldo de Cartera - CrediNica</title>
    <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/fontawesome-free-6.4.0-web/css/all.min.css')}}" rel="stylesheet">
    @include('reportes._corporate_styles')
    <style>@page { size: landscape; margin: 1cm; }</style>
</head>
<body>
    <div class="screen-actions no-print">
        {{html()->form('GET', route('reportes.saldoCartera'))->open()}}
            <input type="hidden" name="cliente"    value="{{$clienteSel}}">
            <input type="hidden" name="cobrador"   value="{{$cobradorSel}}">
            <input type="hidden" name="frecuencia" value="{{$frecuenciaSel}}">
            <input type="hidden" name="estado"     value="{{$estado}}">
            <input type="hidden" name="fin"        value="{{$fin}}">
            <input type="hidden" name="tipo_vista" value="{{$tipo_vista}}">
            <button type="submit" name="excel" value="excel" class="btn-act success"><i class="fas fa-file-excel"></i> Excel</button>
            <button type="submit" name="pdf"   value="pdf"   class="btn-act primary" formtarget="_blank"><i class="fas fa-file-pdf"></i> PDF</button>
            <button type="button" class="btn-act primary" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
            <a href="{{ route('reportes.saldoCartera') }}" class="btn-act"><i class="fas fa-arrow-left"></i> Volver</a>
        {{html()->form()->close()}}
    </div>

    <div class="report-wrapper wide">
        <div class="rpt-header">
            <img src="{{asset('assets/img/LogoCrediNica.png')}}" alt="CrediNica">
            <div class="co-name">CrediNica</div>
            <div class="rpt-title">Saldo de Cartera &mdash; Vista {{ strtoupper($tipo_vista) }}</div>
            <div class="rpt-meta">Generado: {{ date('d/m/Y') }} &nbsp;|&nbsp; {{ date('h:i A') }}</div>
        </div>

        @if($tipo_vista == 'resumido')
            @php
                $totClientes = 0; $totCapital = 0; $totInteres = 0; $totTotal = 0;
                foreach($resumen as $r) { $totClientes += $r->clientes; $totCapital += $r->capital; $totInteres += $r->interes; $totTotal += $r->total; }
            @endphp
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th><th>Cobrador / Agente</th>
                        <th class="text-right">N° Clientes</th>
                        <th class="text-right">Saldo Capital</th><th class="text-right">Saldo Interés</th>
                        <th class="text-right">Total Pendiente</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($resumen as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="bold">{{ strtoupper($row->nombre) }}</td>
                        <td class="text-right">{{ $row->clientes }}</td>
                        <td class="text-right num">C$ {{ number_format($row->capital, 2) }}</td>
                        <td class="text-right num">C$ {{ number_format($row->interes, 2) }}</td>
                        <td class="text-right num bold">C$ {{ number_format($row->total, 2) }}</td>
                    </tr>
                    @empty
                        <tr><td colspan="6" class="text-center" style="padding:30px;color:#718096;">No se encontraron registros.</td></tr>
                    @endforelse
                </tbody>
                @if(count($resumen) > 0)
                <tfoot>
                    <tr class="row-grand-total">
                        <td colspan="2" class="text-right">TOTAL GENERAL</td>
                        <td class="text-right">{{ $totClientes }}</td>
                        <td class="text-right num">C$ {{ number_format($totCapital, 2) }}</td>
                        <td class="text-right num">C$ {{ number_format($totInteres, 2) }}</td>
                        <td class="text-right num">C$ {{ number_format($totTotal, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        @else
            @php
                $totalClientes=0; $totalCapitalPend=0; $totalInteresPend=0; $totalMontoPend=0;
                $cobradorActual=null; $nombreCobradorActual='';
                $subClientes=0; $subCapital=0; $subInteres=0; $subTotal=0;
                $contadorGlobal=0;
            @endphp

            @forelse($prestamos as $prestamo)
                @php
                    $capPend     = $prestamo->monto_prestamo - ($prestamo->suma_abonos_capital ?? 0);
                    $intPend     = ($prestamo->monto_financiado - $prestamo->monto_prestamo) - ($prestamo->suma_abonos_interes ?? 0);
                    $monPend     = $capPend + $intPend;
                    $cambioAgente = ($cobradorActual !== null && $cobradorActual != $prestamo->agente_id);
                @endphp

                @if($cambioAgente)
                    <table class="data-table" style="margin-bottom:0;">
                        <tbody>
                            <tr class="row-subtotal">
                                <td colspan="3" class="text-right">Subtotal {{ strtoupper($nombreCobradorActual) }} &mdash; {{ $subClientes }} clientes</td>
                                <td colspan="3"></td>
                                <td class="text-right num">C$ {{ number_format($subCapital, 2) }}</td>
                                <td class="text-right num">C$ {{ number_format($subInteres, 2) }}</td>
                                <td class="text-right num">C$ {{ number_format($subTotal, 2) }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                    @php $subClientes=0; $subCapital=0; $subInteres=0; $subTotal=0; @endphp
                @endif

                @if($cobradorActual != $prestamo->agente_id)
                    <div class="section-title">Cobrador: {{ strtoupper($prestamo->agente_nombre ?? 'SIN ASIGNAR') }}</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th><th>N° Préstamo</th><th>Cliente</th>
                                <th class="text-right">Capital</th><th class="text-right">Interés</th>
                                <th class="text-right">Total Prést.</th><th class="text-center">Plazo</th>
                                <th class="text-right">Cap. Pend.</th><th class="text-right">Int. Pend.</th>
                                <th class="text-right">Monto Pend.</th>
                            </tr>
                        </thead>
                        <tbody>
                    @php $cobradorActual = $prestamo->agente_id; $nombreCobradorActual = $prestamo->agente_nombre ?? 'SIN ASIGNAR'; @endphp
                @endif

                @php
                    $subClientes++; $subCapital+=$capPend; $subInteres+=$intPend; $subTotal+=$monPend;
                    $totalClientes++; $totalCapitalPend+=$capPend; $totalInteresPend+=$intPend; $totalMontoPend+=$monPend;
                    $contadorGlobal++;
                @endphp
                <tr>
                    <td>{{ $contadorGlobal }}</td>
                    <td class="bold">{{ $prestamo->consecutivo }}</td>
                    <td>{{ strtoupper($prestamo->cliente_nombre ?? '-') }}</td>
                    <td class="text-right num">{{ number_format($prestamo->monto_prestamo, 2) }}</td>
                    <td class="text-right num">{{ number_format($prestamo->monto_financiado - $prestamo->monto_prestamo, 2) }}</td>
                    <td class="text-right num">{{ number_format($prestamo->monto_financiado, 2) }}</td>
                    <td class="text-center">{{ strtoupper($prestamo->plazo . ' ' . $prestamo->forma_pago) }}</td>
                    <td class="text-right num">C$ {{ number_format($capPend, 2) }}</td>
                    <td class="text-right num">C$ {{ number_format($intPend, 2) }}</td>
                    <td class="text-right num bold">C$ {{ number_format($monPend, 2) }}</td>
                </tr>

                @if($loop->last)
                        </tbody>
                    </table>
                    <table class="data-table" style="margin-bottom:0;">
                        <tbody>
                            <tr class="row-subtotal">
                                <td colspan="3" class="text-right">Subtotal {{ strtoupper($nombreCobradorActual) }} &mdash; {{ $subClientes }} clientes</td>
                                <td colspan="3"></td>
                                <td class="text-right num">C$ {{ number_format($subCapital, 2) }}</td>
                                <td class="text-right num">C$ {{ number_format($subInteres, 2) }}</td>
                                <td class="text-right num">C$ {{ number_format($subTotal, 2) }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            @empty
                <div style="text-align:center;padding:48px;color:#718096;">No se encontraron registros.</div>
            @endforelse

            @if(count($prestamos) > 0)
            <table class="data-table" style="margin-top:4px;">
                <tbody>
                    <tr class="row-grand-total">
                        <td colspan="3" class="text-right">TOTAL GENERAL &mdash; {{ $totalClientes }} clientes</td>
                        <td colspan="3"></td>
                        <td class="text-right num">C$ {{ number_format($totalCapitalPend, 2) }}</td>
                        <td class="text-right num">C$ {{ number_format($totalInteresPend, 2) }}</td>
                        <td class="text-right num">C$ {{ number_format($totalMontoPend, 2) }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            @endif
        @endif

        <div class="rpt-footer">Documento generado por CrediNica &mdash; {{ date('d/m/Y h:i A') }} &mdash; Uso interno exclusivo</div>
    </div>
    <script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
</body>
</html>
