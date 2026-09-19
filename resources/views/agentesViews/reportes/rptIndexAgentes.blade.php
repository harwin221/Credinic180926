@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina')
    Reportes
@endsection

@section('content')
    <style>
        .report-card {
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            height: 100%;
            text-decoration: none;
            display: block;
            cursor: pointer;
        }
        
        .report-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.12);
            text-decoration: none;
        }
        
        .report-card-body {
            padding: 15px 12px;
            text-align: center;
            position: relative;
        }
        
        .report-icon {
            font-size: 28px;
            margin-bottom: 8px;
            color: #2c3e50;
        }
        
        .report-title {
            font-size: 12px;
            font-weight: 700;
            margin: 0;
            color: #2c3e50;
            line-height: 1.2;
        }
        
        .report-card-body::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.4);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }
    </style>

    <div class="row g-3">
        <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
            <div class="report-card" data-bs-toggle="modal" data-bs-target="#modalEstadoCuentaAgente" style="background: linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%);">
                <div class="report-card-body">
                    <div class="report-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h5 class="report-title">Estado de Cuenta Cliente</h5>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
            <div class="report-card" data-bs-toggle="modal" data-bs-target="#modalPlanPagoAgente" style="background: linear-gradient(135deg, #fdcbf1 0%, #e6dee9 100%);">
                <div class="report-card-body">
                    <div class="report-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h5 class="report-title">Plan de Pago</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Modal Estado Cuenta Cliente -->
<div class="modal fade" id="modalEstadoCuentaAgente" tabindex="-1" aria-labelledby="modalEstadoCuentaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #1f9cb5; color: white;">
                <h5 class="modal-title" id="modalEstadoCuentaLabel"><i class="fas fa-file-alt"></i> Estado de Cuenta Cliente</h5>
                <button type="button" class="btn-close" style="filter: brightness(0) invert(1);" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{html()->form('GET', route('agentes.reportes.estadoCuentaCliente'))->id('formEstadoCuentaAgente')->open()}}
            <input type="hidden" name="exportar" value="1">
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="ecc_cliente"><strong>Cliente:</strong></label>
                        {{html()->select('cliente', [''=>'-- Seleccione --'] + (isset($listaClientes) ? $listaClientes : []), null)->class('form-control select2')->id('ecc_cliente')->required()->style('width: 100%')}}
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="ecc_prestamos"><strong>Préstamo:</strong></label>
                        <select name="prestamo" class="form-control select2" id="ecc_prestamos" required style="width: 100%">
                            <option value="">-- Seleccione primero un cliente --</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnGenerarEstadoCuenta"><i class="fas fa-file-pdf"></i> Generar Reporte</button>
            </div>
            {{html()->form()->close()}}
        </div>
    </div>
</div>

