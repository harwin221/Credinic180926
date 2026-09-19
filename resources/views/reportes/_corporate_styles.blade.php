{{-- PARTIAL: Estilos corporativos compartidos por todos los reportes HTML --}}
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #1a1a2e; background: #f0f2f5; padding: 24px; }

    .report-wrapper { max-width: 1100px; margin: 0 auto; background: #fff; border: 1px solid #d0d5dd; }
    .report-wrapper.wide { max-width: 1300px; }

    /* ─── ENCABEZADO ─── */
    .rpt-header { text-align: center; padding: 24px 40px 16px; border-bottom: 3px double #1a1a2e; }
    .rpt-header img { height: 40px; width: auto; display: block; margin: 0 auto 8px; }
    .rpt-header .co-name { font-size: 15px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #1a1a2e; }
    .rpt-header .rpt-title { font-size: 12px; font-weight: 600; color: #4a5568; margin-top: 3px; text-transform: uppercase; letter-spacing: 0.8px; }
    .rpt-header .rpt-meta { font-size: 10px; color: #718096; margin-top: 5px; }

    /* ─── BARRA FILTROS ─── */
    .filters-bar { display: flex; justify-content: center; flex-wrap: wrap; gap: 30px; padding: 9px 40px; background: #f7f8fa; border-bottom: 1px solid #e2e8f0; font-size: 11px; color: #4a5568; }
    .filters-bar span { font-weight: 600; color: #1a1a2e; }

    /* ─── SECCIÓN COBRADOR ─── */
    .section-title { background: #1a1a2e; color: #fff; font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; padding: 7px 16px; margin: 0; }
    .section-title.warning { background: #7d4f00; }
    .section-title.danger  { background: #7f1d1d; }

    /* ─── TABLA ─── */
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table thead th { background: #f0f2f5; color: #4a5568; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; padding: 8px 10px; border-bottom: 1px solid #cbd5e0; white-space: nowrap; }
    .data-table tbody td { padding: 7px 10px; border-bottom: 1px solid #edf2f7; vertical-align: middle; color: #2d3748; font-size: 11px; }
    .data-table tbody tr:nth-child(even) td { background: #fafbfc; }

    /* ─── FILAS ESPECIALES ─── */
    .row-subtotal td { background: #f0f2f5 !important; border-top: 1px solid #a0aec0; border-bottom: 2px solid #a0aec0; font-size: 11px; font-weight: 700; color: #1a1a2e; padding: 7px 10px; }
    .row-grand-total td { background: #1a1a2e !important; color: #fff !important; font-size: 11px; font-weight: 700; padding: 9px 10px; border: none; }

    /* ─── BADGES ─── */
    .badge-corp { display: inline-block; font-size: 9px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; padding: 2px 7px; border-radius: 2px; }
    .badge-vencido  { background:#fff0f0; color:#b91c1c; border:1px solid #fca5a5; }
    .badge-mora     { background:#fffbeb; color:#b45309; border:1px solid #fcd34d; }
    .badge-ok       { background:#f0fdf4; color:#15803d; border:1px solid #86efac; }
    .badge-pendiente{ background:#fefce8; color:#854d0e; border:1px solid #fde047; }
    .badge-pagada   { background:#f0fdf4; color:#15803d; border:1px solid #86efac; }
    .badge-anulada  { background:#f1f5f9; color:#64748b; border:1px solid #cbd5e0; }
    .badge-activo   { background:#f0fdf4; color:#15803d; border:1px solid #86efac; }
    .badge-cancelado{ background:#f1f5f9; color:#64748b; border:1px solid #cbd5e0; }

    /* ─── UTILIDADES ─── */
    .num  { font-family: 'Courier New', monospace; font-size: 11px; }
    .num-red { color: #b91c1c; }
    .num-green { color: #15803d; }
    .bold { font-weight: 700; }
    .text-right  { text-align: right; }
    .text-center { text-align: center; }

    /* ─── PIE ─── */
    .rpt-footer { text-align: center; font-size: 10px; color: #a0aec0; padding: 10px 40px; border-top: 1px solid #e2e8f0; }

    /* ─── BOTONES PANTALLA ─── */
    .screen-actions { display: flex; justify-content: center; gap: 10px; margin-bottom: 16px; }
    .btn-act { display: inline-flex; align-items: center; gap: 6px; padding: 7px 18px; font-size: 12px; font-weight: 600; border: 1px solid #cbd5e0; border-radius: 4px; cursor: pointer; text-decoration: none; background: #fff; color: #2d3748; transition: background .15s; }
    .btn-act:hover { background: #f0f2f5; color: #1a1a2e; }
    .btn-act.primary { background: #1a1a2e; color: #fff; border-color: #1a1a2e; }
    .btn-act.primary:hover { background: #2d3748; }
    .btn-act.success { background: #15803d; color: #fff; border-color: #15803d; }

    /* ═══════════════════════════════════════════════════════════════════
       CUADRO RESUMEN: INFORMACIÓN DEL DÍA
    ═══════════════════════════════════════════════════════════════════ */
    .resumen-dia-wrapper {
        margin: 20px 24px 8px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }
    .resumen-dia-header {
        background: linear-gradient(135deg, #1a1a2e 0%, #2d3748 100%);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 10px 18px;
    }
    .resumen-dia-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        background: #f7f8fa;
    }
    .resumen-dia-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border-right: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        background: #fff;
    }
    .rd-icon {
        width: 38px; height: 38px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%;
        font-size: 15px;
        flex-shrink: 0;
    }
    .card-dia      .rd-icon { background: #dbeafe; color: #1d4ed8; }
    .card-mora     .rd-icon { background: #fef3c7; color: #b45309; }
    .card-proximo  .rd-icon { background: #d1fae5; color: #065f46; }
    .card-vencido  .rd-icon { background: #fee2e2; color: #b91c1c; }
    .card-clientes .rd-icon { background: #ede9fe; color: #6d28d9; }
    .card-total    .rd-icon { background: #1a1a2e; color: #fff; }
    .rd-body { flex: 1; min-width: 0; }
    .rd-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #4a5568; margin-bottom: 1px; }
    .rd-hint  { font-size: 9px; color: #a0aec0; margin-bottom: 4px; }
    .rd-value { font-size: 14px; font-weight: 700; font-family: 'Courier New', monospace; color: #1a1a2e; }
    .rd-value-num { font-family: 'Segoe UI', Arial, sans-serif; }
    .card-dia      .rd-value { color: #1d4ed8; }
    .card-mora     .rd-value { color: #b45309; }
    .card-proximo  .rd-value { color: #065f46; }
    .card-vencido  .rd-value { color: #b91c1c; }
    .card-clientes .rd-value { color: #6d28d9; }
    .card-total    .rd-value { color: #1a1a2e; font-size: 15px; }

    /* ─── IMPRESIÓN ─── */
    @media print {
        body { background: white; padding: 0; font-size: 11px; }
        .report-wrapper { border: none; max-width: 100%; }
        .no-print { display: none !important; }
        .section-title      { background: #1a1a2e !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .row-grand-total td { background: #1a1a2e !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .row-subtotal td    { background: #f0f2f5 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .data-table thead th{ background: #f0f2f5 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .data-table tbody tr:nth-child(even) td { background: #fafbfc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        tr { page-break-inside: avoid; }
        .resumen-dia-wrapper  { page-break-inside: avoid; }
        .resumen-dia-header   { background: #1a1a2e !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .card-dia      .rd-icon { background: #dbeafe !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .card-mora     .rd-icon { background: #fef3c7 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .card-proximo  .rd-icon { background: #d1fae5 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .card-vencido  .rd-icon { background: #fee2e2 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .card-clientes .rd-icon { background: #ede9fe !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .card-total    .rd-icon { background: #1a1a2e !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
