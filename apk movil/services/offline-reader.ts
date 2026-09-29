/**
 * Lecturas para modo offline.
 *
 * Estas funciones replican la forma de respuesta de los endpoints del backend
 * (`/api/mobile/mis-clientes` y `/api/mobile/cliente-detalle`) para que las
 * pantallas puedan caer a la base local sin cambiar su lógica de renderizado.
 *
 * Regla: los clientes EXTERNOS (los que no pertenecen a la cartera del agente)
 * NO se guardan offline. Buscarlos requiere conexión, por decisión de diseño.
 */
import { getOfflineClients, getOfflineCredits, getOfflineAbonos } from './offline-db';
import { sessionService } from './session';

const getLocalDateStr = (d: Date = new Date()): string => {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
};

const n = (v: any): number => parseFloat(v) || 0;

/** Reconstruye la lista "Mi Cartera" desde SQLite, con la misma forma que la API. */
export const getMisClientesOffline = async (buscar = ''): Promise<{
    success: boolean;
    data: { all: any[]; reloan: any[]; renewal: any[] };
} | null> => {
    try {
        const [clientes, creditos] = await Promise.all([
            getOfflineClients(),
            getOfflineCredits(),
        ]);

        if (!clientes.length) return null;

        const term = buscar.trim().toLowerCase();

        const all: any[] = [];
        const reloan: any[] = [];

        for (const c of clientes) {
            const suCredito = creditos.filter((cr: any) => String(cr.clientId) === String(c.id));

            if (term) {
                const coincide =
                    String(c.name || '').toLowerCase().includes(term) ||
                    String(c.cedula || '').toLowerCase().includes(term);
                if (!coincide) continue;
            }

            const activo = suCredito.find((cr: any) => cr.estado === 1) || suCredito[0] || null;
            const totalSaldo = suCredito
                .filter((cr: any) => cr.estado === 1)
                .reduce((s: number, cr: any) => s + n(cr.remainingBalance), 0);

            const clientData = {
                id:            c.id,
                id_enc:        c.clientNumber,
                name:          c.name,
                clientNumber:  c.clientNumber,
                cedula:        c.cedula,
                phone:         c.phone,
                address:       c.address,
                municipality:  c.municipality
                    ? `${c.municipality}${c.department ? ', ' + c.department : ''}`
                    : (c.department || ''),
                totalSaldo,
                activeCredits: suCredito.filter((cr: any) => cr.estado === 1).length,
                creditNumber:  activo ? (activo.creditNumber || activo.id) : '',
                isReprestamo:  true,
            };

            all.push(clientData);

            // Mismo criterio de représtamo que usa el backend: 75% pagado
            // sobre el monto_financiado (capital + intereses) y atraso promedio
            // bajo 2.5 días. Se usa totalAmount (= monto_financiado) guardado
            // en SQLite; si no existe se cae al amount para no dejar el campo vacío.
            if (activo) {
                const base = n(activo.totalAmount) > 0 ? n(activo.totalAmount) : n(activo.amount);
                const pagado = Math.max(0, base - n(activo.remainingBalance));
                const pctPagado = base > 0 ? (pagado / base) * 100 : 0;
                if (pctPagado >= 75 && n(activo.promedioAtraso) < 2.5) {
                    reloan.push(clientData);
                }
            }
        }

        all.sort((a, b) => String(a.name).localeCompare(String(b.name)));

        return { success: true, data: { all, reloan, renewal: [] } };
    } catch (error) {
        console.error('[OFFLINE-READER] Error leyendo cartera offline:', error);
        return null;
    }
};

