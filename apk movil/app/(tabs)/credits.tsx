import AsyncStorage from '@react-native-async-storage/async-storage';

const COBRADOS_HOY_KEY = '@credinic_cobrados_hoy_';
const getTodayKey = () => `${COBRADOS_HOY_KEY}${new Date().toLocaleDateString('en-CA')}`;
import {
    View, Text, StyleSheet, SafeAreaView, TouchableOpacity,
    ScrollView, TextInput, RefreshControl, ActivityIndicator,
    Modal, StatusBar, Platform,
} from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useState, useEffect, useCallback } from 'react';
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
    const fetchPortfolio = useCallback(async () => {
        const session = await sessionService.getSession();
        if (!session?.id) return;

        try {
            const resp   = await apiFetch(`${API_ENDPOINTS.mobile_portfolio}`);
            const result = await resp.json();

            if (result.success) {
                const hoy      = new Date().toISOString().split('T')[0];
                const clientes = result.clientes || [];

                const { dueToday, overdue, expired, upToDate, paidToday: classifiedPaidToday } =
                    clasificarPortfolio(clientes, hoy, session.fullName);

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

                setPortfolio(prev => {
                    const mergedPaid = [...classifiedPaidToday];
                    // Agregar los de AsyncStorage
                    for (const item of localSavedPaid) {
                        if (!mergedPaid.some(m => m.id === item.id)) {
                            mergedPaid.push(item);
                        }
                    }
                    // Agregar los del estado previo
                    for (const localPaid of prev.paidToday) {
                        if (!mergedPaid.some(m => m.id === localPaid.id)) {
                            mergedPaid.push(localPaid);
                        }
                    }
                    mergedPaid.sort((a, b) => a.clientName.localeCompare(b.clientName));
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

    useEffect(() => { fetchPortfolio(); }, [fetchPortfolio]);

    const onRefresh = () => {
        setRefreshing(true);
        fetchPortfolio();
    };

    // ─── Búsqueda local en toda la cartera ────────────────────────────────────
    useEffect(() => {
        if (!isSearchActive || searchQuery.length < 2) {
            setSearchResults([]);
            return;
        }
        const q = searchQuery.toLowerCase();
        setSearchResults(
            allItems.filter(i =>
                i.clientName.toLowerCase().includes(q) ||
                i.clientCode.toLowerCase().includes(q) ||
                String(i.creditNumber).includes(q)
            )
        );
    }, [searchQuery, isSearchActive, allItems]);


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

            // Guardar en DB local ya fue hecho en PaymentModal
            setReceiptData({
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
            });
            setIsReceiptVisible(true);
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
                    fecha_abono: new Date().toISOString().split('T')[0],
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

                // Mover el item a "Cobrado Hoy" y quitarlo de su pestaña original
                const paid = {
                    ...selectedCredit,
                    ultimoAbonoId: result.abono_id || null,
                    details: {
                        ...selectedCredit.details,
                        paidToday: paymentData.amount,
                        remainingBalance: Math.max(0, selectedCredit.details.remainingBalance - paymentData.amount),
                    },
                };
                setPortfolio(prev => {
                    const removeFrom = (list: CreditItem[]) =>
                        list.filter(x => x.id !== selectedCredit!.id);
                    return {
                        dueToday:  removeFrom(prev.dueToday),
                        overdue:   removeFrom(prev.overdue),
                        expired:   removeFrom(prev.expired),
                        upToDate:  removeFrom(prev.upToDate),
                        paidToday: [paid, ...prev.paidToday],
                    };
                });
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
                        placeholder="Buscar cliente, cédula o # crédito..."
                        value={searchQuery}
                        onChangeText={setSearchQuery}
                        autoFocus
                    />
                ) : (
                    <TouchableOpacity style={{ flex: 1 }} onPress={() => setIsSearchActive(true)}>
                        <Text style={styles.searchHint}>Buscar en toda la cartera</Text>
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
                                onPress={() => setActiveTab(tab)}
                            >
                                <Text style={[styles.tabText, activeTab === tab && { color: TAB_COLOR[tab], fontWeight: 'bold' }]}>
                                    {tab} ({((portfolio as any)[TAB_KEYS[tab]] as CreditItem[])?.length ?? 0})
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
                /* Resultados de búsqueda global */
                <ScrollView contentContainerStyle={styles.listContainer}>
                    {searchQuery.length < 2 ? (
                        <Text style={styles.emptyText}>Escribe al menos 2 caracteres...</Text>
                    ) : searchResults.length === 0 ? (
                        <Text style={styles.emptyText}>Sin resultados.</Text>
                    ) : (
                        searchResults.map(item => (
                            <CreditCard
                                key={`${item.id}`}
                                item={item}
                                index={-1}
                                tabColor="#64748b"
                                activeTab="Cobro Dia"
                                onToggleExpand={() => handleSelectCredit(item)}
                            />
                        ))
                    )}
                </ScrollView>
            ) : (
                <ScrollView
                    contentContainerStyle={styles.listContainer}
                    refreshControl={
                        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={['#0ea5e9']} />
                    }
                >
                    {filteredList.length === 0 ? (
                        <View style={styles.emptyContainer}>
                            <MaterialCommunityIcons
                                name={
                                    activeTab === 'Cobro Dia' ? 'calendar-check' :
                                    activeTab === 'Mora'      ? 'alert-circle'   :
                                    activeTab === 'Vencido'   ? 'close-circle'   :
                                    activeTab === 'Cobrado Hoy' ? 'check-circle' :
                                    'check-all'
                                }
                                size={48}
                                color="#e2e8f0"
                            />
                            <Text style={styles.emptyText}>
                                {activeTab === 'Cobro Dia'   ? 'No hay cuotas programadas para hoy' :
                                 activeTab === 'Mora'        ? 'No hay clientes en mora' :
                                 activeTab === 'Vencido'     ? 'No hay préstamos vencidos' :
                                 activeTab === 'Cobrado Hoy' ? 'No se ha cobrado nada hoy' :
                                 'Todos los clientes están al día'}
                            </Text>
                        </View>
                    ) : (
                        filteredList.map((item, index) => (
                            <CreditCard
                                key={`${item.id}`}
                                item={item}
                                index={index}
                                tabColor={TAB_COLOR[activeTab]}
                                activeTab={activeTab}
                                isExpanded={activeTab === 'Cobrado Hoy' && expandedCreditId === item.id}
                                onToggleExpand={() => {
                                    if (activeTab === 'Cobrado Hoy') {
                                        setExpandedCreditId(expandedCreditId === item.id ? null : item.id);
                                    } else {
                                        handleSelectCredit(item);
                                    }
                                }}
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

                    {/* Saldo pendiente */}
                    <Text style={styles.infoLabel}>
                        Saldo:{' '}
                        <Text style={[styles.infoValue, { color: '#e11d48' }]}>
                            C$ {fmt(detail.remainingBalance)}
                        </Text>
                    </Text>
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
    listContainer: { padding: 14, paddingBottom: 40 },
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
});
