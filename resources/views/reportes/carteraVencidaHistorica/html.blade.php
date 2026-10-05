<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cartera Vencida Histórica - CrediNica</title>
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/fontawesome-free-6.4.0-web/css/all.min.css') }}" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #1a1a2e; background: #f0f2f5; padding: 20px; }
        .report-wrapper { max-width: 1400px; margin: 0 auto; background: #fff; border: 1px solid #d0d5dd; }
        .report-header { text-align: center; padding: 22px 40px 14px; border-bottom: 3px double #1a1a2e; }
        .report-header .company-name { font-size: 16px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; }
        .report-header .report-title  { font-size: 13px; font-weight: 600; color: #4a5568; margin-top: 4px; text-transform: uppercase; }
        .report-header .report-meta   { font-size: 11px; color: #718096; margin-top: 5px; }
        .filters-bar { background: #f7fafc; padding: 8px 20px; border-bottom: 1px solid #e2e8f0; font-size: 11px; color: #555; }
        .action-bar  { padding: 10px 20px; border-bottom: 1px solid #e2e8f0; display: flex; gap: 10px; flex-wrap: wrap; }
        .btn-action  { padding: 6px 16px; font-size: 12px; font-weight: 600; border-radius: 20px; border: none; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-back  { background: #e2e8f0; color: #4a5568; }
        .btn-excel { background: #1a7a4a; color: #fff; }
        .btn-print { background: #2b6cb0; color: #fff; }
        /* Seccion por vendedor */
        .vendor-header { background: #1a1a2e; color: #fff; padding: 8px 16px; display: flex; justify-content: space-between; align-items: center; }
        .vendor-header .vendor-name    { font-size: 13px; font-weight: 700; }
        .vendor-header .vendor-resumen { font-size: 11px; opacity: 0.85; }
        /* Tabla */
        .report-table { width: 100%; border-collapse: collapse; }
        .report-table thead tr { background: #2d3748; color: #fff; }
        .report-table thead th { padding: 7px 8px; font-size: 11px; font-weight: 600; text-transform: uppercase; border: 1px solid #4a5568; text-align: center; white-space: nowrap; }
        .report-table tbody td { padding: 6px 8px; font-size: 11px; border: 1px solid #e2e8f0; vertical-align: middle; }
        .report-table tbody tr:nth-child(even) { background: #f7fafc; }
        .report-table tbody tr:hover { background: #ebf8ff; }
        .num    { text-align: right; font-family: 'Courier New', monospace; font-weight: 600; }
        .center { text-align: center; }
        .dias-a { color: #2b6cb0; font-weight: 700; }
        .dias-b { color: #276749; font-weight: 700; }
        .dias-c { color: #744210; font-weight: 700; }
        .dias-d { color: #c05621; font-weight: 700; }
        .dias-e { color: #c53030; font-weight: 700; }
        .subtotal-row td { background: #ebf8ff; font-weight: 700; font-size: 11px; border-top: 2px solid #2b6cb0; color: #1a365d; }
        .total-general { background: #1a1a2e; color: #fff; padding: 12px 20px; display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; }
        .total-item { text-align: center; }
        .total-item .label { font-size: 10px; opacity: 0.75; text-transform: uppercase; }
        .total-item .value { font-size: 14px; font-weight: 700; margin-top: 2px; }
        .badge-reasignado { font-size: 9px; background: #fed7d7; color: #c53030; padding: 1px 5px; border-radius: 8px; font-weight: 600; }
        .report-footer { text-align: center; padding: 10px; font-size: 10px; color: #a0aec0; border-top: 1px solid #e2e8f0; }
        @media print { body { background: #fff; padding: 0; } .action-bar { display: none !important; } }
    </style>
</head>
<body>
<div class="report-wrapper">

    <div class="report-header">
        <div class="company-name">CrediNica</div>
        <div class="report-title">Reporte de Cartera Vencida Histórica</div>
        <div class="report-meta">
            Generado: {{ now()->format('d/m/Y H:i') }}
            &nbsp;|&nbsp; Período:
            <strong>{{ $desde ? \Carbon\Carbon::parse($desde)->format('d/m/Y') : '—' }}</strong>
            al
            <strong>{{ $hasta ? \Carbon\Carbon::parse($hasta)->format('d/m/Y') : now()->format('d/m/Y') }}</strong>
            &nbsp;|&nbsp; Total créditos: <strong>{{ $totales['cantidad'] }}</strong>
        </div>
    </div>

    <div class="action-bar">
        <a href="{{ route('reportes.carteraVencidaHistorica.index') }}" class="btn-action btn-back">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <a href="{{ request()->fullUrlWithQuery(['excel' => 1]) }}" class="btn-action btn-excel">
            <i class="fas fa-file-excel"></i> Exportar Excel
        </a>
        <button onclick="window.print()" class="btn-action btn-print">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>

    <div class="filters-bar">
        <strong>Filtros:</strong>
        Período: <strong>{{ $desde ?: '—' }} al {{ $hasta ?: 'hoy' }}</strong>
        &nbsp;|&nbsp;
        <i class="fas fa-info-circle" style="color:#2b6cb0;"></i>
        Vendedor = quien originó el crédito &nbsp;|&nbsp;
        Saldo Inicio = saldo al inicio del período &nbsp;|&nbsp;
        Cobro Período = pagos recibidos en el rango &nbsp;|&nbsp;
        Saldo Final = Saldo Inicio − Cobro Período
    </div>

    @forelse($agrupado as $vendedorNombre => $creditos)
        @php
            $subMonto  = $creditos->sum('monto_colocado');
            $subInicio = $creditos->sum('saldo_inicio');
            $subCobro  = $creditos->sum('cobro_periodo');
            $subFinal  = $creditos->sum('saldo_final');
        @endphp

        <div style="border-bottom: 2px solid #1a1a2e;">
            <div class="vendor-header">
                <span class="vendor-name">
                    <i class="fas fa-user-tie"></i> {{ $vendedorNombre ?: 'Sin vendedor' }}
                </span>
                <span class="vendor-resumen">
                    {{ $creditos->count() }} crédito(s) &nbsp;|&nbsp;
                    Saldo inicio: C$ {{ number_format($subInicio,2) }} &nbsp;|&nbsp;
                    Cobrado: C$ {{ number_format($subCobro,2) }} &nbsp;|&nbsp;
                    Saldo final: C$ {{ number_format($subFinal,2) }}
                </span>
            </div>

            <table class="report-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Crédito</th>
                        <th>Cliente</th>
                        <th>Cobrador Actual</th>
                        <th>Monto Colocado</th>
                        <th>F. Desembolso</th>
                        <th>F. Vencimiento</th>
                        <th>Días Vencido</th>
                        <th>Clasif.</th>
                        <th>Saldo Inicio</th>
                        <th>Cobro Período</th>
                        <th>Saldo Final</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($creditos as $i => $p)
                        @php
                            $dc = $p->clasificacion ?? 'E';
                            $cls = 'dias-' . strtolower($dc);
                            $reasignado = trim($p->vendedor_nombre) !== trim($p->cobrador_nombre);
                        @endphp
                        <tr>
                            <td class="center">{{ $i + 1 }}</td>
                            <td class="center">{{ $p->consecutivo }}</td>
                            <td>{{ $p->cliente_nombre }}</td>
                            <td>
                                {{ $p->cobrador_nombre }}
                                @if($reasignado)
                                    <span class="badge-reasignado">reasignado</span>
                                @endif
                            </td>
                            <td class="num">{{ $p->moneda }} {{ number_format($p->monto_colocado,2) }}</td>
                            <td class="center">{{ $p->fecha_desembolso ? \Carbon\Carbon::parse($p->fecha_desembolso)->format('d/m/Y') : 'N/A' }}</td>
                            <td class="center">{{ $p->fecha_vencimiento ? \Carbon\Carbon::parse($p->fecha_vencimiento)->format('d/m/Y') : 'N/A' }}</td>
                            <td class="center {{ $cls }}">{{ $p->dias_vencido }}</td>
                            <td class="center {{ $cls }}">{{ $dc }}</td>
                            <td class="num">{{ $p->moneda }} {{ number_format($p->saldo_inicio,2) }}</td>
                            <td class="num" style="color:#276749;">{{ $p->moneda }} {{ number_format($p->cobro_periodo,2) }}</td>
                            <td class="num" style="color:#c53030;">{{ $p->moneda }} {{ number_format($p->saldo_final,2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="subtotal-row">
                        <td colspan="4" style="text-align:right;">
                            Subtotal {{ $vendedorNombre }} ({{ $creditos->count() }} créditos):
                        </td>
                        <td class="num">C$ {{ number_format($subMonto,2) }}</td>
                        <td colspan="4"></td>
                        <td class="num">C$ {{ number_format($subInicio,2) }}</td>
                        <td class="num" style="color:#276749;">C$ {{ number_format($subCobro,2) }}</td>
                        <td class="num" style="color:#c53030;">C$ {{ number_format($subFinal,2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @empty
        <div style="text-align:center; padding: 40px; color: #718096;">
            <i class="fas fa-folder-open" style="font-size:32px; margin-bottom:10px; display:block;"></i>
            No se encontraron créditos vencidos con los filtros aplicados.
        </div>
    @endforelse

    @if($agrupado->isNotEmpty())
    <div class="total-general">
        <div class="total-item">
            <div class="label">Total Créditos</div>
            <div class="value">{{ $totales['cantidad'] }}</div>
        </div>
        <div class="total-item">
            <div class="label">Monto Colocado</div>
            <div class="value">C$ {{ number_format($totales['monto_colocado'],2) }}</div>
        </div>
        <div class="total-item">
            <div class="label">Saldo Inicio</div>
            <div class="value">C$ {{ number_format($totales['saldo_inicio'],2) }}</div>
        </div>
        <div class="total-item">
            <div class="label">Cobro Período</div>
            <div class="value" style="color:#68d391;">C$ {{ number_format($totales['cobro_periodo'],2) }}</div>
        </div>
        <div class="total-item">
            <div class="label">Saldo Final</div>
            <div class="value" style="color:#fc8181;">C$ {{ number_format($totales['saldo_final'],2) }}</div>
        </div>
    </div>
    @endif

    <div class="report-footer">
        CrediNica &mdash; Reporte generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }} &mdash; Confidencial
    </div>
</div>
</body>
</html>