/** Reconstruye el detalle de un cliente desde SQLite, con la forma que espera la vista. */
export const getClienteDetalleOffline = async (clientId: string | number): Promise<any | null> => {
    try {
        const id = String(clientId);
        const [clientes, creditos, abonos] = await Promise.all([
            getOfflineClients(),
            getOfflineCredits(id),
            getOfflineAbonos(id),
        ]);

        const c = clientes.find((x: any) => String(x.id) === id);
        if (!c) return null;

        // getOfflineAbonos ya filtró por clientId en SQL; NO volver a filtrar
        // por creditId contra el id del cliente (id ≠ creditId y vaciaba el historial).
        const misAbonos = abonos;

        const credits = creditos.map((cr: any) => {
            const plan = cr.paymentPlan || [];

            // La vista espera saldo anterior y saldo nuevo por fila, así que
            // reconstruimos el cronogramarunning a partir del total financiado.
            const totalCuotas = plan.reduce((s: number, f: any) => s + n(f.monto), 0);
            let acumulado = 0;

            const paymentPlan = plan.map((f: any) => {
                const saldoAnterior = Math.max(0, totalCuotas - acumulado);
                const saldoNuevo = Math.max(0, saldoAnterior - n(f.monto));
                acumulado += n(f.monto);

                let status = 'PENDIENTE';
                if (f.pagada) status = 'PAGADA';
                else if (n(f.pendiente) > 0 && n(f.pendiente) < n(f.monto)) status = 'PARCIAL';

                return {
                    paymentNumber: f.numero,
                    paymentDate:   f.fecha,
                    amount:        n(f.monto),
                    balance:       f.pagada ? 0 : n(f.pendiente),
                    saldoAnterior,
                    saldoNuevo,
                    status,
                };
            });

            const paymentHistory = misAbonos
                .filter((a: any) => !a.creditId || String(a.creditId) === String(cr.id))
                .map((a: any) => ({
                    id:             a.abonoId,
                    abonoId:        a.abonoId,
                    amount:         n(a.monto),
                    receiptNumber:  a.receiptNumber,
                    paymentDate:    a.fecha,
                    receivedBy:     cr.collectionsManager || 'Agente',
                    status:         'VALIDO',
                    tipo_abono:     a.detalle?.tipo_abono,
                }));

            return {
                id:                cr.id,
                id_enc:            cr.id,
                creditNumber:      cr.creditNumber,
                    consecutivo:     cr.creditNumber,
                clientId:          cr.clientId,
                clientName:        cr.clientName,
                amount:            n(cr.amount),
                monto:             n(cr.amount),
                balance:           n(cr.remainingBalance),
                remainingBalance:  n(cr.remainingBalance),
                pendingAmount:     n(cr.remainingBalance),
                dueTodayAmount:    n(cr.dueTodayAmount),
                overdueAmount:     n(cr.overdueAmount),
                lateDays:          n(cr.lateDays),
                status:            cr.estado === 1 ? 'ACTIVO' : (cr.estado === 2 ? 'CANCELADO' : 'ANULADO'),
                estado:            cr.estado,
                averageDaysLate:   n(cr.promedioAtraso),
                promedio_dias_atraso: n(cr.promedioAtraso),
                totalCapital:      n(cr.totalCapital),
                totalInteres:      n(cr.totalInteres),
                paymentFrequency:  cr.paymentFrequency || '',
                // Campos financieros del préstamo (ahora guardados en SQLite)
                interestRate:      n(cr.interestRate),
                termMonths:        n(cr.termMonths),
                installmentAmount: n(cr.installmentAmount),
                totalAmount:       n(cr.totalAmount),
                // Aliases que usan clients.tsx y ClientDetailModal
                montoPrestado:     n(cr.amount),
                montoFinanciado:   n(cr.totalAmount),
                financedAmount:    n(cr.totalAmount),
                term:              n(cr.termMonths),
                plazoPago:         n(cr.termMonths),
                formaPago:         cr.paymentFrequency || '',
                // Fechas
                disbursementDate:  cr.disbursementDate || null,
                firstPaymentDate:  cr.firstPaymentDate || null,
                dueDate:           cr.dueDate || null,
                deliveryDate:      cr.disbursementDate || null,
                fechaApertura:     cr.disbursementDate || null,
                fecha_desembolso:  cr.disbursementDate || null,
                fechaFinal:        cr.dueDate || null,
                fecha_ultimo_pago: cr.dueDate || null,
                // totalPaid calculado desde el plan de pagos
                totalPaid: (() => {
                    const totalFinanciado = n(cr.totalAmount) || plan.reduce((s: number, f: any) => s + n(f.monto), 0);
                    return Math.max(0, totalFinanciado - n(cr.remainingBalance));
                })(),
                // avgLateDaysCurrentCredit y avgLateDaysGlobal que usa clients.tsx
                avgLateDaysCurrentCredit: n(cr.promedioAtraso),
                avgLateDaysGlobal:        n(cr.promedioAtraso),
                // Se reenvían tal cual: la pantalla de detalle los usa para
                // pintar tasas, términos y filas del crédito.
                details:           cr.details || {},
                filas:             cr.filas || [],
                paymentPlan,
                paymentHistory,
                // fullStatement esperado por ClientDetailModal
                fullStatement: {
                    installments: (cr.filas || []).map((f: any, idx: number) => ({
                        numero_cuota:          f.numero_cuota ?? f.numero ?? idx + 1,
                        fecha_cuota:           f.fecha_cuota ?? f.fecha ?? '',
                        monto_cuota:           n(f.monto_cuota ?? f.monto),
                        monto_interes:         n(f.monto_interes ?? f.interes),
                        monto_mora:            n(f.monto_mora ?? f.mora),
                        monto_pendiente_cuota: n(f.monto_pendiente_cuota ?? f.pendiente),
                        estado:                f.estado ?? 1,
                        status:                f.estado === 3 ? 'PAGADA' : (n(f.monto_pendiente_cuota ?? f.pendiente) > 0 && n(f.monto_pendiente_cuota ?? f.pendiente) < n(f.monto_cuota ?? f.monto) ? 'PARCIAL' : 'PENDIENTE'),
                    })),
                    payments: paymentHistory,
                    totals: {
                        plan: {
                            cuota:  plan.reduce((s: number, f: any) => s + n(f.monto), 0),
                            capital: n(cr.totalCapital),
                            interes: n(cr.totalInteres),
                            mora:    0,
                            pagado:  Math.max(0, (n(cr.totalAmount) || plan.reduce((s: number, f: any) => s + n(f.monto), 0)) - n(cr.remainingBalance)),
                            saldo:   n(cr.remainingBalance),
                        },
                        abonos: {
                            total:   Math.max(0, (n(cr.totalAmount) || plan.reduce((s: number, f: any) => s + n(f.monto), 0)) - n(cr.remainingBalance)),
                            capital: n(cr.totalCapital),
                            interes: n(cr.totalInteres),
                            mora:    0,
                        },
                    },
                },
            };
        });

        const activas = credits.filter((cr: any) => cr.estado === 1);
        const principal = activas[0] || credits[0] || null;

        return {
            client: {
                id:           c.id,
                id_enc:       c.clientNumber,
                name:         c.name,
                full_name:    c.name,
                cedula:       c.cedula,
                phone:        c.phone,
                address:      c.address,
                neighborhood: '',
                municipality: c.municipality || c.department || '',
            },
            credits,
            // Métricas agregadas del cliente.
            totalBalance:     credits.reduce((s: number, cr: any) => s + n(cr.remainingBalance), 0),
            totalPending:     principal ? n(principal.remainingBalance) : 0,
            dueToday:         principal ? n(principal.dueTodayAmount) : 0,
            overdue:          principal ? n(principal.overdueAmount) : 0,
            lateDays:         principal ? n(principal.lateDays) : 0,
            averageDaysLate:  credits.length
                ? credits.reduce((s: number, cr: any) => s + n(cr.averageDaysLate), 0) / credits.length
                : 0,
            activeCredits:    activas.length,
            totalCredits:     credits.length,
        };
    } catch (error) {
        console.error('[OFFLINE-READER] Error leyendo detalle offline:', error);
        return null;
    }
};

