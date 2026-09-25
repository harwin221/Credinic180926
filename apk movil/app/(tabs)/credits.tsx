// Función para obtener fecha local YYYY-MM-DD respetando la zona horaria del dispositivo/Nicaragua
const getLocalDateString = (d: Date = new Date()): string => {
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};
import AsyncStorage from '@react-native-async-storage/async-storage';

const COBRADOS_HOY_KEY = '@credinic_cobrados_hoy_';
const getTodayKey = () => `${COBRADOS_HOY_KEY}${getLocalDateString()}`;
import {
    View, Text, StyleSheet, SafeAreaView, TouchableOpacity,
    ScrollView, TextInput, RefreshControl, ActivityIndicator,
    Modal, StatusBar, Platform,
} from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useState, useEffect, useCallback } from 'react';
import { useFocusEffect } from 'expo-router';
import { sessionService } from '../../services/session';
import { API_ENDPOINTS } from '../../config/api';
import { apiFetch } from '../../config/apiFetch';
import PaymentModal from '../../components/PaymentModal';
import ReceiptModal, { ReceiptData } from '../../components/ReceiptModal';
import CustomAlert from '../../components/CustomAlert';
import { AlertHelper } from '../../utils/custom-alert-helper';
import { savePendingPayment } from '../../services/offline-db';

// ─── Tipos ────────────────────────────────────────────────────────────────────
interface CreditItem {
    id: number;
    id_enc: string;
    creditNumber: number;
    clientName: string;
    clientAddress: string;
    clientCode: string;
    clientId: number;
    remainingBalance: number;
    collectionsManager: string;
    cuotaNumero: number;
    ultimoAbonoId?: number | null;
    promedio_atraso?: number;
    // Montos calculados
    details: {
        dueTodayAmount: number;
        overdueAmount: number;
        remainingBalance: number;
        lateDays: number;
        diasVencido: number;
        paidToday: number;
    };
}

export interface TodayPaymentItem {
    id: string;
    abonoId: number | null;
    creditId: number;
    creditNumber: number;
    clientId: number;
    clientName: string;
    clientCode: string;
    clientAddress: string;
    amountPaid: number;
    saldoAnterior: number;
    saldoActual: number;
    hora: string;
    fechaAbono: string;
    receiptNumber: string;
    receiptData?: ReceiptData;
    creditItem?: CreditItem;
}

const ABONOS_HOY_KEY = '@credinic_abonos_individuales_';
const getTodayAbonosKey = () => `${ABONOS_HOY_KEY}${getLocalDateString()}`;

// ─── Clasificación — lógica idéntica al HomeControllerAgenteController.php ───
function clasificarPortfolio(clientes: any[], hoy: string, agenteName: string) {
    const dueToday:  CreditItem[] = []; // Cuotas del Día
    const overdue:   CreditItem[] = []; // Clientes en Mora
    const expired:   CreditItem[] = []; // Préstamos Vencidos
    const upToDate:  CreditItem[] = []; // Al Día
    const paidToday: CreditItem[] = []; // Cobrado Hoy

    for (const cliente of clientes) {
        const prestamos: any[] = cliente.prestamos || [];

        for (const prestamo of prestamos) {
            const cuotas: any[] = prestamo.cuotas || [];
            const cuotasPendientes = cuotas.filter((c: any) => c.estado !== 3 && c.estado !== 4);

            // ── Flags equivalentes a las queries del controlador web ─────────
            const tieneCuotaHoy    = cuotasPendientes.some((c: any) => c.fecha_cuota === hoy);
            const tieneCuotasVenc  = cuotasPendientes.some((c: any) => c.fecha_cuota < hoy);
            const tieneCuotasFutur = cuotasPendientes.some((c: any) => c.fecha_cuota > hoy);

            // ── Montos ───────────────────────────────────────────────────────
            const cuotasHoy    = cuotasPendientes.filter((c: any) => c.fecha_cuota === hoy);
            const cuotasVenc   = cuotasPendientes.filter((c: any) => c.fecha_cuota < hoy);

            const dueTodayAmount = cuotasHoy.reduce(
                (s: number, c: any) => s + (parseFloat(c.monto_pendiente_cuota) || parseFloat(c.monto_cuota) || 0), 0
            );
            const overdueAmount = cuotasVenc.reduce(
                (s: number, c: any) => s + (parseFloat(c.monto_pendiente_cuota) || parseFloat(c.monto_cuota) || 0), 0
            );

            // ── Días de mora: desde la primera cuota vencida ─────────────────
            const primeraVencida = cuotasVenc.sort((a: any, b: any) =>
                a.fecha_cuota.localeCompare(b.fecha_cuota)
            )[0];
            const lateDays = primeraVencida
                ? Math.floor((new Date(hoy).getTime() - new Date(primeraVencida.fecha_cuota).getTime()) / 86400000)
                : 0;

            // ── Días vencido: desde la última cuota ──────────────────────────
            const ultimaCuota = [...cuotas].sort((a: any, b: any) =>
                b.fecha_cuota.localeCompare(a.fecha_cuota)
            )[0];
            const diasVencido = ultimaCuota
                ? Math.floor((new Date(hoy).getTime() - new Date(ultimaCuota.fecha_cuota).getTime()) / 86400000)
                : 0;

            // ── Cuota del día para mostrar en la tarjeta ─────────────────────
            const cuotaHoyObj  = cuotasHoy[0];
            const cuotaVencObj = cuotasVenc.sort((a: any, b: any) =>
                a.fecha_cuota.localeCompare(b.fecha_cuota)
            )[0];

            const cobradoHoyMonto = parseFloat(prestamo.cobrado_hoy || 0);
            const item: CreditItem = {
                id:                  prestamo.id,
                id_enc:              prestamo.id_enc,
                creditNumber:        prestamo.consecutivo,
                clientName:          cliente.full_name || `${cliente.nombres} ${cliente.apellidos}`,
                clientAddress:       cliente.direccion || '',
                clientCode:          cliente.cedula || String(cliente.id),
                clientId:            cliente.id,
                remainingBalance:    parseFloat(prestamo.pendiente_abono) || 0,
                collectionsManager:  agenteName,
                cuotaNumero:         cuotaHoyObj?.numero_cuota ?? cuotaVencObj?.numero_cuota ?? 0,
                ultimoAbonoId:       prestamo.ultimo_abono_id || null,
                promedio_atraso:     parseFloat(prestamo.promedio_atraso) || 0,
                details: {
                    dueTodayAmount,
                    overdueAmount,
                    remainingBalance: parseFloat(prestamo.pendiente_abono) || 0,
                    lateDays,
                    diasVencido: Math.max(0, diasVencido),
                    paidToday: cobradoHoyMonto,
                },
            };

            // Si tiene cobro hoy, incluirlo en la pestaña Cobrado Hoy
            if (cobradoHoyMonto > 0 || prestamo.tiene_abono_hoy) {
                paidToday.push(item);
            }

            // ── Clasificar — misma lógica que el controlador web ─────────────
            // PESTAÑA 1 — Cuotas del Día: tiene cuota HOY (independiente de mora)
            if (tieneCuotaHoy) {
                dueToday.push(item);
            }
            // PESTAÑA 2 — Mora: cuotas vencidas + tiene cuotas futuras (no vencido total)
            else if (tieneCuotasVenc && tieneCuotasFutur) {
                overdue.push(item);
            }
            // PESTAÑA 3 — Vencidos: sin cuotas futuras + tiene saldo pendiente
            else if (tieneCuotasVenc && !tieneCuotasFutur && !tieneCuotaHoy &&
                     parseFloat(prestamo.pendiente_abono) > 0) {
                expired.push(item);
            }
            // Al Día
            else {
                upToDate.push(item);
            }
        }
    }

    // Ordenar Mora por días asc (igual que el web: orderBy dias_mora asc)
    overdue.sort((a, b) => a.details.lateDays - b.details.lateDays);
    // Ordenar Vencidos por días asc
    expired.sort((a, b) => a.details.diasVencido - b.details.diasVencido);
    // Cobro Día: ordenar por nombre del cliente (igual que web: orderBy nombres asc)
    dueToday.sort((a, b) => a.clientName.localeCompare(b.clientName));
    paidToday.sort((a, b) => a.clientName.localeCompare(b.clientName));

    return { dueToday, overdue, expired, upToDate, paidToday };
}

