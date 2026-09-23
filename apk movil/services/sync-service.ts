const getLocalDateStr = (d: Date = new Date()): string => {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
};
import { API_ENDPOINTS } from '../config/api';
import { apiFetch } from '../config/apiFetch';
import {
    getPendingPayments,
    markPaymentAsSynced,
    getPendingCredits,
    markCreditAsSynced,
    saveClientsOffline,
    saveCreditsOffline,
    setConfig,
    getConfig
} from './offline-db';
import { sessionService } from './session';

// Verificar si hay conexión — usa el servidor real de la app
export const checkConnection = async (): Promise<boolean> => {
    try {
        const controller = new AbortController();
        const timeoutId  = setTimeout(() => controller.abort(), 5000);

        const response = await fetch(API_ENDPOINTS.mobile_login.replace('/login', '/ping'), {
            method: 'HEAD',
            signal: controller.signal
        }).catch(() => null);

        clearTimeout(timeoutId);
        // Cualquier respuesta del servidor (incluso 404/405) confirma que hay conexión
        return response !== null;
    } catch {
        return false;
    }
};

// Sincronizar pagos pendientes guardados offline
export const syncPendingPayments = async (): Promise<{
    success: boolean;
    synced: number;
    failed: number;
    errors: string[];
}> => {
    const pendingPayments = await getPendingPayments();
    let synced = 0;
    let failed = 0;
    const errors: string[] = [];

    for (const payment of pendingPayments) {
        try {
            // Usar los nombres que espera el backend: prestamo_id, monto, fecha_abono
            const response = await apiFetch(API_ENDPOINTS.mobile_payments, {
                method: 'POST',
                body: JSON.stringify({
                    prestamo_id: payment.creditId,
                    monto:       payment.amount,
                    fecha_abono: payment.paymentDate
                        ? String(payment.paymentDate).split('T')[0]
                        : getLocalDateStr(),
                    local_id:    payment.id,
                }),
            });

            const result = await response.json();

            if (result.success) {
                await markPaymentAsSynced(payment.id);
                synced++;
            } else {
                failed++;
                errors.push(`Pago ${payment.id}: ${result.message}`);
            }
        } catch (error: any) {
            failed++;
            errors.push(`Pago ${payment.id}: ${error.message}`);
        }
    }

    return { success: failed === 0, synced, failed, errors };
};

// Sincronizar solicitudes de crédito pendientes
export const syncPendingCredits = async (): Promise<{
    success: boolean;
    synced: number;
    failed: number;
    errors: string[];
}> => {
    const pendingCredits = await getPendingCredits();
    let synced = 0;
    let failed = 0;
    const errors: string[] = [];

    for (const credit of pendingCredits) {
        try {
            const response = await apiFetch(API_ENDPOINTS.mobile_create_credit, {
                method: 'POST',
                body: JSON.stringify(credit.data),
            });

            const result = await response.json();

            if (result.success) {
                await markCreditAsSynced(credit.id);
                synced++;
            } else {
                failed++;
                errors.push(`Crédito ${credit.id}: ${result.message}`);
            }
        } catch (error: any) {
            failed++;
            errors.push(`Crédito ${credit.id}: ${error.message}`);
        }
    }

    return { success: failed === 0, synced, failed, errors };
};