/** Datos listos para reimprimir un recibo pasado, sin conexión. */
export const getReciboOffline = async (abonoId: number | string, prestamoId?: number | string): Promise<any | null> => {
    try {
        const target = prestamoId ? String(prestamoId) : null;
        let abono: any = null;

        if (abonoId) {
            const [, allAbonos] = await Promise.all([getOfflineClients(), getPromiseAllAbonos()]);
            abono = allAbonos.find((a: any) => String(a.abonoId) === String(abonoId));
        }

        if (!abono && target) {
            const allAbonos = await getPromiseAllAbonos();
            abono = allAbonos.find((a: any) => String(a.creditId) === target);
        }

        if (!abono) return null;

        const creditos = await getOfflineCredits(abono.clientId);
        const credito = creditos.find((cr: any) => String(cr.id) === String(abono.creditId));
        if (!credito) return null;

        const c = (await getOfflineClients()).find((x: any) => String(x.id) === String(abono.clientId));
        const session = await sessionService.getSession();
        const creditoDelCliente = creditos.find((cr: any) => String(cr.id) === String(abono.creditId));

        return {
            success: true,
            data: {
                transactionNumber: abono.receiptNumber,
                creditNumber:      String(abono.detalle?.creditNumber || creditoDelCliente?.creditNumber || ''),
                clientName:        abono.detalle?.clientName || c?.name || '',
                clientCode:        abono.detalle?.clientCode || c?.cedula || '',
                paymentDate:       abono.detalle?.paymentDate || abono.fecha,
                cuotaDelDia:       0,
                montoAtrasado:     0,
                diasMora:          0,
                totalAPagar:       0,
                montoCancelacion:  0,
                amountPaid:        n(abono.monto),
                saldoAnterior:     0,
                nuevoSaldo:        0,
                managedBy:         session?.fullName || creditoDelCliente?.collectionsManager || 'Agente',
                sucursal:          session?.sucursalName || 'PRINCIPAL',
                role:              'AGENTE DE COBRO',
                abono_id:          abono.abonoId,
                prestamo_id:       credito.id,
                tipo_abono:        abono.detalle?.tipo_abono,
                is_cancelacion:    abono.detalle?.tipo_abono === 3,
                concepto:          abono.detalle?.tipo_abono === 3
                    ? 'CANCELACIÓN DE CRÉDITO'
                    : 'ABONO DE CRÉDITO',
            },
        };
    } catch (error) {
        console.error('[OFFLINE-READER] Error generando recibo offline:', error);
        return null;
    }
};

/** Lee todos los abonos guardados (usado por getReciboOffline). */
const getPromiseAllAbonos = async (): Promise<any[]> => {
    const clients = await getOfflineClients();
    const all: any[] = [];
    for (const c of clients) {
        all.push(...(await getOfflineAbonos(c.id)));
    }
    return all;
};