// ─── Tabs ─────────────────────────────────────────────────────────────────────
const TABS = ['Cobro Dia', 'Mora', 'Vencido', 'Cobrado Hoy', 'Al Día'] as const;
type TabKey = typeof TABS[number];

const TAB_KEYS: Record<TabKey, keyof ReturnType<typeof clasificarPortfolio> | 'paidToday'> = {
    'Cobro Dia':   'dueToday',
    'Mora':        'overdue',
    'Vencido':     'expired',
    'Cobrado Hoy': 'paidToday',
    'Al Día':      'upToDate',
};

const TAB_COLOR: Record<TabKey, string> = {
    'Cobro Dia':   '#10b981',
    'Mora':        '#f97316',
    'Vencido':     '#e11d48',
    'Cobrado Hoy': '#10b981',
    'Al Día':      '#0ea5e9',
};

// ─── Componente ───────────────────────────────────────────────────────────────
export default function CreditsScreen() {
    const [activeTab, setActiveTab]       = useState<TabKey>('Cobro Dia');
    const [searchQuery, setSearchQuery]   = useState('');
    const [isSearchActive, setIsSearchActive] = useState(false);
    const [isLoading, setIsLoading]       = useState(true);
    const [refreshing, setRefreshing]     = useState(false);

    const [todayPayments, setTodayPayments] = useState<TodayPaymentItem[]>([]);
    const [portfolio, setPortfolio] = useState<{
        dueToday:  CreditItem[];
        overdue:   CreditItem[];
        expired:   CreditItem[];
        paidToday: CreditItem[];
        upToDate:  CreditItem[];
    }>({ dueToday: [], overdue: [], expired: [], paidToday: [], upToDate: [] });

    const [isModalVisible, setIsModalVisible]     = useState(false);
    const [selectedCredit, setSelectedCredit]     = useState<CreditItem | null>(null);
    const [receiptData, setReceiptData]           = useState<ReceiptData | null>(null);
    const [isReceiptVisible, setIsReceiptVisible] = useState(false);
    const [searchResults, setSearchResults]       = useState<CreditItem[]>([]);
    const [externalResults, setExternalResults]   = useState<CreditItem[]>([]);
    const [isSearchingExternal, setIsSearchingExternal] = useState(false);
    const [expandedCreditId, setExpandedCreditId] = useState<number | null>(null);
    const [isReprinting, setIsReprinting]         = useState(false);

    // Referencia interna a todos los préstamos (para búsqueda local)
    const [allItems, setAllItems] = useState<CreditItem[]>([]);

    const [alert, setAlert] = useState<{
        visible: boolean;
        type: 'success' | 'error' | 'warning' | 'info';
        title: string; message: string;
    }>({ visible: false, type: 'info', title: '', message: '' });

    // ─── Carga de cartera ─────────────────────────────────────────────────────
    const fetchPortfolio = useCallback(async (showLoadingSpinner = false) => {
        if (showLoadingSpinner) setIsLoading(true);
        const session = await sessionService.getSession();
        if (!session?.id) return;

        try {
            const resp   = await apiFetch(`${API_ENDPOINTS.mobile_portfolio}`);
            const result = await resp.json();

            if (result.success) {
                const hoy      = result.fecha_hoy || getLocalDateString();
                const clientes = result.clientes || [];

                const { dueToday, overdue, expired, upToDate, paidToday: classifiedPaidToday } =
                    clasificarPortfolio(clientes, hoy, session.fullName);

                // Cargar abonos individuales de hoy devueltos por el backend
                const serverAbonos: TodayPaymentItem[] = [];
                for (const cliente of clientes) {
                    for (const prestamo of (cliente.prestamos || [])) {
                        if (Array.isArray(prestamo.abonos_hoy)) {
                            for (const ab of prestamo.abonos_hoy) {
                                serverAbonos.push({
                                    id: `abono_${ab.abono_id}`,
                                    abonoId: ab.abono_id,
                                    creditId: prestamo.id,
                                    creditNumber: prestamo.consecutivo,
                                    clientId: cliente.id,
                                    clientName: cliente.full_name || `${cliente.nombres} ${cliente.apellidos}`,
                                    clientCode: cliente.cedula || String(cliente.id),
                                    clientAddress: cliente.direccion || '',
                                    amountPaid: parseFloat(ab.monto) || 0,
                                    saldoAnterior: parseFloat(ab.saldo_anterior) || (parseFloat(prestamo.pendiente_abono) + parseFloat(ab.monto)),
                                    saldoActual: parseFloat(ab.saldo_actual) || parseFloat(prestamo.pendiente_abono),
                                    hora: ab.hora || (ab.created_at ? new Date(ab.created_at).toLocaleTimeString('es-NI', { hour: '2-digit', minute: '2-digit', hour12: true }) : 'Hoy'),
                                    fechaAbono: ab.fecha_abono || hoy,
                                    receiptNumber: `REC-${String(ab.abono_id).padStart(6, '0')}`,
                                    creditItem: {
                                        id: prestamo.id,
                                        id_enc: prestamo.id_enc,
                                        creditNumber: prestamo.consecutivo,
                                        clientName: cliente.full_name || `${cliente.nombres} ${cliente.apellidos}`,
                                        clientAddress: cliente.direccion || '',
                                        clientCode: cliente.cedula || String(cliente.id),
                                        clientId: cliente.id,
                                        remainingBalance: parseFloat(prestamo.pendiente_abono) || 0,
                                        collectionsManager: session.fullName,
                                        cuotaNumero: 0,
                                        ultimoAbonoId: ab.abono_id,
                                        promedio_atraso: parseFloat(prestamo.promedio_atraso) || 0,
                                        details: {
                                            dueTodayAmount: 0,
                                            overdueAmount: 0,
                                            remainingBalance: parseFloat(prestamo.pendiente_abono) || 0,
                                            lateDays: 0,
                                            diasVencido: 0,
                                            paidToday: parseFloat(ab.monto) || 0,
                                        },
                                    },
                                });
                            }
                        }
                    }
                }

                // Cargar abonos guardados en AsyncStorage en este dispositivo
                let localAbonos: TodayPaymentItem[] = [];
                try {
                    const localAbonosStr = await AsyncStorage.getItem(getTodayAbonosKey());
                    if (localAbonosStr) {
                        localAbonos = JSON.parse(localAbonosStr);
                    }
                } catch (e) {
                    console.error('[STORAGE] Error leyendo abonos individuales:', e);
                }

                // Fusionar abonos: el servidor es la fuente de verdad.
                // Preservar receiptData del caché local y agregar solo pagos offline (abonoId === null).
                const mergedAbonosList = [...serverAbonos];
                // Preservar receiptData que solo existe localmente
                for (const item of mergedAbonosList) {
                    if (!item.receiptData) {
                        const cached = localAbonos.find(l => l.abonoId === item.abonoId);
                        if (cached?.receiptData) item.receiptData = cached.receiptData;
                    }
                }
                // Agregar únicamente pagos offline que aún no se han sincronizado (sin abonoId)
                for (const local of localAbonos) {
                    if (local.abonoId === null && !mergedAbonosList.some(m => m.id === local.id)) {
                        mergedAbonosList.push(local);
                    }
                }
                // Actualizar AsyncStorage para que coincida con la lista autorizada
                AsyncStorage.setItem(getTodayAbonosKey(), JSON.stringify(mergedAbonosList)).catch(e =>
                    console.error('[STORAGE] Error actualizando abonos individuales:', e)
                );
                setTodayPayments(mergedAbonosList);

                // Cargar también cobrados guardados en almacenamiento local del teléfono hoy
                let localSavedPaid: CreditItem[] = [];
                try {
                    const savedStr = await AsyncStorage.getItem(getTodayKey());
                    if (savedStr) {
                        localSavedPaid = JSON.parse(savedStr);
                    }
                } catch (e) {
                    console.error('[STORAGE] Error leyendo cobrados hoy:', e);
                }

                setPortfolio(() => {
                    const mergedPaid = [...classifiedPaidToday];
                    // Agregar solo pagos offline del caché local (sin abonoId = no sincronizados aún)
                    for (const item of localSavedPaid) {
                        const isOfflineOnly = !serverAbonos.some(sa => sa.creditId === item.id);
                        if (isOfflineOnly && !mergedPaid.some(m => m.id === item.id)) {
                            mergedPaid.push(item);
                        }
                    }
                    // NO mezclar prev.paidToday: el servidor es la fuente de verdad
                    mergedPaid.sort((a, b) => a.clientName.localeCompare(b.clientName));
                    // Sincronizar AsyncStorage con la lista autorizada
                    AsyncStorage.setItem(getTodayKey(), JSON.stringify(mergedPaid)).catch(e =>
                        console.error('[STORAGE] Error actualizando cobrados hoy:', e)
                    );
                    return {
                        dueToday,
                        overdue,
                        expired,
                        upToDate,
                        paidToday: mergedPaid,
                    };
                });

                // Índice plano para búsqueda local
                setAllItems([...dueToday, ...overdue, ...expired, ...upToDate]);
            }
        } catch (error) {
            console.error('[CARTERA] Error:', error);
        } finally {
            setIsLoading(false);
            setRefreshing(false);
        }
    }, []);

    useFocusEffect(
        useCallback(() => {
            fetchPortfolio();
        }, [fetchPortfolio])
    );

    const onRefresh = () => {
        setRefreshing(true);
        fetchPortfolio();
    };

    // ─── Búsqueda en Cartera Local y Clientes Externos (igual que en la web) ──
    useEffect(() => {
        if (!isSearchActive || searchQuery.trim().length < 2) {
            setSearchResults([]);
            setExternalResults([]);
            setIsSearchingExternal(false);
            return;
        }

        const q = searchQuery.trim().toLowerCase();
        // 1. Filtrar en la cartera propia
        const localMatches = allItems.filter(i =>
            i.clientName.toLowerCase().includes(q) ||
            i.clientCode.toLowerCase().includes(q) ||
            String(i.creditNumber).includes(q)
        );
        setSearchResults(localMatches);

        // 2. Buscar clientes externos en el servidor con debounce de 350ms
        setIsSearchingExternal(true);
        const timer = setTimeout(async () => {
            try {
                const endpoint = `${API_ENDPOINTS.base}/api/mobile/clientes-externos?buscar=${encodeURIComponent(searchQuery.trim())}`;
                const resp = await apiFetch(endpoint);
                const result = await resp.json();
                if (result.success && Array.isArray(result.clientes)) {
                    setExternalResults(result.clientes);
                } else {
                    setExternalResults([]);
                }
            } catch (err) {
                console.error('[BUSQUEDA_EXTERNA] Error:', err);
                setExternalResults([]);
            } finally {
                setIsSearchingExternal(false);
            }
        }, 350);

        return () => clearTimeout(timer);
    }, [searchQuery, isSearchActive, allItems]);


    // ─── Reimprimir Pago Específico (de la pestaña Cobrado Hoy) ─────────────
    const handleReprintPayment = async (paymentItem: TodayPaymentItem) => {
        setIsReprinting(true);
        try {
            const session = await sessionService.getSession();
            const endpoint = (API_ENDPOINTS as any).mobile_recibo || `${API_ENDPOINTS.base}/api/mobile/recibo`;

            const response = await apiFetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    abono_id: paymentItem.abonoId || null,
                    prestamo_id: paymentItem.creditId,
                }),
            });

            const result = await response.json();
            if (result.success && result.data) {
                setReceiptData(result.data);
                setIsReceiptVisible(true);
            } else if (paymentItem.receiptData) {
                setReceiptData(paymentItem.receiptData);
                setIsReceiptVisible(true);
            } else {
                const now = new Date().toLocaleString('es-NI');
                setReceiptData({
                    transactionNumber: paymentItem.receiptNumber || (paymentItem.abonoId ? `REC-${String(paymentItem.abonoId).padStart(6, '0')}` : 'REC-REIMPRESION'),
                    creditNumber: paymentItem.creditNumber || paymentItem.creditId,
                    clientName: paymentItem.clientName,
                    clientCode: paymentItem.clientCode,
                    paymentDate: paymentItem.hora ? `${paymentItem.fechaAbono} ${paymentItem.hora}` : now,
                    cuotaDelDia: paymentItem.amountPaid,
                    montoAtrasado: 0,
                    diasMora: 0,
                    totalAPagar: paymentItem.amountPaid,
                    montoCancelacion: paymentItem.saldoActual,
                    amountPaid: paymentItem.amountPaid,
                    saldoAnterior: paymentItem.saldoAnterior,
                    nuevoSaldo: paymentItem.saldoActual,
                    managedBy: session?.fullName || 'AGENTE',
                    sucursal: session?.sucursalName || 'SUCURSAL',
                    role: session?.role || 'AGENTE DE COBRO',
                });
                setIsReceiptVisible(true);
            }
        } catch (error) {
            console.error('[REIMPRIMIR_PAGO] Error:', error);
            if (paymentItem.receiptData) {
                setReceiptData(paymentItem.receiptData);
                setIsReceiptVisible(true);
            } else {
                AlertHelper.alert('Error', 'No se pudo conectar con el servidor para reimprimir el recibo');
            }
        } finally {
            setIsReprinting(false);
        }
    };

    // ─── Reimprimir Recibo (lógica idéntica al botón reimprimir de la web) ──────
    const handleReprint = async (item: CreditItem) => {
        setIsReprinting(true);
        try {
            const session = await sessionService.getSession();
            const endpoint = (API_ENDPOINTS as any).mobile_recibo || `${API_ENDPOINTS.base}/api/mobile/recibo`;
            
            const response = await apiFetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    abono_id: item.ultimoAbonoId || null,
                    prestamo_id: item.id,
                }),
            });

            const result = await response.json();
            if (result.success && result.data) {
                setReceiptData(result.data);
                setIsReceiptVisible(true);
            } else {
                // Fallback con datos calculados locales si no se pudo conectar al endpoint específico
                const detail = item.details;
                const now = new Date().toLocaleString('es-NI');
                setReceiptData({
                    transactionNumber: item.ultimoAbonoId ? `REC-${String(item.ultimoAbonoId).padStart(6, '0')}` : 'REC-REIMPRESION',
                    creditNumber: item.creditNumber || item.id,
                    clientName: item.clientName,
                    clientCode: item.clientCode,
                    paymentDate: now,
                    cuotaDelDia: detail.dueTodayAmount,
                    montoAtrasado: detail.overdueAmount,
                    diasMora: detail.lateDays,
                    totalAPagar: detail.dueTodayAmount + detail.overdueAmount,
                    montoCancelacion: detail.remainingBalance,
                    amountPaid: detail.paidToday || detail.dueTodayAmount,
                    saldoAnterior: detail.remainingBalance + (detail.paidToday || 0),
                    nuevoSaldo: detail.remainingBalance,
                    managedBy: session?.fullName || 'AGENTE',
                    sucursal: session?.sucursalName || 'SUCURSAL',
                    role: session?.role || 'AGENTE DE COBRO',
                });
                setIsReceiptVisible(true);
            }
        } catch (error) {
            console.error('[REIMPRIMIR] Error:', error);
            AlertHelper.alert('Error', 'No se pudo conectar con el servidor para reimprimir');
        } finally {
            setIsReprinting(false);
        }
    };

    // ─── Seleccionar crédito para pago ────────────────────────────────────────
    const handleSelectCredit = (item: CreditItem) => {
        setSelectedCredit(item);
        setIsModalVisible(true);
    };

    // ─── Procesar pago ────────────────────────────────────────────────────────
    const handleProcessPayment = async (paymentData: any) => {
        const session = await sessionService.getSession();
        if (!session) return;

        // ── OFFLINE ───────────────────────────────────────────────────────────
        if (paymentData.isOffline) {
            if (!selectedCredit) return;
            setIsModalVisible(false);
            const detail = selectedCredit.details;
            const now    = new Date().toLocaleString('es-NI');

            const offlineReceipt: ReceiptData = {
                transactionNumber: paymentData.offlineId,
                creditNumber:      selectedCredit.creditNumber || selectedCredit.id,
                clientName:        selectedCredit.clientName,
                clientCode:        selectedCredit.clientCode,
                paymentDate:       now,
                cuotaDelDia:       detail.dueTodayAmount,
                montoAtrasado:     detail.overdueAmount,
                diasMora:          detail.lateDays,
                totalAPagar:       detail.dueTodayAmount + detail.overdueAmount,
                montoCancelacion:  detail.remainingBalance,
                amountPaid:        paymentData.amount,
                saldoAnterior:     detail.remainingBalance,
                nuevoSaldo:        Math.max(0, detail.remainingBalance - paymentData.amount),
                managedBy:         session.fullName,
                sucursal:          session.sucursalName || 'SUCURSAL',
                role:              session.role,
            };
            setReceiptData(offlineReceipt);
            setIsReceiptVisible(true);

            const offlinePaymentItem: TodayPaymentItem = {
                id: paymentData.offlineId || `local_${Date.now()}`,
                abonoId: null,
                creditId: selectedCredit.id,
                creditNumber: selectedCredit.creditNumber || selectedCredit.id,
                clientId: selectedCredit.clientId,
                clientName: selectedCredit.clientName,
                clientCode: selectedCredit.clientCode,
                clientAddress: selectedCredit.clientAddress,
                amountPaid: paymentData.amount,
                saldoAnterior: detail.remainingBalance,
                saldoActual: Math.max(0, detail.remainingBalance - paymentData.amount),
                hora: new Date().toLocaleTimeString('es-NI', { hour: '2-digit', minute: '2-digit', hour12: true }),
                fechaAbono: getLocalDateString(),
                receiptNumber: paymentData.offlineId || 'OFFLINE',
                receiptData: offlineReceipt,
                creditItem: selectedCredit,
            };

            setTodayPayments(prev => {
                const updated = [offlinePaymentItem, ...prev.filter(p => p.id !== offlinePaymentItem.id)];
                AsyncStorage.setItem(getTodayAbonosKey(), JSON.stringify(updated)).catch(e =>
                    console.error('[STORAGE] Error guardando abono individual offline:', e)
                );
                return updated;
            });
            return;
        }

        // ── ONLINE ────────────────────────────────────────────────────────────
        if (!selectedCredit) return;
        try {
            const response = await apiFetch(API_ENDPOINTS.mobile_payments, {
                method: 'POST',
                body: JSON.stringify({
                    prestamo_id: selectedCredit.id,
                    monto:       paymentData.amount,
                    fecha_abono: getLocalDateString(),
                    local_id:    null,
                }),
            });

            const result = await response.json();

            if (result.success) {
                setIsModalVisible(false);
                const detail = selectedCredit.details;
                const now    = new Date().toLocaleString('es-NI');

                setReceiptData({
                    transactionNumber: result.abono_id
                        ? `REC-${String(result.abono_id).padStart(6, '0')}`
                        : 'N/A',
                    creditNumber:     selectedCredit.creditNumber || selectedCredit.id,
                    clientName:       selectedCredit.clientName,
                    clientCode:       selectedCredit.clientCode,
                    paymentDate:      now,
                    cuotaDelDia:      detail.dueTodayAmount,
                    montoAtrasado:    detail.overdueAmount,
                    diasMora:         detail.lateDays,
                    totalAPagar:      detail.dueTodayAmount + detail.overdueAmount,
                    montoCancelacion: detail.remainingBalance,
                    amountPaid:       paymentData.amount,
                    saldoAnterior:    detail.remainingBalance,
                    nuevoSaldo:       Math.max(0, detail.remainingBalance - paymentData.amount),
                    managedBy:        session.fullName,
                    sucursal:         session.sucursalName || 'SUCURSAL',
                    role:             session.role,
                });
                setIsReceiptVisible(true);

                // Generar registro de pago individual (como en el listado de abonos de la web)
                const newReceiptSnapshot: ReceiptData = {
                    transactionNumber: result.abono_id
                        ? `REC-${String(result.abono_id).padStart(6, '0')}`
                        : 'REC-LOCAL',
                    creditNumber:     selectedCredit.creditNumber || selectedCredit.id,
                    clientName:       selectedCredit.clientName,
                    clientCode:       selectedCredit.clientCode,
                    paymentDate:      now,
                    cuotaDelDia:      detail.dueTodayAmount,
                    montoAtrasado:    detail.overdueAmount,
                    diasMora:         detail.lateDays,
                    totalAPagar:      detail.dueTodayAmount + detail.overdueAmount,
                    montoCancelacion: detail.remainingBalance,
                    amountPaid:       paymentData.amount,
                    saldoAnterior:    detail.remainingBalance,
                    nuevoSaldo:       Math.max(0, detail.remainingBalance - paymentData.amount),
                    managedBy:        session.fullName,
                    sucursal:         session.sucursalName || 'SUCURSAL',
                    role:             session.role,
                };

                const individualPayment: TodayPaymentItem = {
                    id: result.abono_id ? `abono_${result.abono_id}` : `local_${Date.now()}`,
                    abonoId: result.abono_id || null,
                    creditId: selectedCredit.id,
                    creditNumber: selectedCredit.creditNumber || selectedCredit.id,
                    clientId: selectedCredit.clientId,
                    clientName: selectedCredit.clientName,
                    clientCode: selectedCredit.clientCode,
                    clientAddress: selectedCredit.clientAddress,
                    amountPaid: paymentData.amount,
                    saldoAnterior: detail.remainingBalance,
                    saldoActual: Math.max(0, detail.remainingBalance - paymentData.amount),
                    hora: new Date().toLocaleTimeString('es-NI', { hour: '2-digit', minute: '2-digit', hour12: true }),
                    fechaAbono: getLocalDateString(),
                    receiptNumber: result.abono_id ? `REC-${String(result.abono_id).padStart(6, '0')}` : 'REC-LOCAL',
                    receiptData: newReceiptSnapshot,
                    creditItem: {
                        ...selectedCredit,
                        details: {
                            ...selectedCredit.details,
                            remainingBalance: Math.max(0, detail.remainingBalance - paymentData.amount),
                            paidToday: (selectedCredit.details.paidToday || 0) + paymentData.amount,
                        },
                    },
                };

                setTodayPayments(prev => {
                    const updated = [individualPayment, ...prev.filter(p => p.id !== individualPayment.id)];
                    AsyncStorage.setItem(getTodayAbonosKey(), JSON.stringify(updated)).catch(e =>
                        console.error('[STORAGE] Error guardando abono individual:', e)
                    );
                    return updated;
                });

                // Mover el crédito a "Cobrado Hoy" en el portafolio
                const paid = {
                    ...selectedCredit,
                    ultimoAbonoId: result.abono_id || null,
                    details: {
                        ...selectedCredit.details,
                        paidToday: (selectedCredit.details.paidToday || 0) + paymentData.amount,
                        remainingBalance: Math.max(0, selectedCredit.details.remainingBalance - paymentData.amount),
                    },
                };
                const updatedPaidToday = [paid, ...portfolio.paidToday.filter(x => x.id !== paid.id)];
                AsyncStorage.setItem(getTodayKey(), JSON.stringify(updatedPaidToday)).catch(e =>
                    console.error('[STORAGE] Error guardando pago:', e)
                );

                setPortfolio(prev => {
                    const removeFrom = (list: CreditItem[]) =>
                        list.filter(x => x.id !== selectedCredit!.id);
                    return {
                        dueToday:  removeFrom(prev.dueToday),
                        overdue:   removeFrom(prev.overdue),
                        expired:   removeFrom(prev.expired),
                        upToDate:  removeFrom(prev.upToDate),
                        paidToday: [paid, ...prev.paidToday.filter(x => x.id !== paid.id)],
                    };
                });

                // Sincronizar en segundo plano con el servidor para tener los saldos y cuotas exactos
                setTimeout(() => {
                    fetchPortfolio();
                }, 1000);
            } else {
                AlertHelper.alert('Error', result.message || 'No se pudo registrar el abono');
            }
        } catch (error) {
            console.error('[PAGO] Error:', error);
            AlertHelper.alert('Error de conexión', 'No se pudo conectar con el servidor. Revisa tu internet.');
        }
    };

    // ─── Lista activa ─────────────────────────────────────────────────────────
    const currentKey  = TAB_KEYS[activeTab];
    const currentList = (portfolio as any)[currentKey] as CreditItem[];

    const filteredList = currentList.filter(c =>
        searchQuery.length === 0 ||
        c.clientName.toLowerCase().includes(searchQuery.toLowerCase())
    );

    // Filtrado de abonos individuales para la pestaña "Cobrado Hoy"
    const filteredPayments = todayPayments.filter(p =>
        searchQuery.length === 0 ||
        p.clientName.toLowerCase().includes(searchQuery.toLowerCase()) ||
        String(p.creditNumber).includes(searchQuery) ||
        p.receiptNumber.toLowerCase().includes(searchQuery.toLowerCase()) ||
        p.clientCode.toLowerCase().includes(searchQuery.toLowerCase())
    );

    const totalCobradoHoyMonto = filteredPayments.reduce((s, p) => s + (p.amountPaid || 0), 0);

    // ─── Render ───────────────────────────────────────────────────────────────
    return (
        <SafeAreaView style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor="#fff" translucent={false} />

            {/* ── Barra de búsqueda ─────────────────────────────────────── */}
            <View style={styles.searchBar}>
                <TouchableOpacity
                    onPress={() => {
                        setIsSearchActive(!isSearchActive);
                        if (isSearchActive) { setSearchQuery(''); setSearchResults([]); }
                    }}
                    style={styles.searchIcon}
                >
                    <MaterialCommunityIcons
                        name={isSearchActive ? 'close' : 'magnify'}
                        size={20} color="#334155"
                    />
                </TouchableOpacity>

                {isSearchActive ? (
                    <TextInput
                        style={styles.searchInput}
                        placeholder="Buscar cliente propio o externo (otra cartera)..."
                        value={searchQuery}
                        onChangeText={setSearchQuery}
                        autoFocus
                    />
                ) : (
                    <TouchableOpacity style={{ flex: 1 }} onPress={() => setIsSearchActive(true)}>
                        <Text style={styles.searchHint}>Buscar cliente (propio o externo)</Text>
                    </TouchableOpacity>
                )}
            </View>

            {/* ── Pestañas ──────────────────────────────────────────────── */}
            {!isSearchActive && (
                <View style={styles.tabsWrapper}>
                    <ScrollView horizontal showsHorizontalScrollIndicator={false}
                        contentContainerStyle={styles.tabsContainer}>
                        {TABS.map(tab => (
                            <TouchableOpacity
                                key={tab}
                                style={[styles.tabBtn, activeTab === tab && { borderBottomWidth: 3, borderBottomColor: TAB_COLOR[tab] }]}
                                onPress={() => { setActiveTab(tab); setExpandedCreditId(null); }}
                            >
                                <Text style={[styles.tabText, activeTab === tab && { color: TAB_COLOR[tab], fontWeight: 'bold' }]}>
                                    {tab} ({tab === "Cobrado Hoy" ? todayPayments.length : (((portfolio as any)[TAB_KEYS[tab]] as CreditItem[])?.length ?? 0)})
                                </Text>
                            </TouchableOpacity>
                        ))}
                    </ScrollView>
                </View>
            )}

            {/* ── Contenido ─────────────────────────────────────────────── */}
            {isLoading ? (
                <View style={styles.centered}>
                    <ActivityIndicator size="large" color="#0ea5e9" />
                </View>
            ) : isSearchActive ? (
                /* Resultados de búsqueda global y externos */
                <ScrollView contentContainerStyle={styles.listContainer}>
                    {searchQuery.trim().length < 2 ? (
                        <Text style={styles.emptyText}>Escribe al menos 2 caracteres para buscar...</Text>
                    ) : (
                        <>
                            {/* Resultados de Cartera Propia */}
                            {searchResults.length > 0 && (
                                <View style={{ marginBottom: 16 }}>
                                    <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 8, paddingHorizontal: 4 }}>
                                        <MaterialCommunityIcons name="briefcase-outline" size={18} color="#0ea5e9" style={{ marginRight: 6 }} />
                                        <Text style={{ fontSize: 13, fontWeight: '700', color: '#0369a1', textTransform: 'uppercase' }}>
                                            En Mi Cartera ({searchResults.length})
                                        </Text>
                                    </View>
                                    {searchResults.map((item, idx) => (
                                        <CreditCard
                                            key={`search_local_${item.id}_${idx}`}
                                            item={item}
                                            index={idx}
                                            tabColor="#0ea5e9"
                                            activeTab="Cobro Dia"
                                            onToggleExpand={() => handleSelectCredit(item)}
                                        />
                                    ))}
                                </View>
                            )}

                            {/* Resultados de Clientes Externos (No pertenecen a mi cartera) */}
                            <View style={{ marginTop: 4 }}>
                                <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 8, paddingHorizontal: 4 }}>
                                    <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                                        <MaterialCommunityIcons name="account-search" size={18} color="#f59e0b" style={{ marginRight: 6 }} />
                                        <Text style={{ fontSize: 13, fontWeight: '700', color: '#b45309', textTransform: 'uppercase' }}>
                                            Clientes Externos (Otra Cartera)
                                        </Text>
                                    </View>
                                    {isSearchingExternal && (
                                        <ActivityIndicator size="small" color="#f59e0b" />
                                    )}
                                </View>

                                {externalResults.length > 0 ? (
                                    externalResults.map((item, idx) => (
                                        <CreditCard
                                            key={`search_ext_${item.id}_${idx}`}
                                            item={item}
                                            index={idx}
                                            tabColor="#f59e0b"
                                            activeTab="Cobro Dia"
                                            onToggleExpand={() => handleSelectCredit(item)}
                                        />
                                    ))
                                ) : !isSearchingExternal && searchResults.length === 0 ? (
                                    <View style={styles.emptyContainer}>
                                        <MaterialCommunityIcons name="account-search-outline" size={48} color="#cbd5e1" />
                                        <Text style={styles.emptyText}>No se encontraron clientes ni en tu cartera ni en clientes externos.</Text>
                                    </View>
                                ) : !isSearchingExternal && (
                                    <Text style={[styles.emptyText, { marginVertical: 10 }]}>No hay clientes externos que coincidan con la búsqueda.</Text>
                                )}
                            </View>
                        </>
                    )}
                </ScrollView>
            ) : (
                <ScrollView
                    contentContainerStyle={styles.listContainer}
                    refreshControl={
                        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={['#0ea5e9']} />
                    }
                >
                    {activeTab === 'Cobrado Hoy' ? (
                        filteredPayments.length === 0 ? (
                            <View style={styles.emptyContainer}>
                                <MaterialCommunityIcons name="receipt-text-outline" size={54} color="#cbd5e1" />
                                <Text style={styles.emptyTitle}>No hay cobros registrados hoy</Text>
                                <Text style={styles.emptyText}>
                                    Los pagos que apliques hoy aparecerán aquí separados individualmente con su recibo y hora de cobro.
                                </Text>
                            </View>
                        ) : (
                            <>
                                {/* Resumen superior estilo Web */}
                                <View style={styles.summaryBanner}>
                                    <View>
                                        <Text style={styles.summaryTitle}>TOTAL COBRADO HOY</Text>
                                        <Text style={styles.summaryTotal}>C$ {fmt(totalCobradoHoyMonto)}</Text>
                                    </View>
                                    <View style={styles.summaryBadge}>
                                        <MaterialCommunityIcons name="receipt" size={16} color="#065f46" />
                                        <Text style={styles.summaryBadgeText}>
                                            {filteredPayments.length} {filteredPayments.length === 1 ? 'cobro' : 'cobros'}
                                        </Text>
                                    </View>
                                </View>

                                {/* Listado de Abonos / Cobros individuales */}
                                {filteredPayments.map((payment, index) => (
                                    <PaymentTransactionCard
                                        key={`payment_${payment.id}_${index}`}
                                        item={payment}
                                        index={index}
                                        onReprint={() => handleReprintPayment(payment)}
                                        onPayAgain={() => {
                                            const cr = payment.creditItem || allItems.find(c => c.id === payment.creditId);
                                            if (cr) {
                                                handleSelectCredit(cr);
                                            } else {
                                                handleSelectCredit({
                                                    id: payment.creditId,
                                                    id_enc: '',
                                                    creditNumber: payment.creditNumber,
                                                    clientName: payment.clientName,
                                                    clientAddress: payment.clientAddress || '',
                                                    clientCode: payment.clientCode || '',
                                                    clientId: payment.clientId,
                                                    remainingBalance: payment.saldoActual,
                                                    collectionsManager: '',
                                                    cuotaNumero: 0,
                                                    details: {
                                                        dueTodayAmount: 0,
                                                        overdueAmount: 0,
                                                        remainingBalance: payment.saldoActual,
                                                        lateDays: 0,
                                                        diasVencido: 0,
                                                        paidToday: 0,
                                                    },
                                                });
                                            }
                                        }}
                                        isReprinting={isReprinting}
                                    />
                                ))}
                            </>
                        )
                    ) : filteredList.length === 0 ? (
                        <View style={styles.emptyContainer}>
                            <MaterialCommunityIcons
                                name={
                                    activeTab === 'Cobro Dia' ? 'calendar-check' :
                                    activeTab === 'Mora'      ? 'alert-circle'   :
                                    activeTab === 'Vencido'   ? 'close-circle'   :
                                    'check-all'
                                }
                                size={48}
                                color="#e2e8f0"
                            />
                            <Text style={styles.emptyText}>
                                {activeTab === 'Cobro Dia'   ? 'No hay cuotas programadas para hoy' :
                                 activeTab === 'Mora'        ? 'No hay clientes en mora' :
                                 activeTab === 'Vencido'     ? 'No hay préstamos vencidos' :
                                 'Todos los clientes están al día'}
                            </Text>
                        </View>
                    ) : (
                        filteredList.map((item, index) => (
                            <CreditCard
                                key={`${activeTab}_${item.id}_${index}`}
                                item={item}
                                index={index}
                                tabColor={TAB_COLOR[activeTab]}
                                activeTab={activeTab}
                                isExpanded={false}
                                onToggleExpand={() => handleSelectCredit(item)}
                                onApplyPayment={() => handleSelectCredit(item)}
                                onReprint={() => handleReprint(item)}
                                isReprinting={isReprinting}
                            />
                        ))
                    )}
                </ScrollView>
            )}

            {/* ── Modal de pago ─────────────────────────────────────────── */}
            <PaymentModal
                visible={isModalVisible}
                onClose={() => setIsModalVisible(false)}
                credit={selectedCredit}
                onPay={handleProcessPayment}
            />

            {/* ── Recibo ────────────────────────────────────────────────── */}
            <ReceiptModal
                visible={isReceiptVisible}
                onClose={() => setIsReceiptVisible(false)}
                receipt={receiptData}
            />

            <CustomAlert
                visible={alert.visible}
                type={alert.type}
                title={alert.title}
                message={alert.message}
                onClose={() => setAlert(a => ({ ...a, visible: false }))}
            />
        </SafeAreaView>
    );
}

