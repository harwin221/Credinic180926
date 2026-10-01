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
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: #1a1a2e;
            background: #f0f2f5;
            padding: 20px;
        }
        .report-wrapper {
            max-width: 1300px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #d0d5dd;
        }
        /* ── Encabezado ── */
        .report-header {
            text-align: center;
            padding: 22px 40px 14px;
            border-bottom: 3px double #1a1a2e;
        }
        .report-header .company-name {
            font-size: 16px; font-weight: 700;
            letter-spacing: 1.5px; text-transform: uppercase;
        }
        .report-header .report-title {
            font-size: 13px; font-weight: 600; color: #4a5568;
            margin-top: 4px; text-transform: uppercase; letter-spacing: 0.8px;
        }
        .report-header .report-meta {
            font-size: 11px; color: #718096; margin-top: 5px;
        }
        /* ── Filtros aplicados ── */
        .filters-bar {
            background: #f7fafc; padding: 10px 20px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px; color: #555;
        }
        /* ── Botones de acción ── */
        .action-bar {
            padding: 12px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex; gap: 10px; flex-wrap: wrap;
        }
        .btn-action {
            padding: 6px 16px; font-size: 12px; font-weight: 600;
            border-radius: 20px; border: none; cursor: pointer;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-back    { background: #e2e8f0; color: #4a5568; }
        .btn-excel   { background: #1a7a4a; color: #fff; }
        .btn-print   { background: #2b6cb0; color: #fff; }
        /* ── Sección por gestor ── */
        .gestor-section { margin: 0; border-bottom: 2px solid #1a1a2e; }
        .gestor-header {
            background: #1a1a2e; color: #fff;
            padding: 8px 16px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .gestor-header .gestor-name { font-size: 13px; font-weight: 700; letter-spacing: 0.5px; }
        .gestor-header .gestor-resumen { font-size: 11px; opacity: 0.85; }
        /* ── Tabla ── */
        .report-table { width: 100%; border-collapse: collapse; }
        .report-table thead tr {
            background: #2d3748; color: #fff;
        }
        .report-table thead th {
            padding: 7px 8px; font-size: 11px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.4px;
            border: 1px solid #4a5568; text-align: center; white-space: nowrap;
        }
        .report-table tbody td {
            padding: 6px 8px; font-size: 11px;
            border: 1px solid #e2e8f0; vertical-align: middle;
        }
        .report-table tbody tr:nth-child(even) { background: #f7fafc; }
        .report-table tbody tr:hover { background: #ebf8ff; }
        /* Columnas numéricas */
        .num { text-align: right; font-family: 'Courier New', monospace; font-weight: 600; }
        .center { text-align: center; }
        /* Colores de días vencido */
        .dias-a { color: #2b6cb0; font-weight: 700; }
        .dias-b { color: #276749; font-weight: 700; }
        .dias-c { color: #744210; font-weight: 700; }
        .dias-d { color: #c05621; font-weight: 700; }
        .dias-e { color: #c53030; font-weight: 700; }
        /* Subtotal por gestor */
        .subtotal-row td {
            background: #ebf8ff; font-weight: 700; font-size: 11px;
            border-top: 2px solid #2b6cb0; color: #1a365d;
        }
        /* Total general */
        .total-general {
            background: #1a1a2e; color: #fff;
            padding: 12px 20px;
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;
        }
        .total-item { text-align: center; }
        .total-item .label { font-size: 10px; opacity: 0.75; text-transform: uppercase; }
        .total-item .value { font-size: 15px; font-weight: 700; margin-top: 2px; }
        /* Badge gestor actual diferente */
        .badge-reasignado {
            font-size: 9px; background: #fed7d7; color: #c53030;
            padding: 1px 5px; border-radius: 8px; font-weight: 600;
            white-space: nowrap;
        }
        /* Footer */
        .report-footer {
            text-align: center; padding: 10px;
            font-size: 10px; color: #a0aec0;
            border-top: 1px solid #e2e8f0;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .action-bar { display: none !important; }
            .report-wrapper { border: none; max-width: 100%; }
        }
    </style>
</head>
<body>
<div class="report-wrapper">

    {{-- ── Encabezado ── --}}
    <div class="report-header">
        <div class="company-name">CrediNica</div>
        <div class="report-title">Reporte de Cartera Vencida Histórica</div>
        <div class="report-meta">
            Generado: {{ now()->format('d/m/Y H:i') }}
            @if($desde || $hasta)
                &nbsp;|&nbsp; Vencimiento:
                {{ $desde ? \Carbon\Carbon::parse($desde)->format('d/m/Y') : '—' }}
                al
                {{ $hasta ? \Carbon\Carbon::parse($hasta)->format('d/m/Y') : \Carbon\Carbon::now()->format('d/m/Y') }}
            @else
                &nbsp;|&nbsp; Todos los períodos
            @endif
            &nbsp;|&nbsp; Total créditos: <strong>{{ $totales['cantidad'] }}</strong>
        </div>
    </div>

    {{-- ── Botones ── --}}
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

    {{-- ── Filtros aplicados ── --}}
    <div class="filters-bar">
        <strong>Filtros aplicados:</strong>
        Gestor original: <strong>{{ $cobrador ? ($listaCobradores[encode(reset((array)$cobrador))] ?? 'Todos') : 'Todos' }}</strong>
        &nbsp;|&nbsp; Vencimiento desde: <strong>{{ $desde ?: '—' }}</strong>
        &nbsp;|&nbsp; Hasta: <strong>{{ $hasta ?: 'Hoy' }}</strong>
        &nbsp;|&nbsp;
        <span style="color:#c53030;">
            <i class="fas fa-info-circle"></i>
            Gestor original = quien tenía el crédito cuando venció (o fue reasignado por primera vez).
        </span>
    </div>

    {{-- ── Cuerpo del reporte ── --}}
    @forelse($agrupado as $gestorNombre => $creditos)
        @php
            $subtotalVencer  = $creditos->sum('saldo_al_vencer');
            $subtotalActual  = $creditos->sum('saldo_actual');
            $subtotalDif     = $creditos->sum('diferencia_cobrada');
        @endphp

        <div class="gestor-section">
            {{-- Cabecera del gestor --}}
            <div class="gestor-header">
                <span class="gestor-name">
                    <i class="fas fa-user-tie me-1"></i> {{ $gestorNombre }}
                </span>
                <span class="gestor-resumen">
                    {{ $creditos->count() }} crédito(s) &nbsp;|&nbsp;
                    Saldo al vencer: C$ {{ number_format($subtotalVencer, 2) }} &nbsp;|&nbsp;
                    Saldo actual: C$ {{ number_format($subtotalActual, 2) }} &nbsp;|&nbsp;
                    Cobrado: C$ {{ number_format($subtotalDif, 2) }}
                </span>
            </div>

            {{-- Tabla de créditos --}}
            <table class="report-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Crédito</th>
                        <th>Cliente</th>
                        <th>Gestor Actual</th>
                        <th>F. Desembolso</th>
                        <th>F. Vencimiento</th>
                        <th>Días Vencido</th>
                        <th>Clasif.</th>
                        <th>Saldo al Vencer</th>
                        <th>Saldo Actual</th>
                        <th>Diferencia Cobrada</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($creditos as $i => $p)
                        @php
                            $dias = (int)$p->dias_vencido;
                            if ($dias <= 15)        { $clasif = 'A'; $diasClass = 'dias-a'; }
                            elseif ($dias <= 30)    { $clasif = 'B'; $diasClass = 'dias-b'; }
                            elseif ($dias <= 60)    { $clasif = 'C'; $diasClass = 'dias-c'; }
                            elseif ($dias <= 90)    { $clasif = 'D'; $diasClass = 'dias-d'; }
                            else                    { $clasif = 'E'; $diasClass = 'dias-e'; }

                            $esReasignado = $p->gestor_original !== $p->gestor_actual;
                        @endphp
                        <tr>
                            <td class="center">{{ $i + 1 }}</td>
                            <td class="center">{{ $p->consecutivo }}</td>
                            <td>{{ $p->cliente_nombre }}</td>
                            <td>
                                {{ $p->gestor_actual }}
                                @if($esReasignado)
                                    <span class="badge-reasignado">reasignado</span>
                                @endif
                            </td>
                            <td class="center">
                                {{ $p->fecha_desembolso ? \Carbon\Carbon::parse($p->fecha_desembolso)->format('d/m/Y') : 'N/A' }}
                            </td>
                            <td class="center">
                                {{ $p->fecha_vencimiento ? \Carbon\Carbon::parse($p->fecha_vencimiento)->format('d/m/Y') : 'N/A' }}
                            </td>
                            <td class="center {{ $diasClass }}">{{ $dias }}</td>
                            <td class="center {{ $diasClass }}">{{ $clasif }}</td>
                            <td class="num">{{ $p->moneda }} {{ number_format($p->saldo_al_vencer, 2) }}</td>
                            <td class="num" style="color:#c53030;">{{ $p->moneda }} {{ number_format($p->saldo_actual, 2) }}</td>
                            <td class="num" style="color:#276749;">{{ $p->moneda }} {{ number_format($p->diferencia_cobrada, 2) }}</td>
                        </tr>
                    @endforeach

                    {{-- Subtotal del gestor --}}
                    <tr class="subtotal-row">
                        <td colspan="8" style="text-align:right;">
                            Subtotal {{ $gestorNombre }} ({{ $creditos->count() }} créditos):
                        </td>
                        <td class="num">C$ {{ number_format($subtotalVencer, 2) }}</td>
                        <td class="num" style="color:#c53030;">C$ {{ number_format($subtotalActual, 2) }}</td>
                        <td class="num" style="color:#276749;">C$ {{ number_format($subtotalDif, 2) }}</td>
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

    {{-- ── Total General ── --}}
    @if($agrupado->isNotEmpty())
    <div class="total-general">
        <div class="total-item">
            <div class="label">Total Créditos</div>
            <div class="value">{{ $totales['cantidad'] }}</div>
        </div>
        <div class="total-item">
            <div class="label">Saldo al Vencer</div>
            <div class="value">C$ {{ number_format($totales['saldo_al_vencer'], 2) }}</div>
        </div>
        <div class="total-item">
            <div class="label">Saldo Actual</div>
            <div class="value" style="color:#fc8181;">C$ {{ number_format($totales['saldo_actual'], 2) }}</div>
        </div>
        <div class="total-item">
            <div class="label">Total Cobrado</div>
            <div class="value" style="color:#68d391;">C$ {{ number_format($totales['diferencia_cobrada'], 2) }}</div>
        </div>
    </div>
    @endif

    <div class="report-footer">
        CrediNica &mdash; Reporte generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }} &mdash; Confidencial
    </div>
</div>

<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
