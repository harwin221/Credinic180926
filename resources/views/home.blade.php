@extends('layouts.app')
@section('tituloPagina')
    Inicio
@endsection

@section('content')

<style>
    .stat-card {
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        height: 100%;
        text-decoration: none;
        display: block;
        position: relative;
        z-index: 1; /* Asegurar que esté debajo del header */
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    
    .stat-card-body {
        padding: 20px;
        text-align: center;
        position: relative;
        color: white;
        z-index: 1; /* Asegurar que esté debajo del header */
    }
    
    .stat-icon {
        font-size: 40px;
        margin-bottom: 10px;
        color: white;
        position: relative;
        z-index: 1;
    }
    
    .stat-title {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 10px;
        color: white;
        position: relative;
        z-index: 1;
    }
    
    .stat-number {
        font-size: 32px;
        font-weight: 700;
        margin: 0;
        color: white;
        position: relative;
        z-index: 1;
    }
    
    .stat-card-body::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 80px;
        height: 80px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        transform: translate(30%, -30%);
        z-index: 0; /* Detrás del contenido de la tarjeta */
        pointer-events: none; /* NO debe capturar eventos */
    }
    
    /* En móvil, asegurar que las tarjetas NO interfieran con el header */
    @media (max-width: 991px) {
        .stat-card {
            z-index: 1 !important;
            position: relative !important;
        }
        
        .stat-card-body {
            z-index: 1 !important;
        }
        
        .stat-card-body::before {
            z-index: 0 !important;
            pointer-events: none !important;
        }
    }
</style>

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                <div class="stat-card-body">
                    <div class="stat-icon">
                        <i class="fa fa-users" style="color: #2c3e50;"></i>
                    </div>
                    <h6 class="stat-title" style="color: #2c3e50;">Clientes Activos</h6>
                    <h2 class="stat-number" style="color: #2c3e50;">{{$datos['clientesActivos']}}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);">
                <div class="stat-card-body">
                    <div class="stat-icon">
                        <i class="fa fa-users" style="color: #2c3e50;"></i>
                    </div>
                    <h6 class="stat-title" style="color: #2c3e50;">Clientes | Año</h6>
                    <h2 class="stat-number" style="color: #2c3e50;">{{$datos['clientesAnyo']}}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);">
                <div class="stat-card-body">
                    <div class="stat-icon">
                        <i class="fa fa-users" style="color: #2c3e50;"></i>
                    </div>
                    <h6 class="stat-title" style="color: #2c3e50;">Clientes | Mes</h6>
                    <h2 class="stat-number" style="color: #2c3e50;">{{$datos['clientesMes']}}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #d4fc79 0%, #96e6a1 100%);">
                <div class="stat-card-body">
                    <div class="stat-icon">
                        <i class="fa fa-hand-holding-dollar" style="color: #2c3e50;"></i>
                    </div>
                    <h6 class="stat-title" style="color: #2c3e50;">Préstamos Activos</h6>
                    <h2 class="stat-number" style="color: #2c3e50;">{{$datos['prestamosActivos']}}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold text-primary">
                        <i class="fa fa-money-bill-wave me-2"></i>Recuperación por Agente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Agente</th>
                                    <th class="text-center">Último Pago</th>
                                    <th class="text-end">Monto Recuperado</th>
                                    <th class="text-center">Clientes Atendidos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recuperacionAgentes as $recuperacion)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3 bg-primary-light rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(13, 110, 253, 0.1);">
                                                    <i class="fa fa-user text-primary small"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-semibold text-dark">{{ $recuperacion->user_create->full_name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center text-muted">
                                            {{ \Carbon\Carbon::parse($recuperacion->ultimo_pago)->format('d/m/Y h:i A') }}
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            C$ {{ number_format($recuperacion->total, 2) }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border px-3">{{ $recuperacion->clientes_atendidos }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No hay recuperaciones registradas hoy.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light fw-bold border-top-0">
                                <tr>
                                    <td colspan="2" class="text-end">TOTAL GENERAL:</td>
                                    <td class="text-end text-primary">C$ {{ number_format($totalRecuperado, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary px-3">{{ $totalClientesAtendidos }}</span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
