@extends('layouts.agentesLayouts.appAgente')
@section('tituloPagina') Recaudo del Día @endsection

@section('content')
<style>
    /* Contenedor responsivo principal */
    .recaudo-container {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 15px;
        min-height: 80vh;
    }

    /* Tarjeta principal estilo premium */
    .recaudo-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0, 86, 179, 0.08), 0 2px 5px rgba(0, 0, 0, 0.03);
        padding: 30px 24px;
        width: 100%;
        max-width: 480px;
        border: 1px solid rgba(13, 110, 253, 0.1);
        text-align: center;
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }

    /* Nombre del agente */
    .recaudo-agent-name {
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        margin-bottom: 6px;
        letter-spacing: 0.5px;
        line-height: 1.3;
    }

    /* Fecha del recaudo */
    .recaudo-date {
        font-size: 13px;
        font-weight: 600;
        color: #0d6efd;
        margin-bottom: 20px;
    }

    /* Línea divisora */
    .recaudo-divider {
        height: 1px;
        background: #e2e8f0;
        border: none;
        margin: 20px 0;
    }

    /* Título de sección */
    .recaudo-sec-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 1.5px;
        margin-bottom: 6px;
    }

    /* Monto principal de recuperación */
    .recaudo-main-amount {
        font-size: 36px;
        font-weight: 800;
        color: #000000;
        line-height: 1.1;
        margin-bottom: 20px;
    }

    /* Encabezado sección de información */
    .recaudo-info-header {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        text-align: left;
        margin-bottom: 12px;
        margin-top: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .recaudo-info-header i {
        color: #0d6efd;
    }

    /* Fila del listado de información */
    .recaudo-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
        text-align: left;
    }
    .recaudo-row:last-child {
        border-bottom: none;
    }

    /* Etiquetas de filas */
    .recaudo-row-label {
        font-size: 13.5px;
        color: #475569;
        font-weight: 500;
    }

    /* Valores de filas */
    .recaudo-row-value {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
    }
    
    .recaudo-row-value.highlight {
        color: #0d6efd;
    }

    .recaudo-row-value.val-dia {
        color: #198754;
    }

    .recaudo-row-value.val-mora {
        color: #fd7e14;
    }

    .recaudo-row-value.val-proximo {
        color: #0d6efd;
    }

    .recaudo-row-value.val-vencido {
        color: #dc3545;
    }

    .recaudo-row-value.zero {
        color: #94a3b8;
        font-weight: 500;
    }

    /* Botón de retroceso */
    .recaudo-back-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 24px;
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 0;
        width: 100%;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .recaudo-back-btn:hover {
        background: #f1f5f9;
        color: #1e293b;
        border-color: #cbd5e1;
    }

    /* Ajustes específicos para móviles */
    @media (max-width: 576px) {
        .recaudo-card {
            padding: 24px 16px;
            border-radius: 12px;
            box-shadow: none;
            border: none;
        }
        .recaudo-container {
            padding: 0;
        }
        .recaudo-main-amount {
            font-size: 32px;
        }
    }
</style>

<div class="recaudo-container">
    <div class="recaudo-card">
        <!-- Nombre del agente logueado -->
        <div class="recaudo-agent-name">{{ strtoupper($agente->full_name) }}</div>
        
        <!-- Fecha formateada en español con primera letra mayúscula -->
        <div class="recaudo-date">
            {{ ucfirst(\Carbon\Carbon::parse($hoy)->translatedFormat('l, d \d\e F \d\e\l Y')) }}
        </div>

        <hr class="recaudo-divider">

        <!-- Monto total recuperado del día -->
        <div class="recaudo-sec-title">Recuperación del Día</div>
        <div class="recaudo-main-amount">
            C$ {{ number_format($resumen['total_recuperado'], 2) }}
        </div>

        <hr class="recaudo-divider">

        <!-- Información del día -->
        <div class="recaudo-info-header">
            <i class="fas fa-info-circle"></i>
            <span>Información del día</span>
        </div>

        <!-- Día Recaudado -->
        <div class="recaudo-row">
            <span class="recaudo-row-label">Dia Recaudado:</span>
            <span class="recaudo-row-value {{ $resumen['dia_recaudado'] > 0 ? 'val-dia' : 'zero' }}">
                C$ {{ number_format($resumen['dia_recaudado'], 2) }}
            </span>
        </div>

        <!-- Mora Recaudada -->
        <div class="recaudo-row">
            <span class="recaudo-row-label">Mora Recaudada:</span>
            <span class="recaudo-row-value {{ $resumen['mora_recaudada'] > 0 ? 'val-mora' : 'zero' }}">
                C$ {{ number_format($resumen['mora_recaudada'], 2) }}
            </span>
        </div>

        <!-- Próximo Recaudado -->
        <div class="recaudo-row">
            <span class="recaudo-row-label">Proximo Recaudado:</span>
            <span class="recaudo-row-value {{ $resumen['proximo_recaudado'] > 0 ? 'val-proximo' : 'zero' }}">
                C$ {{ number_format($resumen['proximo_recaudado'], 2) }}
            </span>
        </div>

        <!-- Vencido Recaudado -->
        <div class="recaudo-row">
            <span class="recaudo-row-label">Vencido Recaudado:</span>
            <span class="recaudo-row-value {{ $resumen['vencido_recaudado'] > 0 ? 'val-vencido' : 'zero' }}">
                C$ {{ number_format($resumen['vencido_recaudado'], 2) }}
            </span>
        </div>

        <!-- Total Clientes Cobrados -->
        <div class="recaudo-row">
            <span class="recaudo-row-label">Total Clientes Cobrados:</span>
            <span class="recaudo-row-value {{ $resumen['total_clientes'] > 0 ? 'highlight' : 'zero' }}">
                {{ $resumen['total_clientes'] }}
            </span>
        </div>

        <!-- Botón para volver al inicio -->
        <a href="{{ route('agentes.homeAgentes') }}" class="recaudo-back-btn">
            <i class="fas fa-arrow-left"></i> Volver al Inicio
        </a>
    </div>
</div>
@endsection