<!-- Modal Plan de Pago -->
<div class="modal fade" id="modalPlanPagoAgente" tabindex="-1" aria-labelledby="modalPlanPagoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #1f9cb5; color: white;">
                <h5 class="modal-title" id="modalPlanPagoLabel"><i class="fas fa-clipboard-list"></i> Plan de Pago</h5>
                <button type="button" class="btn-close" style="filter: brightness(0) invert(1);" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{html()->form('GET', route('agentes.reportes.planPago'))->id('formPlanPagoAgente')->open()}}
            <input type="hidden" name="exportar" value="1">
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="pp_cliente"><strong>Cliente:</strong></label>
                        {{html()->select('cliente', [''=>'-- Seleccione --'] + (isset($listaClientes) ? $listaClientes : []), null)->class('form-control select2')->id('pp_cliente')->required()->style('width: 100%')}}
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label for="pp_prestamos"><strong>Préstamo:</strong></label>
                        <select name="prestamos" class="form-control select2" id="pp_prestamos" required style="width: 100%">
                            <option value="">-- Seleccione primero un cliente --</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnGenerarPlanPago"><i class="fas fa-file-pdf"></i> Generar Reporte</button>
            </div>
            {{html()->form()->close()}}
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    // ── Helpers ──────────────────────────────────────────────────────────────────
    function initSelect2InModal(modalSelector) {
        const $modal = $(modalSelector);
        $modal.find('.select2').each(function () {
            // Destruir instancia previa si existe para evitar duplicados
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
            $(this).select2({
                dropdownParent: $modal,
                width: '100%',
                language: {
                    noResults: function () { return 'Sin resultados'; },
                    searching:  function () { return 'Buscando...'; }
                }
            });
        });
    }

    function cargarPrestamos(clienteId, selectSelector, modalSelector) {
        const $select = $(selectSelector);
        const $modal  = $(modalSelector);

        // Destruir y resetear mientras carga
        if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
        $select.empty().append('<option value="">Cargando préstamos...</option>');
        $select.select2({ dropdownParent: $modal, width: '100%' });

        fetch(`/agente/reportes/plan-pago/prestamos/${clienteId}`)
            .then(r => r.json())
            .then(data => {
                if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
                $select.empty();

                const keys = Object.keys(data);
                if (keys.length === 0) {
                    $select.append('<option value="">-- Sin préstamos activos --</option>');
                } else {
                    $select.append('<option value="">-- Seleccione --</option>');
                    keys.forEach(id => $select.append(`<option value="${id}">${data[id]}</option>`));
                }
                $select.select2({ dropdownParent: $modal, width: '100%', placeholder: '-- Seleccione --' });
            })
            .catch(() => {
                if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
                $select.empty().append('<option value="">-- Error al cargar --</option>');
                $select.select2({ dropdownParent: $modal, width: '100%' });
            });
    }

    function resetPrestamos(selectSelector, modalSelector) {
        const $select = $(selectSelector);
        if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
        $select.empty().append('<option value="">-- Seleccione primero un cliente --</option>');
        $select.select2({ dropdownParent: $(modalSelector), width: '100%' });
    }

    // ── Inicialización al abrir modales ──────────────────────────────────────────
    $('#modalEstadoCuentaAgente').on('shown.bs.modal', function () {
        initSelect2InModal('#modalEstadoCuentaAgente');
    });

    $('#modalPlanPagoAgente').on('shown.bs.modal', function () {
        initSelect2InModal('#modalPlanPagoAgente');
    });

    // Limpiar selects al cerrar para que queden frescos la próxima vez
    $('#modalEstadoCuentaAgente').on('hidden.bs.modal', function () {
        resetPrestamos('#ecc_prestamos', '#modalEstadoCuentaAgente');
        $('#ecc_cliente').val('').trigger('change.select2');
    });

    $('#modalPlanPagoAgente').on('hidden.bs.modal', function () {
        resetPrestamos('#pp_prestamos', '#modalPlanPagoAgente');
        $('#pp_cliente').val('').trigger('change.select2');
    });

    // ── Cambio de cliente → cargar préstamos (delegado al documento) ─────────────
    $(document).on('change', '#ecc_cliente', function () {
        const clienteId = $(this).val();
        if (clienteId) {
            cargarPrestamos(clienteId, '#ecc_prestamos', '#modalEstadoCuentaAgente');
        } else {
            resetPrestamos('#ecc_prestamos', '#modalEstadoCuentaAgente');
        }
    });

    $(document).on('change', '#pp_cliente', function () {
        const clienteId = $(this).val();
        if (clienteId) {
            cargarPrestamos(clienteId, '#pp_prestamos', '#modalPlanPagoAgente');
        } else {
            resetPrestamos('#pp_prestamos', '#modalPlanPagoAgente');
        }
    });

    // ── Submit Estado de Cuenta ──────────────────────────────────────────────────
    $('#formEstadoCuentaAgente').on('submit', function (e) {
        e.preventDefault();
        const cliente  = $('#ecc_cliente').val();
        const prestamo = $('#ecc_prestamos').val();
        if (!cliente || !prestamo) {
            alert('Seleccione un cliente y un préstamo.');
            return false;
        }
        window.open(`{{ route('agentes.reportes.estadoCuentaCliente') }}?cliente=${cliente}&prestamo=${prestamo}&exportar=1`, '_blank');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEstadoCuentaAgente')).hide();
    });

    // ── Submit Plan de Pago ──────────────────────────────────────────────────────
    $('#formPlanPagoAgente').on('submit', function (e) {
        e.preventDefault();
        const cliente  = $('#pp_cliente').val();
        const prestamo = $('#pp_prestamos').val();
        if (!cliente || !prestamo) {
            alert('Seleccione un cliente y un préstamo.');
            return false;
        }
        window.open(`{{ route('agentes.reportes.planPago') }}?cliente=${cliente}&prestamos=${prestamo}&exportar=1`, '_blank');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPlanPagoAgente')).hide();
    });
</script>
<style>
    /* Asegurar que el dropdown de Select2 quede sobre el modal */
    .select2-container--open { z-index: 99999 !important; }
    .select2-dropdown        { z-index: 99999 !important; }
</style>
@endsection
