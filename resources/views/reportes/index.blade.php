@extends('layouts.app')
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
        
        .modal-header {
            background: #1f9cb5;
            color: white;
        }
        
        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }
    </style>

    <div class="row g-3">
        @can('Reporte Clientes (1)')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <div class="report-card" data-bs-toggle="modal" data-bs-target="#modalDesembolsos" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                    <div class="report-card-body">
                        <div class="report-icon">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <h5 class="report-title">Desembolsos</h5>
                    </div>
                </div>
            </div>
        @endcan

        @can('Reporte Recuperación (3)')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <div class="report-card" data-bs-toggle="modal" data-bs-target="#modalRecuperacion" style="background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);">
                    <div class="report-card-body">
                        <div class="report-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h5 class="report-title">Recuperación</h5>
                    </div>
                </div>
            </div>
        @endcan

        @can('Reporte Cuotas Vencidas (4)')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <div class="report-card" data-bs-toggle="modal" data-bs-target="#modalCuotasVencidas" style="background: linear-gradient(135deg, #d4fc79 0%, #96e6a1 100%);">
                    <div class="report-card-body">
                        <div class="report-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h5 class="report-title">Cuotas Vencidas</h5>
                    </div>
                </div>
            </div>
        @endcan

        @can('Reporte Desembolsos Vencidos (5)')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <div class="report-card" data-bs-toggle="modal" data-bs-target="#modalCreditosVencidos" style="background: linear-gradient(135deg, #fdcbf1 0%, #e6dee9 100%);">
                    <div class="report-card-body">
                        <div class="report-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <h5 class="report-title">Créditos Vencidos (Total)</h5>
                    </div>
                </div>
            </div>
        @endcan

        @can('Reporte Asignación Clientes (7)')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <div class="report-card" data-bs-toggle="modal" data-bs-target="#modalAsignacionClientes" style="background: linear-gradient(135deg, #fbc7d4 0%, #9796f0 100%);">
                    <div class="report-card-body">
                        <div class="report-icon">
                            <i class="fas fa-user-tag"></i>
                        </div>
                        <h5 class="report-title">Asignación Clientes</h5>
                    </div>
                </div>
            </div>
        @endcan

        @can('Cobranza (10)')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <div class="report-card" data-bs-toggle="modal" data-bs-target="#modalCobranza" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);">
                    <div class="report-card-body">
                        <div class="report-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                        <h5 class="report-title">Cobros sin abono</h5>
                    </div>
                </div>
            </div>
        @endcan

        @can('Saldo Cartera (11)')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <div class="report-card" data-bs-toggle="modal" data-bs-target="#modalSaldoCartera" style="background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);">
                    <div class="report-card-body">
                        <div class="report-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <h5 class="report-title">Saldo Cartera</h5>
                    </div>
                </div>
            </div>
        @endcan

        @can('Estado Clientes (12)')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <div class="report-card" data-bs-toggle="modal" data-bs-target="#modalEstadoClientes" style="background: linear-gradient(135deg, #d4fc79 0%, #96e6a1 100%);">
                    <div class="report-card-body">
                        <div class="report-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="report-title">Clientes Inactivos</h5>
                    </div>
                </div>
            </div>
        @endcan

        @can('Estado Cuenta Cliente (13)')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <div class="report-card" data-bs-toggle="modal" data-bs-target="#modalEstadoCuenta" style="background: linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%);">
                    <div class="report-card-body">
                        <div class="report-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h5 class="report-title">Estado de Cuenta Cliente</h5>
                    </div>
                </div>
            </div>
        @endcan

        @can('Plan de Pago (14)')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <div class="report-card" data-bs-toggle="modal" data-bs-target="#modalPlanPago" style="background: linear-gradient(135deg, #fdcbf1 0%, #e6dee9 100%);">
                    <div class="report-card-body">
                        <div class="report-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h5 class="report-title">Plan de Pago</h5>
                    </div>
                </div>
            </div>
        @endcan

        @can('Antiguedad de Saldos (15)')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <div class="report-card" data-bs-toggle="modal" data-bs-target="#modalAntiguedadSaldos" style="background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);">
                    <div class="report-card-body">
                        <div class="report-icon">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <h5 class="report-title">Clasificación CONAMI</h5>
                    </div>
                </div>
            </div>
        @endcan

        @can('Cartera Diaria (16)')
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
                <div class="report-card" data-bs-toggle="modal" data-bs-target="#modalCarteraDiaria" style="background: linear-gradient(135deg, #fbc7d4 0%, #9796f0 100%);">
                    <div class="report-card-body">
                        <div class="report-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h5 class="report-title">Cartera Diaria</h5>
                    </div>
                </div>
            </div>
        @endcan

        <!-- Reporte Colocación vs Recuperación -->
        <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3">
            <div class="report-card" data-bs-toggle="modal" data-bs-target="#modalColocacionRecuperacion" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="report-card-body">
                    <div class="report-icon" style="color:#fff;">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <h5 class="report-title" style="color:#fff;">Colocación vs Recuperación</h5>
                </div>
            </div>
        </div>

    </div>

    <!-- MODALES CON FILTROS -->
    @include('reportes.modals.modalDesembolsos')
    @include('reportes.modals.modalAbonos')
    @include('reportes.modals.modalRecuperacion')
    @include('reportes.modals.modalCuotasVencidas')
    @include('reportes.modals.modalPrestamosVencidos')
    @include('reportes.modals.modalAsignacionClientes')
    @include('reportes.modals.modalCobrosDia')
    @include('reportes.modals.modalCobranza')
    @include('reportes.modals.modalSaldoCartera')
    @include('reportes.modals.modalEstadoClientes')
    @include('reportes.modals.modalEstadoCuenta')
    @include('reportes.modals.modalPlanPago')
    @include('reportes.modals.modalAntiguedadSaldos')
    @include('reportes.modals.modalCarteraDiaria')
    @include('reportes.modals.modalCreditosVencidos')
    @include('reportes.modals.modalColocacionVsRecuperacion')

@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Inicializar Select2 simple cuando se abra cualquier modal
        $('.modal').on('shown.bs.modal', function () {
            $(this).find('.select2').not('.select2-multiple').select2({
                theme: 'bootstrap-5',
                dropdownParent: $(this)
            });
            // Inicializar Select2 múltiple para cobrador
            $(this).find('.select2-multiple').select2({
                theme: 'bootstrap-5',
                dropdownParent: $(this),
                placeholder: 'Todos',
                allowClear: true,
                closeOnSelect: false
            });
        });
        // Destruir Select2 al cerrar para evitar duplicados
        $('.modal').on('hidden.bs.modal', function () {
            $(this).find('.select2-multiple').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
            });
        });
    });
</script>
@endsection
