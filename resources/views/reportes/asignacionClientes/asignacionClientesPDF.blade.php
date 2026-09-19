<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignación de Clientes</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 9px; color: #333; padding: 10px; }
        .header { width: 100%; margin-bottom: 15px; border-bottom: 2px solid #1f9cb5; padding-bottom: 10px; }
        .header-content { display: table; width: 100%; }
        .logo-section { display: table-cell; width: 150px; vertical-align: middle; }
        .logo-section img { width: 140px; }
        .title-section { display: table-cell; vertical-align: middle; text-align: center; }
        .title-section h1 { font-size: 18px; color: #1f9cb5; margin-bottom: 5px; }
        .title-section p { font-size: 9px; color: #666; }
        .filters-section { background-color: #f8f9fa; padding: 8px; margin-bottom: 10px; border-left: 3px solid #1f9cb5; }
        .filters-section h3 { font-size: 10px; color: #1f9cb5; margin-bottom: 5px; }
        .filter-item { display: inline-block; margin-right: 15px; font-size: 9px; }
        .cobrador-section { margin-top: 15px; page-break-inside: avoid; }
        .cobrador-header { background-color: #1f9cb5; color: white; padding: 6px 8px; font-size: 10px; font-weight: bold; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 8px; }
        table thead { background-color: #e9ecef; }
        table thead th { padding: 5px 3px; text-align: left; font-weight: bold; border: 1px solid #dee2e6; }
        table tbody td { padding: 4px 3px; border: 1px solid #dee2e6; }
        table tbody tr:nth-child(even) { background-color: #f8f9fa; }
        .subtotal-row { background-color: #d1ecf1 !important; font-weight: bold; }
        .subtotal-row td { padding: 6px 3px; border-top: 2px solid #1f9cb5; }
        .gran-total-section { margin-top: 15px; background-color: #1f9cb5; color: white; padding: 10px; }
        .gran-total-section h2 { font-size: 14px; margin-bottom: 8px; text-align: center; }
        .totales-grid { display: table; width: 100%; font-size: 10px; }
        .total-item { display: table-cell; text-align: center; padding: 5px; }
        .total-item .label { font-size: 8px; opacity: 0.9; }
        .total-item .value { font-size: 12px; font-weight: bold; margin-top: 3px; }
        .footer { margin-top: 15px; padding-top: 8px; border-top: 1px solid #dee2e6; font-size: 8px; color: #666; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo-section">
                <img src="{{public_path('assets/img/LogoCrediNica.png')}}" alt="CrediNica">
            </div>
            <div class="title-section">
                <h1>ASIGNACIÓN DE CLIENTES</h1>
                <p>Reporte de Préstamos por Cobrador</p>
            </div>
        </div>
    </div>

    <div class="filters-section">
        <h3>FILTROS APLICADOS:</h3>
        <div class="filter-item">
            <strong>Cobrador:</strong> {{request('cobrador') ? \App\Models\User::find(decode(request('cobrador')))->full_name : 'Todos'}}
        </div>
    </div>

    @php
        $prestamosPorCobrador = $prestamos->groupBy(function($prestamo) {
            return $prestamo->agente->id;
        });
        $granTotalMonto = 0;
        $granTotalFinanciado = 0;
        $totalRegistros = 0;
    @endphp

    @foreach($prestamosPorCobrador as $cobradorId => $prestamosGrupo)
        @php
            $cobrador = $prestamosGrupo->first()->agente;
            $subtotalMonto = 0;
            $subtotalFinanciado = 0;
        @endphp
        
        <div class="cobrador-section">
            <div class="cobrador-header">COBRADOR: {{strtoupper($cobrador->full_name)}}</div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%;"># Préstamo</th>
                        <th style="width: 35%;">Cliente</th>
                        <th style="width: 15%;">Cédula</th>
                        <th style="width: 15%;">Teléfono</th>
                        <th style="width: 12%;" class="text-right">Monto</th>
                        <th style="width: 13%;" class="text-right">Financiado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prestamosGrupo as $prest)
                        @php
                            $subtotalMonto += $prest->monto_prestamo;
                            $subtotalFinanciado += $prest->monto_financiado;
                            $totalRegistros++;
                        @endphp
                        <tr>
                            <td>{{$prest->consecutivo}}</td>
                            <td>{{$prest->cliente->full_name}}</td>
                            <td>{{$prest->cliente->cedula}}</td>
                            <td>{{$prest->cliente->telefono1}}</td>
                            <td class="text-right">{{$prest->moneda}} {{number_format($prest->monto_prestamo, 2)}}</td>
                            <td class="text-right">{{$prest->moneda}} {{number_format($prest->monto_financiado, 2)}}</td>
                        </tr>
                    @endforeach
                    
                    <tr class="subtotal-row">
                        <td colspan="4" class="text-right"><strong>SUBTOTAL {{strtoupper($cobrador->full_name)}}:</strong></td>
                        <td class="text-right"><strong>C$ {{number_format($subtotalMonto, 2)}}</strong></td>
                        <td class="text-right"><strong>C$ {{number_format($subtotalFinanciado, 2)}}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        @php
            $granTotalMonto += $subtotalMonto;
            $granTotalFinanciado += $subtotalFinanciado;
        @endphp
    @endforeach

    <div class="gran-total-section">
        <h2>TOTALES GENERALES</h2>
        <div class="totales-grid">
            <div class="total-item">
                <div class="label">Total Monto</div>
                <div class="value">C$ {{number_format($granTotalMonto, 2)}}</div>
            </div>
            <div class="total-item">
                <div class="label">Total Financiado</div>
                <div class="value">C$ {{number_format($granTotalFinanciado, 2)}}</div>
            </div>
            <div class="total-item">
                <div class="label">Total Cobradores</div>
                <div class="value">{{$prestamosPorCobrador->count()}}</div>
            </div>
            <div class="total-item">
                <div class="label">Total Préstamos</div>
                <div class="value">{{$totalRegistros}}</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p><strong>Generado por:</strong> {{Auth::user()->full_name}} | <strong>Fecha:</strong> {{fecha_d_m_Y_h_i(now())}}</p>
    </div>
</body>
</html>