// Descargar cartera para modo offline — usa solo los endpoints que existen en el backend
export const downloadOfflineData = async (): Promise<{
    success: boolean;
    message: string;
}> => {
    try {
        const session = await sessionService.getSession();
        if (!session?.id) {
            return { success: false, message: 'No hay sesión activa' };
        }

        // El backend solo tiene /cartera — de ahí sacamos tanto clientes como créditos
        const portfolioResp   = await apiFetch(`${API_ENDPOINTS.mobile_portfolio}`);
        const portfolioResult = await portfolioResp.json();

        if (!portfolioResult.success) {
            return { success: false, message: 'Error al descargar cartera' };
        }

        // El backend devuelve { clientes: [...] } — cada cliente tiene sus prestamos
        const clientes: any[] = portfolioResult.clientes || [];

        // Armar lista de clientes para la tabla offline_clients
        const allClients = clientes.map((c: any) => ({
            id:           String(c.id),
            clientNumber: c.id_enc || String(c.id),
            name:         c.full_name || `${c.nombres} ${c.apellidos}`,
            cedula:       c.cedula || '',
            phone:        c.telefono1 || '',
            address:      c.direccion || '',
            neighborhood: '',
            municipality: '',
            department:   '',
            isNew:        false,
        }));

        // Armar lista de créditos para la tabla offline_credits
        const hoy = getLocalDateStr();
        const allCredits: any[] = [];

        for (const cliente of clientes) {
            for (const prestamo of (cliente.prestamos || [])) {
                const cuotas: any[] = prestamo.cuotas || [];
                const cuotasHoy     = cuotas.filter((c: any) => c.estado !== 3 && c.fecha_cuota === hoy);
                const cuotasVenc    = cuotas.filter((c: any) => c.estado !== 3 && c.fecha_cuota < hoy);

                const dueTodayAmount = cuotasHoy.reduce(
                    (s: number, c: any) => s + (parseFloat(c.monto_pendiente_cuota) || parseFloat(c.monto_cuota) || 0), 0
                );
                const overdueAmount = cuotasVenc.reduce(
                    (s: number, c: any) => s + (parseFloat(c.monto_pendiente_cuota) || parseFloat(c.monto_cuota) || 0), 0
                );

                allCredits.push({
                    id:                 String(prestamo.id),
                    creditNumber:       prestamo.consecutivo,
                    clientName:         cliente.full_name || `${cliente.nombres} ${cliente.apellidos}`,
                    clientId:           String(cliente.id),
                    amount:             parseFloat(prestamo.monto) || 0,
                    remainingBalance:   parseFloat(prestamo.pendiente_abono) || 0,
                    dueTodayAmount,
                    overdueAmount,
                    lateDays:           cuotasVenc.length,
                    paymentFrequency:   '',
                    collectionsManager: session.fullName,
                    details:            { dueTodayAmount, overdueAmount, remainingBalance: parseFloat(prestamo.pendiente_abono) || 0, lateDays: cuotasVenc.length, paidToday: 0 },
                    paymentPlan:        [],
                });
            }
        }

        await saveClientsOffline(allClients);
        await saveCreditsOffline(allCredits);
        await setConfig('lastSync', Date.now().toString());

        return {
            success: true,
            message: `Descargados ${allClients.length} clientes y ${allCredits.length} créditos`,
        };
    } catch (error: any) {
        return { success: false, message: error.message };
    }
};

// Sincronización completa: sube pendientes → descarga actualizado
export const fullSync = async (): Promise<{
    success: boolean;
    message: string;
    details: {
        payments: { synced: number; failed: number };
        credits:  { synced: number; failed: number };
        download: boolean;
    };
}> => {
    const hasConnection = await checkConnection();

    if (!hasConnection) {
        return {
            success: false,
            message: 'Sin conexión al servidor',
            details: {
                payments: { synced: 0, failed: 0 },
                credits:  { synced: 0, failed: 0 },
                download: false,
            },
        };
    }

    const [paymentsResult, creditsResult] = await Promise.all([
        syncPendingPayments(),
        syncPendingCredits(),
    ]);

    const downloadResult = await downloadOfflineData();

    const allSuccess = paymentsResult.success && creditsResult.success && downloadResult.success;

    return {
        success: allSuccess,
        message: allSuccess
            ? 'Sincronización completada'
            : 'Sincronización con errores: ' + downloadResult.message,
        details: {
            payments: { synced: paymentsResult.synced, failed: paymentsResult.failed },
            credits:  { synced: creditsResult.synced,  failed: creditsResult.failed  },
            download: downloadResult.success,
        },
    };
};

export const getLastSyncDate = async (): Promise<Date | null> => {
    const lastSync = await getConfig('lastSync');
    return lastSync ? new Date(parseInt(lastSync)) : null;
};