// ─── Tarjeta de crédito ───────────────────────────────────────────────────────
function CreditCard({
    item, index, tabColor, activeTab,
    isExpanded, onToggleExpand, onApplyPayment, onReprint, isReprinting,
}: {
    item: CreditItem;
    index: number;
    tabColor: string;
    activeTab: TabKey;
    isExpanded?: boolean;
    onToggleExpand: () => void;
    onApplyPayment?: () => void;
    onReprint?: () => void;
    isReprinting?: boolean;
}) {
    const detail = item.details;

    const renderSubtitle = () => {
        switch (activeTab) {
            case 'Cobro Dia':
                return (
                    <View style={styles.rowInfo}>
                        <Text style={styles.infoLabel}>
                            Cuota #{item.cuotaNumero}  •  Abono:{' '}
                            <Text style={[styles.infoValue, { color: '#10b981' }]}>
                                C$ {fmt(detail.dueTodayAmount)}
                            </Text>
                        </Text>
                    </View>
                );
            case 'Mora':
                return (
                    <View style={styles.rowInfo}>
                        <Text style={styles.infoLabel}>
                            Mora:{' '}
                            <Text style={[styles.infoValue, { color: '#e11d48' }]}>
                                {detail.lateDays} días
                            </Text>
                            {'  '}Monto:{' '}
                            <Text style={[styles.infoValue, { color: '#f97316' }]}>
                                C$ {fmt(detail.overdueAmount)}
                            </Text>
                        </Text>
                    </View>
                );
            case 'Vencido':
                return (
                    <View style={styles.rowInfo}>
                        <Text style={styles.infoLabel}>
                            Vencido:{' '}
                            <Text style={[styles.infoValue, { color: '#e11d48' }]}>
                                {detail.diasVencido} días
                            </Text>
                        </Text>
                    </View>
                );
            case 'Cobrado Hoy':
                return (
                    <View style={styles.rowInfo}>
                        <Text style={styles.infoLabel}>
                            Cobrado hoy:{' '}
                            <Text style={[styles.infoValue, { color: '#10b981' }]}>
                                C$ {fmt(detail.paidToday)}
                            </Text>
                        </Text>
                    </View>
                );
            default:
                return null;
        }
    };

    return (
        <View style={styles.cardContainer}>
            <TouchableOpacity 
                style={styles.card} 
                onPress={onToggleExpand} 
                activeOpacity={0.75}
            >
                {/* Avatar numérico */}
                <View style={[styles.avatar, { backgroundColor: tabColor }]}>
                    {index >= 0
                        ? <Text style={styles.avatarText}>{index + 1}</Text>
                        : <MaterialCommunityIcons name="account" size={18} color="#fff" />
                    }
                </View>

                <View style={styles.cardBody}>
                    {/* Nombre cliente */}
                    <Text style={styles.clientName} numberOfLines={1}>{item.clientName}</Text>

                    {/* Dirección (igual que la web) */}
                    {!!item.clientAddress && (
                        <Text style={styles.clientAddress} numberOfLines={1}>{item.clientAddress}</Text>
                    )}

                    {/* Subtítulo por pestaña */}
                    {renderSubtitle()}

                    {/* Saldo pendiente y promedio */}
                    <View style={styles.rowInfo}>
                        <Text style={styles.infoLabel}>
                            Saldo:{' '}
                            <Text style={[styles.infoValue, { color: '#0ea5e9' }]}>
                                C$ {fmt(detail.remainingBalance)}
                            </Text>
                            {'  •  '}Promedio:{' '}
                            <Text style={[styles.infoValue, { color: (item.promedio_atraso ?? 0) > 2.5 ? '#ef4444' : '#1e293b' }]}>
                                {(item.promedio_atraso ?? 0).toFixed(1)}
                            </Text>
                        </Text>
                    </View>
                </View>

                {/* Ícono de acción */}
                <MaterialCommunityIcons 
                    name={activeTab === 'Cobrado Hoy' ? (isExpanded ? 'chevron-up' : 'chevron-down') : 'chevron-right'} 
                    size={22} 
                    color={activeTab === 'Cobrado Hoy' && isExpanded ? '#0ea5e9' : '#cbd5e1'} 
                />
            </TouchableOpacity>

            {/* Opciones desplegables debajo del cliente al seleccionarlo en "Cobrado Hoy" */}
            {activeTab === 'Cobrado Hoy' && isExpanded && (
                <View style={styles.actionsRow}>
                    <TouchableOpacity 
                        style={[styles.actionBtn, styles.btnPay]}
                        onPress={onApplyPayment}
                        activeOpacity={0.8}
                    >
                        <MaterialCommunityIcons name="cash-plus" size={18} color="#fff" />
                        <Text style={styles.actionBtnText}>Aplicar Pago</Text>
                    </TouchableOpacity>

                    <TouchableOpacity 
                        style={[styles.actionBtn, styles.btnReprint]}
                        onPress={onReprint}
                        activeOpacity={0.8}
                        disabled={isReprinting}
                    >
                        {isReprinting ? (
                            <ActivityIndicator size="small" color="#fff" />
                        ) : (
                            <>
                                <MaterialCommunityIcons name="printer" size={18} color="#fff" />
                                <Text style={styles.actionBtnText}>Reimprimir</Text>
                            </>
                        )}
                    </TouchableOpacity>
                </View>
            )}
        </View>
    );
}

