<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Créditos Vencidos - CrediNica</title>
    <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/fontawesome-free-6.4.0-web/css/all.min.css')}}" rel="stylesheet">
    <style>
        /* ─── TIPOGRAFÍA Y BASE ─────────────────────────── */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: #1a1a2e;
            background: #f0f2f5;
            padding: 24px;
        }

        /* ─── CONTENEDOR PRINCIPAL ─────────────────────── */
        .report-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #d0d5dd;
        }

        /* ─── ENCABEZADO CORPORATIVO ────────────────────── */
        .report-header {
            text-align: center;
            padding: 28px 40px 18px;
            border-bottom: 3px double #1a1a2e;
        }
        .report-header img {
            height: 42px;
            width: auto;
            margin-bottom: 10px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .report-header .company-name {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #1a1a2e;
        }
        .report-header .report-title {
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .report-header .report-meta {
            font-size: 11px;
            color: #718096;
            margin-top: 6px;
        }

        /* ─── BARRA DE FILTROS ──────────────────────────── */
        .filters-bar {
            display: flex;
            justify-content: center;
            gap: 40px;
            padding: 10px 40px;
            background: #f7f8fa;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
            color: #4a5568;
        }
        .filters-bar span { font-weight: 600; color: #1a1a2e; }

        /* ─── SECCIÓN POR COBRADOR ──────────────────────── */
        .cobrador-section { padding: 0 0 16px 0; }
        .cobrador-title {
            background: #1a1a2e;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 7px 16px;
            margin: 0;
        }

        /* ─── TABLA DE DATOS ────────────────────────────── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table thead th {
            background: #f0f2f5;
            color: #4a5568;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            border-bottom: 1px solid #cbd5e0;
            border-top: none;
            white-space: nowrap;
        }
        .data-table tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #edf2f7;
            vertical-align: middle;
            color: #2d3748;
        }
        .data-table tbody tr:nth-child(even) td { background: #fafbfc; }

        /* ─── FILA SUBTOTAL ─────────────────────────────── */
        .row-subtotal td {
            background: #f0f2f5 !important;
            border-top: 1px solid #a0aec0;
            border-bottom: 2px solid #a0aec0;
            font-size: 11px;
            font-weight: 700;
            color: #1a1a2e;
            padding: 7px 10px;
        }

        /* ─── FILA TOTAL GENERAL ────────────────────────── */
        .row-grand-total td {
            background: #1a1a2e !important;
            color: #ffffff !important;
            font-size: 11px;
            font-weight: 700;
            padding: 9px 10px;
            border: none;
        }

        /* ─── BADGES DE ESTADO ──────────────────────────── */
        .estado-badge {
            display: inline-block;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 2px 8px;
            border-radius: 2px;
        }
        .badge-vencido  { background: #fff0f0; color: #b91c1c; border: 1px solid #fca5a5; }
        .badge-mora     { background: #fffbeb; color: #b45309; border: 1px solid #fcd34d; }
        .badge-ok       { background: #f0fdf4; color: #15803d; border: 1px solid #86efac; }

        /* ─── TEXTOS NUMÉRICOS ──────────────────────────── */
        .num  { font-family: 'Courier New', Courier, monospace; font-size: 11px; }
        .num-red { color: #b91c1c; }
        .bold { font-weight: 700; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* ─── PIE DEL REPORTE ───────────────────────────── */
        .report-footer {
            text-align: center;
            font-size: 10px;
            color: #a0aec0;
            padding: 12px 40px;
            border-top: 1px solid #e2e8f0;
        }

        /* ─── BOTONES (solo pantalla) ────────────────────── */
        .screen-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 18px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 20px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid #cbd5e0;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            background: #fff;
            color: #2d3748;
            transition: background .15s;
        }
        .btn-action:hover { background: #f0f2f5; color: #1a1a2e; }
        .btn-action.primary { background: #1a1a2e; color: #fff; border-color: #1a1a2e; }
        .btn-action.primary:hover { background: #2d3748; color: #fff; }

        /* ─── MEDIA PRINT ───────────────────────────────── */
        @media print {
            body { background: white; padding: 0; font-size: 11px; }
            .report-wrapper { border: none; max-width: 100%; }
            .no-print { display: none !important; }
            .cobrador-title { background: #1a1a2e !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .row-grand-total td { background: #1a1a2e !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .row-subtotal td { background: #f0f2f5 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .data-table tbody tr:nth-child(even) td { background: #fafbfc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .data-table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
            .cobrador-section { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    {{-- ─── BOTONES PANTALLA ─────────────────────────────── --}}
    <div class="screen-actions no-print">
        <button onclick="window.print()" class="btn-action primary">
            <i class="fas fa-print"></i> Imprimir reporte
        </button>
        <a href="{{ route('reportes.creditosVencidos.index') }}" class="btn-action">
            <i class="fas fa-arrow-left"></i> Volver a filtros
        </a>
    </div>

    <div class="report-wrapper">

        {{-- ─── ENCABEZADO ───────────────────────────────────── --}}
        <div class="report-header">
            <img src="{{asset('assets/img/LogoCrediNica.png')}}" alt="CrediNica">
            <div class="company-name">CrediNica</div>
            <div class="report-title">Reporte de Créditos Vencidos</div>
            <div class="report-meta">
                Fecha de generación: {{ date('d/m/Y') }} &nbsp;|&nbsp; Hora: {{ date('h:i A') }}
            </div>
        </div>

        {{-- ─── BARRA DE FILTROS ─────────────────────────────── --}}
        <div class="filters-bar">
            <div>Desde: <span>{{ $desde ? fecha_d_m_Y($desde) : 'Inicio' }}</span></div>
            <div>Hasta: <span>{{ $hasta ? fecha_d_m_Y($hasta) : 'Hoy' }}</span></div>
            <div>Cobrador: <span>{{ $cobrador ? ($listaCobradores[$cobrador] ?? 'Específico') : 'Todos' }}</span></div>
        </div>

        {{-- ─── CUERPO DEL REPORTE ───────────────────────────── --}}
        @php
            $granTotalPrincipal      = 0;
            $granTotalAtrasado       = 0;
            $granTotalSaldoPendiente = 0;
            $granTotalPrestamos      = 0;
        @endphp

        @forelse($prestamosGrouped as $agenteId => $prestamos)
            @php
                $cobradorNombre       = $prestamos->first()->agente_nombre ?? 'SIN COBRADOR';
                $subtotalPrincipal    = $prestamos->sum('monto_prestamo');
                $subtotalSaldo        = $prestamos->sum('saldo_pendiente');
                $cantidadPrestamos    = $prestamos->count();

                $granTotalPrincipal      += $subtotalPrincipal;
                $granTotalAtrasado       += $prestamos->sum('monto_atrasado');
                $granTotalSaldoPendiente += $subtotalSaldo;
                $granTotalPrestamos      += $cantidadPrestamos;
            @endphp

            <div class="cobrador-section">
                <div class="cobrador-title">
                    Cobrador: {{ strtoupper($cobradorNombre) }}
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>N° Préstamo</th>
                            <th>Cliente</th>
                            <th class="text-right">Monto Principal</th>
                            <th class="text-center">F. Vencimiento</th>
                            <th class="text-right">Cuota</th>
                            <th class="text-right">Monto Atrasado</th>
                            <th class="text-right">Saldo Pendiente</th>
                            <th class="text-center">Prom. Días Atraso</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prestamos as $p)
                        <tr>
                            <td class="bold" style="color:#1a1a2e;">#{{ $p->consecutivo }}</td>
                            <td>{{ $p->cliente_nombre }}</td>
                            <td class="text-right num">C$ {{ number_format($p->monto_prestamo, 2) }}</td>
                            <td class="text-center bold">{{ fecha_d_m_Y($p->fecha_vencimiento) }}</td>
                            <td class="text-right num">C$ {{ number_format($p->monto_cuota, 2) }}</td>
                            <td class="text-right num {{ $p->monto_atrasado > 0 ? 'num-red bold' : '' }}">
                                C$ {{ number_format($p->monto_atrasado, 2) }}
                            </td>
                            <td class="text-right num num-red bold">C$ {{ number_format($p->saldo_pendiente, 2) }}</td>
                            <td class="text-center">
                                @if($p->promedio_dias_atraso > 0)
                                    <span class="num-red bold">{{ $p->promedio_dias_atraso }}</span>
                                @else
                                    <span style="color:#a0aec0;">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach

                        <tr class="row-subtotal">
                            <td colspan="2" class="text-right">
                                Subtotal — {{ $cantidadPrestamos }} préstamo{{ $cantidadPrestamos != 1 ? 's' : '' }}
                            </td>
                            <td class="text-right num">C$ {{ number_format($subtotalPrincipal, 2) }}</td>
                            <td></td>
                            <td></td>
                            <td class="text-right num num-red">C$ {{ number_format($prestamos->sum('monto_atrasado'), 2) }}</td>
                            <td class="text-right num num-red">C$ {{ number_format($subtotalSaldo, 2) }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @empty
            <div style="text-align:center; padding: 48px; color:#718096;">
                No se encontraron créditos que coincidan con los criterios seleccionados.
            </div>
        @endforelse

        {{-- ─── TOTAL GENERAL ────────────────────────────────── --}}
        @if($prestamosGrouped->count() > 0)
        <table class="data-table" style="margin-top: 4px;">
            <tbody>
                <tr class="row-grand-total">
                    <td colspan="2" class="text-right" style="letter-spacing:0.5px;">
                        TOTAL GENERAL &mdash; {{ $granTotalPrestamos }} préstamo{{ $granTotalPrestamos != 1 ? 's' : '' }} vencido{{ $granTotalPrestamos != 1 ? 's' : '' }}
                    </td>
                    <td class="text-right num">C$ {{ number_format($granTotalPrincipal, 2) }}</td>
                    <td colspan="2"></td>
                    <td class="text-right num" style="color:#fca5a5;">C$ {{ number_format($granTotalAtrasado, 2) }}</td>
                    <td class="text-right num" style="color:#fca5a5;">C$ {{ number_format($granTotalSaldoPendiente, 2) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        @endif

        {{-- ─── PIE DE PÁGINA ────────────────────────────────── --}}
        <div class="report-footer">
            Documento generado por el sistema CrediNica &mdash; {{ date('d/m/Y h:i A') }} &mdash; Uso interno exclusivo
        </div>

    </div>

    <script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
</body>
</html>
