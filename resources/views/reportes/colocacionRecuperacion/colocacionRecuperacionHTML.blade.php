<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Colocación vs Recuperación - CrediNica</title>
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/fontawesome-free-6.4.0-web/css/all.min.css') }}" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: #1a1a2e;
            background: #f0f2f5;
            padding: 24px;
        }
        .report-wrapper {
            max-width: 1050px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #d0d5dd;
            border-radius: 4px;
            overflow: hidden;
        }
        .report-header {
            text-align: center;
            padding: 24px 40px 16px;
            border-bottom: 3px double #1a1a2e;
        }
        .report-header img {
            height: 40px;
            margin: 0 auto 8px;
            display: block;
        }
        .report-header h1 {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .report-header p {
            font-size: 11px;
            color: #555;
            margin-top: 4px;
        }
        .report-body { padding: 24px 32px; }

        /* ─── TABLA ────────────────────────────────────────── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
            font-size: 12px;
        }
        .data-table th {
            background: #1a1a2e;
            color: #fff;
            padding: 8px 10px;
            font-weight: 600;
            text-align: left;
            font-size: 11px;
            white-space: nowrap;
        }
        .data-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #e8eaf0;
            vertical-align: middle;
        }
        .data-table tbody tr:nth-child(even) { background: #f8f9fb; }
        .data-table tbody tr:hover { background: #eef0f6; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .num { font-family: 'Courier New', monospace; font-size: 11px; }
        .bold { font-weight: 700; }
        .num-green { color: #166534; }
        .num-red   { color: #991b1b; }

        /* ─── FILA TOTAL FINAL ───────────────────────────── */
        .row-grand-total td {
            background: #1a1a2e;
            color: #fff;
            font-weight: 700;
            padding: 9px 10px;
            font-size: 11.5px;
        }

        /* ─── CAJA RESUMEN ───────────────────────────────── */
        .summary-box {
            border: 1px solid #d0d5dd;
            border-radius: 6px;
            overflow: hidden;
            margin-top: 28px;
        }
        .summary-box-header {
            background: #1a1a2e;
            color: #fff;
            padding: 9px 18px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .summary-box-body {
            display: flex;
            gap: 0;
            background: #f8f9fb;
        }
        .summary-item {
            flex: 1;
            text-align: center;
            padding: 18px 12px;
            border-right: 1px solid #e0e0e0;
        }
        .summary-item:last-child { border-right: none; }
        .summary-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
            margin-bottom: 4px;
        }
        .summary-value {
            font-size: 16px;
            font-weight: 800;
            font-family: 'Courier New', monospace;
        }

        .rpt-footer {
            text-align: center;
            font-size: 10px;
            color: #999;
            padding: 14px;
            margin-top: 24px;
            border-top: 1px solid #eee;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .report-wrapper { border: none; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <!-- Botón imprimir (no se imprime) -->
    <div class="no-print" style="max-width:1050px; margin:0 auto 12px; text-align:right;">
        <button onclick="window.print()" class="btn btn-sm btn-dark">
            <i class="fas fa-print me-1"></i> Imprimir
        </button>
    </div>

    <div class="report-wrapper">
        <div class="report-header">
            @if(file_exists(public_path('assets/images/logo.png')))
                <img src="{{ asset('assets/images/logo.png') }}" alt="Logo">
            @endif
            <h1>Reporte Colocación vs Recuperación</h1>
            <p>Desde {{ fecha_d_m_Y($desde) }} hasta {{ fecha_d_m_Y($hasta) }}</p>
        </div>

        <div class="report-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre del Gestor</th>
                        <th class="text-right">Colocación</th>
                        <th class="text-right">Recuperación</th>
                        <th class="text-right">Diferencia</th>
                        <th class="text-center">Rango Fecha</th>
                        <th class="text-center">Desembolsos</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($resultado as $fila)
                        @php $dif = $fila['diferencia']; @endphp
                        <tr>
                            <td class="bold">{{ $fila['agente_nombre'] }}</td>
                            <td class="text-right num">C$ {{ number_format($fila['colocacion'], 2) }}</td>
                            <td class="text-right num">C$ {{ number_format($fila['recuperacion'], 2) }}</td>
                            <td class="text-right num {{ $dif >= 0 ? 'num-green' : 'num-red' }} bold">
                                C$ {{ number_format($dif, 2) }}
                            </td>
                            <td class="text-center">{{ fecha_d_m_Y($desde) }} – {{ fecha_d_m_Y($hasta) }}</td>
                            <td class="text-center bold">{{ $fila['num_desembolsos'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center" style="padding:30px; color:#718096;">
                                No se encontraron datos para el rango seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($resultado) > 0)
                <tfoot>
                    <tr class="row-grand-total">
                        <td><strong>TOTALES GENERALES</strong></td>
                        <td class="text-right num">C$ {{ number_format($totales['colocacion'], 2) }}</td>
                        <td class="text-right num">C$ {{ number_format($totales['recuperacion'], 2) }}</td>
                        <td class="text-right num">C$ {{ number_format($totales['diferencia'], 2) }}</td>
                        <td></td>
                        <td class="text-center">{{ $totales['num_desembolsos'] }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>

            @if(count($resultado) > 0)
            {{-- CAJA RESUMEN --}}
            <div class="summary-box">
                <div class="summary-box-header">
                    <i class="fas fa-chart-bar me-2"></i> Montos Totales
                </div>
                <div class="summary-box-body">
                    <div class="summary-item">
                        <div class="summary-label">Total Colocación</div>
                        <div class="summary-value" style="color:#1a56db;">C$ {{ number_format($totales['colocacion'], 2) }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Total Recuperación</div>
                        <div class="summary-value" style="color:#1e7e34;">C$ {{ number_format($totales['recuperacion'], 2) }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Total Diferencia</div>
                        <div class="summary-value" style="color:{{ $totales['diferencia'] >= 0 ? '#166534' : '#991b1b' }};">
                            C$ {{ number_format($totales['diferencia'], 2) }}
                        </div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Total Desembolsos</div>
                        <div class="summary-value" style="color:#1a1a2e;">{{ $totales['num_desembolsos'] }}</div>
                    </div>
                </div>
            </div>
            @endif

            <div class="rpt-footer">
                Documento generado por CrediNica &mdash; {{ date('d/m/Y h:i A') }} &mdash; Uso interno exclusivo
            </div>
        </div>
    </div>
</body>
</html>
