const getLocalDateStr = (d: Date = new Date()): string => {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
};
import AsyncStorage from '@react-native-async-storage/async-storage';
import { API_ENDPOINTS } from '../config/api';
import { apiFetch } from '../config/apiFetch';
import {
    getPendingPayments,
    markPaymentAsSynced,
    getPendingCredits,
    markCreditAsSynced,
    saveClientsOffline,
    saveCreditsOffline,
    saveAbonosOffline,
    setConfig,
    getConfig
} from './offline-db';
import { sessionService } from './session';

// Verificar si hay conexión real contra el servidor
export const checkConnection = async (): Promise<boolean> => {
    try {
        const controller = new AbortController();
        const timeoutId  = setTimeout(() => controller.abort(), 4000);

        // Usa apiFetch: incluye token y evita que una respuesta 401/405 por sesión
        // se interprete erróneamente como falta de conexión WiFi/servidor.
        const response = await apiFetch(`${API_ENDPOINTS.base}/api/mobile/login`, {
            method: 'GET',
            signal: controller.signal
        }).catch(() => null);

        clearTimeout(timeoutId);
        // Cualquier respuesta HTTP confirma que hay red y el servidor responde.
        // 401/405 también son respuestas reales, no "sin señal".
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

    const todayDateStr = getLocalDateStr();
    const todayAbonosKey = `@credinic_abonos_individuales_${todayDateStr}`;
    const todayCobradosKey = `@credinic_cobrados_hoy_${todayDateStr}`;

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
                // El servidor es la fuente de verdad: primero sincronizar y
                // descargar la cartera, y solo después limpiar las vistas locales.
                // Así el pago deja de verse como OFFLINE y el saldo queda actualizado.
                await markPaymentAsSynced(payment.id);
                synced++;

                // Limpiar o actualizar en AsyncStorage para que no persista como OFFLINE en "Cobrado Hoy"
                try {
                    const localAbonosStr = await AsyncStorage.getItem(todayAbonosKey);
                    if (localAbonosStr) {
                        const localAbonos = JSON.parse(localAbonosStr);
                        // Filtramos el pago temporal offline o actualizamos su abonoId
                        const updated = localAbonos.filter((a: any) =>
                            String(a.id) !== String(payment.id) &&
                            String(a.receiptNumber) !== String(payment.id) &&
                            a.id !== `OFFLINE-${payment.id}`
                        );
                        await AsyncStorage.setItem(todayAbonosKey, JSON.stringify(updated));
                    }
                    const cobradosStr = await AsyncStorage.getItem(todayCobradosKey);
                    if (cobradosStr) {
                        const cobrados = JSON.parse(cobradosStr);
                        const updatedCobrados = cobrados.filter((c: any) =>
                            String(c.id) !== String(payment.creditId) || c.ultimoAbonoId !== null
                        );
                        await AsyncStorage.setItem(todayCobradosKey, JSON.stringify(updatedCobrados));
                    }
                } catch (cleanErr) {
                    console.warn('[SYNC] Error limpiando AsyncStorage para pago sincronizado:', cleanErr);
                }
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
        const allAbonos: any[] = [];

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

                // Plan de pagos completo: sin esto el agente no puede ver el
                // estado de cuenta sin señal, que es el propósito del modo offline.
                const paymentPlan = cuotas.map((c: any, idx: number) => ({
                    numero:    c.numero_cuota ?? idx + 1,
                    fecha:     c.fecha_cuota,
                    monto:     parseFloat(c.monto_cuota) || 0,
                    interes:   parseFloat(c.monto_interes) || 0,
                    mora:      parseFloat(c.monto_mora) || 0,
                    pendiente: parseFloat(c.monto_pendiente_cuota) || 0,
                    estado:    c.estado,
                    pagada:    c.estado === 3,
                }));

                const totalInteres = cuotas.reduce(
                    (s: number, c: any) => s + (parseFloat(c.monto_interes) || 0), 0
                );
                const totalCapital = cuotas.reduce(
                    (s: number, c: any) => s + ((parseFloat(c.monto_cuota) || 0) - (parseFloat(c.monto_interes) || 0)), 0
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
                    paymentPlan,
                    filas:              cuotas,
                    totalCapital,
                    totalInteres,
                    promedioAtraso:     parseFloat(prestamo.promedio_dias_atraso) || 0,
                    estado:             prestamo.estado ?? 1,
                    tipo_abono:         prestamo.tipo_abono ?? 1,
                });

                // Abonos ya realizados: permiten reimprimir recibos antiguos sin conexión.
                for (const abono of (prestamo.abonos || [])) {
                    if (abono.estado !== 1) continue; // Solo abonos vigentes.
                    allAbonos.push({
                        id:            `SRV-${prestamo.id}-${abono.id}`,
                        clientId:      String(cliente.id),
                        creditId:      String(prestamo.id),
                        abonoId:       abono.id,
                        receiptNumber: 'REC-' + String(abono.id).padStart(6, '0'),
                        monto:         parseFloat(abono.total_abonado) || 0,
                        fecha:         abono.fecha_abono || abono.created_at || '',
                        detalle: {
                            creditNumber:   prestamo.consecutivo || '',
                            clientName:     cliente.full_name || `${cliente.nombres} ${cliente.apellidos}`,
                            clientCode:     cliente.cedula || String(cliente.id),
                            paymentDate:    abono.fecha_abono || abono.created_at || '',
                            monto:          parseFloat(abono.total_abonado) || 0,
                            tipo_abono:     abono.tipo_abono,
                            referencia:     abono.referencia_transferencia || '',
                        },
                    });
                }
            }
        }

        await saveClientsOffline(allClients);
        await saveCreditsOffline(allCredits);
        await saveAbonosOffline(allAbonos);
        await setConfig('lastSync', Date.now().toString());

        return {
            success: true,
            message: `Descargados ${allClients.length} clientes, ${allCredits.length} créditos y ${allAbonos.length} abonos`,
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