const fmt = (n: number) =>
    Number(n || 0).toLocaleString('es-NI', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

// ─── Tarjeta de Transacción de Abono Individual (Pestaña "Cobrado Hoy") ──────────
function PaymentTransactionCard({
    item, index, onReprint, onPayAgain, isReprinting,
}: {
    item: TodayPaymentItem;
    index: number;
    onReprint: () => void;
    onPayAgain: () => void;
    isReprinting?: boolean;
}) {
    return (
        <View style={styles.paymentCard}>
            {/* Cabecera del pago */}
            <View style={styles.paymentCardHeader}>
                <View style={styles.paymentAvatar}>
                    <MaterialCommunityIcons name="receipt" size={20} color="#059669" />
                </View>
                <View style={{ flex: 1 }}>
                    <Text style={styles.clientName} numberOfLines={1}>{item.clientName}</Text>
                    <View style={styles.receiptMetaRow}>
                        <View style={styles.receiptTag}>
                            <Text style={styles.receiptTagText}>{item.receiptNumber}</Text>
                        </View>
                        <Text style={styles.paymentTimeText}>
                            <MaterialCommunityIcons name="clock-outline" size={12} color="#64748b" /> {item.hora || 'Hoy'}
                        </Text>
                        <Text style={styles.paymentCreditText}>
                            Crédito #{item.creditNumber}
                        </Text>
                    </View>
                </View>
            </View>

            {/* Caja de montos y saldos de este cobro */}
            <View style={styles.paymentDetailsBox}>
                <View style={styles.detailCol}>
                    <Text style={styles.detailBoxLabel}>Monto Cobrado</Text>
                    <Text style={styles.detailBoxValuePaid}>C$ {fmt(item.amountPaid)}</Text>
                </View>
                <View style={styles.detailColDivider} />
                <View style={styles.detailCol}>
                    <Text style={styles.detailBoxLabel}>Saldo Anterior</Text>
                    <Text style={styles.detailBoxValue}>C$ {fmt(item.saldoAnterior)}</Text>
                </View>
                <View style={styles.detailColDivider} />
                <View style={styles.detailCol}>
                    <Text style={styles.detailBoxLabel}>Nuevo Saldo</Text>
                    <Text style={[styles.detailBoxValue, { color: item.saldoActual === 0 ? '#10b981' : '#0ea5e9' }]}>
                        {item.saldoActual === 0 ? 'CANCELADO' : `C$ ${fmt(item.saldoActual)}`}
                    </Text>
                </View>
            </View>

            {/* Botones de acción independientes por cada cobro */}
            <View style={styles.paymentActionsRow}>
                <TouchableOpacity
                    style={[styles.paymentActionBtn, styles.btnReprintSmall]}
                    onPress={onReprint}
                    activeOpacity={0.8}
                    disabled={isReprinting}
                >
                    {isReprinting ? (
                        <ActivityIndicator size="small" color="#fff" />
                    ) : (
                        <>
                            <MaterialCommunityIcons name="printer" size={16} color="#fff" />
                            <Text style={styles.paymentActionBtnText}>Reimprimir Recibo</Text>
                        </>
                    )}
                </TouchableOpacity>

                <TouchableOpacity
                    style={[styles.paymentActionBtn, styles.btnPayAgainSmall]}
                    onPress={onPayAgain}
                    activeOpacity={0.8}
                >
                    <MaterialCommunityIcons name="cash-plus" size={16} color="#065f46" />
                    <Text style={[styles.paymentActionBtnText, { color: '#065f46' }]}>Cobrar de Nuevo</Text>
                </TouchableOpacity>
            </View>
        </View>
    );
}

// ─── Estilos ──────────────────────────────────────────────────────────────────
const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#fff',
        paddingTop: Platform.OS === 'android' ? StatusBar.currentHeight : 0,
    },
    centered: { flex: 1, justifyContent: 'center', alignItems: 'center' },

    // Barra de búsqueda
    searchBar: {
        flexDirection: 'row',
        alignItems: 'center',
        marginHorizontal: 16,
        marginTop: 12,
        marginBottom: 10,
        backgroundColor: '#f1f5f9',
        borderRadius: 12,
        paddingHorizontal: 12,
        height: 44,
    },
    searchIcon:  { marginRight: 8 },
    searchInput: { flex: 1, fontSize: 14, color: '#1e293b' },
    searchHint:  { fontSize: 13, color: '#94a3b8' },

    // Pestañas
    tabsWrapper:    { borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    tabsContainer:  { paddingHorizontal: 8 },
    tabBtn: {
        paddingHorizontal: 14,
        paddingVertical: 10,
        marginHorizontal: 2,
    },
    tabText: { fontSize: 12, color: '#64748b', fontWeight: '600' },

    // Lista
    listContainer: { padding: 14, paddingBottom: Platform.OS === "android" ? 110 : 80 },
    emptyContainer: { alignItems: 'center', marginTop: 60, gap: 12 },
    emptyText: { textAlign: 'center', color: '#94a3b8', fontSize: 14, marginTop: 8 },

    // Tarjeta
    cardContainer: {
        borderBottomWidth: 1,
        borderBottomColor: '#f1f5f9',
        paddingVertical: 4,
    },
    actionsRow: {
        flexDirection: 'row',
        gap: 10,
        paddingHorizontal: 48,
        paddingVertical: 10,
        backgroundColor: '#f8fafc',
        borderRadius: 10,
        marginBottom: 8,
    },
    actionBtn: {
        flex: 1,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: 10,
        paddingHorizontal: 12,
        borderRadius: 8,
        gap: 6,
        elevation: 1,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 1 },
        shadowOpacity: 0.1,
        shadowRadius: 2,
    },
    btnPay: {
        backgroundColor: '#10b981',
    },
    btnReprint: {
        backgroundColor: '#f59e0b',
    },
    actionBtnText: {
        color: '#fff',
        fontSize: 13,
        fontWeight: '700',
    },
    card: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingVertical: 12,
        borderBottomWidth: 1,
        borderBottomColor: '#f1f5f9',
        gap: 10,
    },
    avatar: {
        width: 38, height: 38,
        borderRadius: 19,
        alignItems: 'center',
        justifyContent: 'center',
        flexShrink: 0,
    },
    avatarText:    { color: '#fff', fontSize: 15, fontWeight: 'bold' },
    cardBody:      { flex: 1, gap: 2 },
    clientName:    { fontSize: 14, fontWeight: '700', color: '#1e293b' },
    clientAddress: { fontSize: 11, color: '#94a3b8' },
    rowInfo:       { flexDirection: 'row', flexWrap: 'wrap' },
    infoLabel:     { fontSize: 12, color: '#64748b' },
    infoValue:     { fontWeight: '700' },

    // Estilos de la Pestaña Cobrado Hoy Dividida
    emptyTitle: { fontSize: 16, fontWeight: '700', color: '#475569', marginTop: 10 },
    summaryBanner: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        backgroundColor: '#ecfdf5',
        borderColor: '#a7f3d0',
        borderWidth: 1,
        borderRadius: 12,
        paddingHorizontal: 16,
        paddingVertical: 12,
        marginBottom: 12,
    },
    summaryTitle: { fontSize: 11, fontWeight: '700', color: '#047857', letterSpacing: 0.5 },
    summaryTotal: { fontSize: 20, fontWeight: 'bold', color: '#065f46', marginTop: 2 },
    summaryBadge: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#d1fae5',
        paddingHorizontal: 10,
        paddingVertical: 6,
        borderRadius: 20,
        gap: 4,
    },
    summaryBadgeText: { fontSize: 12, fontWeight: '700', color: '#065f46' },

    paymentCard: {
        backgroundColor: '#ffffff',
        borderRadius: 14,
        borderWidth: 1,
        borderColor: '#e2e8f0',
        padding: 14,
        marginBottom: 10,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 1 },
        shadowOpacity: 0.05,
        shadowRadius: 3,
        elevation: 2,
    },
    paymentCardHeader: {
        flexDirection: 'row',
        alignItems: 'center',
    },
    paymentAvatar: {
        width: 38,
        height: 38,
        borderRadius: 19,
        backgroundColor: '#ecfdf5',
        alignItems: 'center',
        justifyContent: 'center',
        marginRight: 10,
    },
    receiptMetaRow: {
        flexDirection: 'row',
        alignItems: 'center',
        marginTop: 3,
        gap: 8,
        flexWrap: 'wrap',
    },
    receiptTag: {
        backgroundColor: '#f1f5f9',
        paddingHorizontal: 6,
        paddingVertical: 2,
        borderRadius: 4,
    },
    receiptTagText: {
        fontSize: 11,
        fontWeight: '700',
        color: '#334155',
    },
    paymentTimeText: {
        fontSize: 11,
        color: '#64748b',
    },
    paymentCreditText: {
        fontSize: 11,
        fontWeight: '600',
        color: '#0ea5e9',
    },
    paymentDetailsBox: {
        flexDirection: 'row',
        backgroundColor: '#f8fafc',
        borderRadius: 10,
        paddingVertical: 10,
        paddingHorizontal: 6,
        marginVertical: 10,
        borderWidth: 1,
        borderColor: '#f1f5f9',
        alignItems: 'center',
    },
    detailCol: {
        flex: 1,
        alignItems: 'center',
    },
    detailColDivider: {
        width: 1,
        height: 24,
        backgroundColor: '#e2e8f0',
    },
    detailBoxLabel: {
        fontSize: 10,
        fontWeight: '600',
        color: '#64748b',
        textTransform: 'uppercase',
        marginBottom: 2,
    },
    detailBoxValue: {
        fontSize: 13,
        fontWeight: '700',
        color: '#1e293b',
    },
    detailBoxValuePaid: {
        fontSize: 14,
        fontWeight: '800',
        color: '#059669',
    },
    paymentActionsRow: {
        flexDirection: 'row',
        gap: 8,
        marginTop: 2,
    },
    paymentActionBtn: {
        flex: 1,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: 9,
        paddingHorizontal: 10,
        borderRadius: 8,
        gap: 6,
    },
    btnReprintSmall: {
        backgroundColor: '#059669',
    },
    btnPayAgainSmall: {
        backgroundColor: '#ecfdf5',
        borderWidth: 1,
        borderColor: '#a7f3d0',
    },
    paymentActionBtnText: {
        fontSize: 12,
        fontWeight: '700',
        color: '#ffffff',
    },
});
