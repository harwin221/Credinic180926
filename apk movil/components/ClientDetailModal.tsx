import React, { useState, useMemo } from 'react';
import { 
    Modal, 
    View, 
    Text, 
    StyleSheet, 
    TouchableOpacity, 
    ScrollView, 
    ActivityIndicator, 
    Platform, 
    Dimensions 
} from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';

interface ClientDetailModalProps {
    visible: boolean;
    onClose: () => void;
    credit: any | null;
    onApplyPayment?: (credit: any) => void;
    onReprintReceipt?: (payment: any, credit: any) => void;
}

type TabType = 'estado' | 'consolidado' | 'financiero';

const fmt = (n: any) => Number(n || 0).toLocaleString('es-NI', { 
    minimumFractionDigits: 2, 
    maximumFractionDigits: 2 
});

// Helper para formateo de fechas robusto (maneja YYYY-MM-DD, DD/MM/YYYY, ISO strings sin desajuste de zona horaria)
const formatDate = (dateValue: any) => {
    if (!dateValue) return 'N/A';
    try {
        const str = String(dateValue).trim();
        const matchYMD = str.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (matchYMD) {
            const [, y, m, d] = matchYMD;
            return `${d}/${m}/${y}`;
        }
        const matchDMY = str.match(/^(\d{2})\/(\d{2})\/(\d{4})/);
        if (matchDMY) {
            return matchDMY[0];
        }
        const d = new Date(dateValue);
        if (isNaN(d.getTime())) return 'N/A';
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        return `${day}/${month}/${year}`;
    } catch (e) {
        return 'N/A';
    }
};

const formatDateTime = (dateValue: any) => {
    if (!dateValue) return 'N/A';
    try {
        const str = String(dateValue).trim();
        const match = str.match(/^(\d{4})-(\d{2})-(\d{2})[T\s](\d{2}):(\d{2})/);
        if (match) {
            const [, y, m, d, hh, mm] = match;
            return `${d}/${m}/${y} ${hh}:${mm}`;
        }
        return formatDate(dateValue);
    } catch (e) {
        return 'N/A';
    }
};

export default function ClientDetailModal({ 
    visible, 
    onClose, 
    credit, 
    onApplyPayment, 
    onReprintReceipt 
}: ClientDetailModalProps) {
    const [activeTab, setActiveTab] = useState<TabType>('estado');

    const fullStatement = useMemo(() => {
        if (!credit?.fullStatement) {
            return {
                installments: [],
                payments: [],
                totals: {
                    plan: { cuota: 0, capital: 0, interes: 0, mora: 0, pagado: 0, saldo: 0 },
                    abonos: { total: 0, capital: 0, interes: 0, mora: 0 }
                }
            };
        }
        return credit.fullStatement;
    }, [credit]);

    if (!credit) return null;

    const details = credit.details || {};
    const moneda = credit.moneda || 'C$';

    // Totales calculados con fallbacks
    const installments = fullStatement.installments || [];
    const payments = fullStatement.payments || [];

    const totalAbonos = fullStatement.totals?.abonos?.total ?? 
        payments.reduce((acc: number, p: any) => acc + Number(p.amount || p.total || p.monto || 0), 0);

    const totalPlanFinanciado = credit.financedAmount || credit.montoFinanciado || 
        fullStatement.totals?.plan?.cuota || 
        installments.reduce((acc: number, i: any) => acc + Number(i.amount || i.quota || i.monto_cuota || 0), 0);

    const saldoPendiente = details.remainingBalance ?? Math.max(0, totalPlanFinanciado - totalAbonos);

    const promedioAtraso = credit.promedioDiasAtraso ?? 
        details.promedioDiasAtraso ?? 
        details.lateDays ?? 0;

    const fechaApertura = credit.deliveryDate || credit.fechaApertura || credit.fecha_desembolso;
    const fechaFinal = credit.dueDate || credit.fechaFinal || credit.fecha_ultimo_pago;

    return (
        <Modal visible={visible} animationType="slide" transparent onRequestClose={onClose}>
            <View style={styles.overlay}>
                <View style={styles.container}>
                    {/* Header Estilo Reporte Web (Logo + Títulos) */}
                    <View style={styles.webHeader}>
                        <View style={styles.webHeaderLeft}>
                            <View style={styles.logoBadge}>
                                <MaterialCommunityIcons name="shield-check" size={20} color="#0ea5e9" />
                                <Text style={styles.brandName}>CREDINIC</Text>
                            </View>
                            <View style={styles.titleContainer}>
                                <Text style={styles.webReportTitle}>ESTADO DE CUENTA</Text>
                                <Text style={styles.webReportSubtitle}>Cronograma de Cuotas del Préstamo</Text>
                            </View>
                        </View>
                        <TouchableOpacity onPress={onClose} style={styles.closeBtn} hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}>
                            <MaterialCommunityIcons name="close" size={24} color="#64748b" />
                        </TouchableOpacity>
                    </View>

                    {/* Barra de Pestañas */}
                    <View style={styles.reportTabs}>
                        <ReportTab 
                            active={activeTab === 'estado'} 
                            label="ESTADO CUENTA" 
                            icon="file-document-outline"
                            onPress={() => setActiveTab('estado')} 
                        />
                        <ReportTab 
                            active={activeTab === 'consolidado'} 
                            label="CONSOLIDADO" 
                            icon="account-group-outline"
                            onPress={() => setActiveTab('consolidado')} 
                        />
                        <ReportTab 
                            active={activeTab === 'financiero'} 
                            label="PLAN DETALLADO" 
                            icon="table-large"
                            onPress={() => setActiveTab('financiero')} 
                        />
                    </View>

                    <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.reportContent}>
                        {/* ─── TAB: ESTADO DE CUENTA (IDÉNTICO A LA WEB) ─── */}
                        {activeTab === 'estado' && (
                            <View style={styles.webReportContainer}>
                                {/* Caja 1: INFORMACIÓN DEL PRÉSTAMO */}
                                <View style={styles.infoSectionBox}>
                                    <View style={styles.infoSectionHeader}>
                                        <Text style={styles.infoSectionHeaderText}>INFORMACIÓN DEL PRÉSTAMO</Text>
                                    </View>
                                    <View style={styles.infoSectionRow}>
                                        <View style={styles.infoCol}>
                                            <Text style={styles.infoColLabel}>N° Préstamo:</Text>
                                            <Text style={styles.infoColValue}>#{credit.creditNumber}</Text>
                                        </View>
                                        <View style={styles.infoCol}>
                                            <Text style={styles.infoColLabel}>Fecha Apertura:</Text>
                                            <Text style={styles.infoColValue}>{formatDate(fechaApertura)}</Text>
                                        </View>
                                        <View style={styles.infoCol}>
                                            <Text style={styles.infoColLabel}>Fecha Final:</Text>
                                            <Text style={styles.infoColValue}>{formatDate(fechaFinal)}</Text>
                                        </View>
                                    </View>
                                </View>

                                {/* Caja 2: TABLA DATOS DEL PRÉSTAMO (Estilo Web Table) */}
                                <View style={styles.webTableCard}>
                                    <View style={styles.tableCardRow}>
                                        <Text style={styles.tableCardLabel}>Cliente:</Text>
                                        <Text style={[styles.tableCardValue, styles.textBold, { color: '#0ea5e9' }]}>
                                            {credit.clientName?.toUpperCase() || 'N/A'}
                                        </Text>
                                    </View>
                                    <View style={styles.tableCardRow}>
                                        <Text style={styles.tableCardLabel}>Dirección:</Text>
                                        <Text style={styles.tableCardValue}>
                                            {credit.clientAddress || credit.address || 'N/A'}
                                        </Text>
                                    </View>
                                    <View style={styles.tableCardRow}>
                                        <Text style={styles.tableCardLabel}>Cobrador:</Text>
                                        <Text style={styles.tableCardValue}>
                                            {credit.collectionsManager?.toUpperCase() || credit.cobrador?.toUpperCase() || 'N/A'}
                                        </Text>
                                    </View>
                                    <View style={styles.tableCardRowMulti}>
                                        <View style={styles.halfCol}>
                                            <Text style={styles.tableCardLabel}>Monto Prestado:</Text>
                                            <Text style={[styles.tableCardValue, styles.textBold]}>
                                                {moneda} {fmt(credit.totalAmount || credit.montoPrestado)}
                                            </Text>
                                        </View>
                                        <View style={styles.halfCol}>
                                            <Text style={styles.tableCardLabel}>Total a Pagar C+I:</Text>
                                            <Text style={[styles.tableCardValue, styles.textBold, { color: '#1f9cb5' }]}>
                                                {moneda} {fmt(credit.financedAmount || credit.montoFinanciado)}
                                            </Text>
                                        </View>
                                    </View>
                                    <View style={styles.tableCardRowMulti}>
                                        <View style={styles.halfCol}>
                                            <Text style={styles.tableCardLabel}>Plazo (Meses):</Text>
                                            <Text style={styles.tableCardValue}>
                                                {credit.term || credit.plazoPago || 1}
                                            </Text>
                                        </View>
                                        <View style={styles.halfCol}>
                                            <Text style={styles.tableCardLabel}>Frecuencia:</Text>
                                            <Text style={styles.tableCardValue}>
                                                {credit.paymentFrequency || credit.formaPago || 'N/A'}
                                            </Text>
                                        </View>
                                    </View>
                                    <View style={styles.tableCardRowMulti}>
                                        <View style={styles.halfCol}>
                                            <Text style={styles.tableCardLabel}>Estado:</Text>
                                            <View style={[styles.statusBadge, getStatusBadgeStyle(credit.status || credit.estadoPrestamo)]}>
                                                <Text style={styles.statusBadgeText}>
                                                    {credit.status || credit.estadoPrestamo || 'Activo'}
                                                </Text>
                                            </View>
                                        </View>
                                        <View style={styles.halfCol}>
                                            <Text style={styles.tableCardLabel}>Promedio Días Atraso:</Text>
                                            <Text style={[
                                                styles.tableCardValue, 
                                                styles.textBold, 
                                                { color: Number(promedioAtraso) > 3 ? '#dc2626' : (Number(promedioAtraso) > 0 ? '#ea580c' : '#16a34a') }
                                            ]}>
                                                {Number(promedioAtraso).toFixed(2)} días
                                            </Text>
                                        </View>
                                    </View>
                                </View>

                                {/* ─── SECCIÓN 1: CRONOGRAMA DE CUOTAS (Cabecera Teal #1f9cb5 como en la Web) ─── */}
                                <View style={styles.sectionBannerTeal}>
                                    <MaterialCommunityIcons name="calendar-clock" size={16} color="#ffffff" />
                                    <Text style={styles.sectionBannerText}>CRONOGRAMA DE CUOTAS</Text>
                                </View>

                                <View style={styles.webTableWrapper}>
                                    <View style={styles.webTableHeadTeal}>
                                        <Text style={[styles.thText, styles.colNrWeb]}>#</Text>
                                        <Text style={[styles.thText, styles.colDateWeb]}>Fecha Cuota</Text>
                                        <Text style={[styles.thText, styles.colAmountWeb]}>Monto Cuota</Text>
                                        <Text style={[styles.thText, styles.colStatusWeb]}>Estado</Text>
                                    </View>

                                    {installments.length > 0 ? (
                                        installments.map((item: any, idx: number) => {
                                            const itemDate = item.fecha_cuota || item.paymentDate || item.dueDate || item.date;
                                            const itemQuota = item.monto_cuota ?? item.quota ?? item.amount ?? 0;
                                            const status = item.status || (item.estado === 3 ? 'PAGADA' : (item.estado === 2 ? 'PARCIAL' : 'PENDIENTE'));
                                            
                                            return (
                                                <View 
                                                    key={idx} 
                                                    style={[
                                                        styles.webTableRow, 
                                                        idx % 2 === 1 && styles.webTableRowEven,
                                                        status === 'PAGADA' && styles.rowPaidSoft
                                                    ]}
                                                >
                                                    <Text style={[styles.tdText, styles.colNrWeb, styles.textBold]}>
                                                        {item.numero_cuota || item.paymentNumber || item.number || idx + 1}
                                                    </Text>
                                                    {/* FECHA FORMATEADA CORRECTAMENTE DD/MM/YYYY */}
                                                    <Text style={[styles.tdText, styles.colDateWeb, styles.textDateHighlight]}>
                                                        {formatDate(itemDate)}
                                                    </Text>
                                                    <Text style={[styles.tdText, styles.colAmountWeb, styles.textBold]}>
                                                        {moneda} {fmt(itemQuota)}
                                                    </Text>
                                                    <View style={styles.colStatusWeb}>
                                                        <View style={[styles.quotaStatusPill, getQuotaStatusStyle(status)]}>
                                                            <Text style={[styles.quotaStatusText, getQuotaStatusTextStyle(status)]}>
                                                                {status}
                                                            </Text>
                                                        </View>
                                                    </View>
                                                </View>
                                            );
                                        })
                                    ) : (
                                        <View style={styles.emptyTableBox}>
                                            <Text style={styles.emptyTableText}>No hay cuotas registradas para este crédito.</Text>
                                        </View>
                                    )}
                                </View>

                                {/* ─── SECCIÓN 2: ABONOS REALIZADOS (Cabecera Verde #28a745 como en la Web) ─── */}
                                <View style={[styles.sectionBannerGreen, { marginTop: 22 }]}>
                                    <MaterialCommunityIcons name="cash-check" size={16} color="#ffffff" />
                                    <Text style={styles.sectionBannerText}>ABONOS REALIZADOS</Text>
                                </View>

                                <View style={styles.webTableWrapper}>
                                    {payments.length > 0 ? (
                                        <>
                                            <View style={styles.webTableHeadGreen}>
                                                <Text style={[styles.thText, styles.colNrWeb]}>#</Text>
                                                <Text style={[styles.thText, styles.colDateWeb]}>Fecha Pago</Text>
                                                <Text style={[styles.thText, styles.colAmountWeb]}>Monto</Text>
                                                <Text style={[styles.thText, styles.colAgentWeb]}>Recibido Por</Text>
                                                <Text style={[styles.thText, { width: 32 }]}></Text>
                                            </View>

                                            {payments.map((p: any, idx: number) => {
                                                const pDate = p.fecha_pago || p.paymentDate || p.fecha_abono || p.date;
                                                const pMonto = p.amount ?? p.total ?? p.monto ?? 0;
                                                const pAgente = p.receivedBy || p.recibido_por || 'N/A';
                                                const isCanc = Boolean(p.is_cancelacion || (p.tipo && p.tipo.includes('Cancelación')));

                                                return (
                                                    <View 
                                                        key={idx} 
                                                        style={[
                                                            styles.webTableRow, 
                                                            idx % 2 === 1 && styles.webTableRowEven
                                                        ]}
                                                    >
                                                        <View style={styles.colNrWeb}>
                                                            <Text style={[styles.tdText, styles.textBold]}>{idx + 1}</Text>
                                                            {isCanc && (
                                                                <View style={styles.cancTinyBadge}>
                                                                    <Text style={styles.cancTinyText}>Canc.</Text>
                                                                </View>
                                                            )}
                                                        </View>
                                                        {/* FECHA PAGO FORMATEADA DD/MM/YYYY */}
                                                        <Text style={[styles.tdText, styles.colDateWeb, styles.textDateHighlight]}>
                                                            {formatDate(pDate)}
                                                        </Text>
                                                        <Text style={[styles.tdText, styles.colAmountWeb, styles.textBold, { color: '#15803d' }]}>
                                                            {moneda} {fmt(pMonto)}
                                                        </Text>
                                                        <Text 
                                                            style={[styles.tdText, styles.colAgentWeb, { fontSize: 10, color: '#475569' }]}
                                                            numberOfLines={1}
                                                        >
                                                            {pAgente}
                                                        </Text>
                                                        {onReprintReceipt ? (
                                                            <TouchableOpacity 
                                                                style={styles.actionIconBtn}
                                                                onPress={() => onReprintReceipt(p, credit)}
                                                                hitSlop={{ top: 6, bottom: 6, left: 6, right: 6 }}
                                                            >
                                                                <MaterialCommunityIcons name="printer" size={16} color="#0ea5e9" />
                                                            </TouchableOpacity>
                                                        ) : (
                                                            <View style={{ width: 32 }} />
                                                        )}
                                                    </View>
                                                );
                                            })}

                                            {/* Totales idénticos a la web */}
                                            {/* 1. TOTAL ABONADO (Verde suave #d4edda) */}
                                            <View style={styles.footerTotalRowGreen}>
                                                <Text style={styles.footerTotalLabel}>TOTAL ABONADO:</Text>
                                                <Text style={styles.footerTotalValueGreen}>
                                                    {moneda} {fmt(totalAbonos)}
                                                </Text>
                                                <View style={{ width: 32 }} />
                                            </View>

                                            {/* 2. SALDO PENDIENTE (Amarillo suave #fff3cd con texto rojo) */}
                                            <View style={styles.footerTotalRowYellow}>
                                                <Text style={styles.footerTotalLabel}>SALDO PENDIENTE:</Text>
                                                <Text style={styles.footerTotalValueRed}>
                                                    {moneda} {fmt(saldoPendiente)}
                                                </Text>
                                                <View style={{ width: 32 }} />
                                            </View>
                                        </>
                                    ) : (
                                        <View style={styles.noAbonosAlert}>
                                            <MaterialCommunityIcons name="alert-circle-outline" size={20} color="#dc2626" />
                                            <Text style={styles.noAbonosAlertText}>
                                                No se han registrado abonos para este préstamo.
                                            </Text>
                                        </View>
                                    )}
                                </View>
                            </View>
                        )}

                        {/* ─── TAB: CONSOLIDADO (HISTORIAL GENERAL DEL CLIENTE) ─── */}
                        {activeTab === 'consolidado' && (
                            <View style={styles.consolidatedView}>
                                <Text style={styles.tableTitle}>Resumen Global</Text>
                                <View style={styles.statsCard}>
                                    <StatRow 
                                        label="CRÉDITO PROMEDIO:" 
                                        value={`${moneda} ${fmt(credit.summary?.averageAmount || credit.totalAmount)}`} 
                                    />
                                    <StatRow 
                                        label="CANTIDAD DE CRÉDITOS:" 
                                        value={`${credit.summary?.totalCredits || 1}`} 
                                    />
                                    <StatRow 
                                        label="CÓDIGO DEL CLIENTE:" 
                                        value={credit.clientCode || 'N/A'} 
                                    />
                                    <StatRow 
                                        label="ACTIVIDAD ECONÓMICA:" 
                                        value={credit.summary?.economicActivity || 'No especificada'} 
                                    />
                                    <StatRow 
                                        label="PROMEDIO CRÉDITO ACTUAL:" 
                                        value={`${Number(details.avgLateDaysCredit || promedioAtraso || 0).toFixed(2)} días`} 
                                    />
                                    <StatRow 
                                        label="PROMEDIO GLOBAL:" 
                                        value={`${Number(credit.summary?.globalAverageLateDays || details.avgLateDaysGlobal || promedioAtraso || 0).toFixed(2)} días`} 
                                    />
                                </View>

                                <Text style={[styles.tableTitle, { marginTop: 20 }]}>Historial de Préstamos</Text>
                                <ScrollView horizontal showsHorizontalScrollIndicator={true} contentContainerStyle={{ paddingBottom: 10 }}>
                                    <View>
                                        <View style={styles.tableHeaderWide}>
                                            <Text style={[styles.cellTextWide, { width: 85 }, styles.textBold]}>Crédito #</Text>
                                            <Text style={[styles.cellTextWide, { width: 95 }, styles.textBold]}>Monto</Text>
                                            <Text style={[styles.cellTextWide, { width: 65 }, styles.textBold]}>Tasa</Text>
                                            <Text style={[styles.cellTextWide, { width: 55 }, styles.textBold]}>Plazo</Text>
                                            <Text style={[styles.cellTextWide, { width: 90 }, styles.textBold]}>Apertura</Text>
                                            <Text style={[styles.cellTextWide, { width: 90 }, styles.textBold]}>Vencimiento</Text>
                                            <Text style={[styles.cellTextWide, { width: 75 }, styles.textBold]}>Días Atraso</Text>
                                        </View>
                                        {(credit.history || []).length > 0 ? (
                                            credit.history.map((hist: any, hIdx: number) => (
                                                <View key={hIdx} style={[styles.tableRowWide, hIdx % 2 === 1 && styles.webTableRowEven]}>
                                                    <Text style={[styles.cellTextWide, { width: 85, fontWeight: '700' }]}>
                                                        #{hist.creditNumber || hist.id}
                                                    </Text>
                                                    <Text style={[styles.cellTextWide, { width: 95 }]}>
                                                        {moneda} {fmt(hist.amount || hist.monto)}
                                                    </Text>
                                                    <Text style={[styles.cellTextWide, { width: 65 }]}>
                                                        {Number(hist.interestRate || hist.tasa || 0).toFixed(2)}%
                                                    </Text>
                                                    <Text style={[styles.cellTextWide, { width: 55 }]}>
                                                        {hist.termMonths || hist.plazo || 1}m
                                                    </Text>
                                                    <Text style={[styles.cellTextWide, { width: 90 }]}>
                                                        {formatDate(hist.deliveryDate || hist.fecha_desembolso)}
                                                    </Text>
                                                    <Text style={[styles.cellTextWide, { width: 90 }]}>
                                                        {formatDate(hist.dueDate || hist.fecha_vencimiento)}
                                                    </Text>
                                                    <Text style={[
                                                        styles.cellTextWide, 
                                                        { width: 75, fontWeight: '700', color: Number(hist.avgLateDays || 0) > 1 ? '#dc2626' : '#16a34a' }
                                                    ]}>
                                                        {Number(hist.avgLateDays || 0).toFixed(2)}
                                                    </Text>
                                                </View>
                                            ))
                                        ) : (
                                            <View style={[styles.tableRowWide, { paddingVertical: 15 }]}>
                                                <Text style={{ color: '#94a3b8', fontStyle: 'italic' }}>
                                                    Solo crédito actual registrado (#{credit.creditNumber})
                                                </Text>
                                            </View>
                                        )}
                                    </View>
                                </ScrollView>
                            </View>
                        )}

                        {/* ─── TAB: PLAN DETALLADO FINANCIERO (CON CAPITAL, INTERÉS, SALDOS Y MORA) ─── */}
                        {activeTab === 'financiero' && (
                            <View>
                                <Text style={styles.tableTitle}>Plan de Pagos Completo (Auditoría Financiera)</Text>
                                <ScrollView horizontal showsHorizontalScrollIndicator={true} contentContainerStyle={{ paddingBottom: 15 }}>
                                    <View>
                                        <View style={styles.tableHeaderWide}>
                                            <Text style={[styles.cellTextWide, { width: 35 }, styles.textBold]}>#</Text>
                                            <Text style={[styles.cellTextWide, { width: 85 }, styles.textBold]}>Fecha</Text>
                                            <Text style={[styles.cellTextWide, { width: 80, textAlign: 'right' }, styles.textBold]}>Capital</Text>
                                            <Text style={[styles.cellTextWide, { width: 75, textAlign: 'right' }, styles.textBold]}>Interés</Text>
                                            <Text style={[styles.cellTextWide, { width: 80, textAlign: 'right' }, styles.textBold]}>Cuota</Text>
                                            <Text style={[styles.cellTextWide, { width: 80, textAlign: 'right' }, styles.textBold]}>Pagado</Text>
                                            <Text style={[styles.cellTextWide, { width: 85, textAlign: 'right' }, styles.textBold]}>Saldo</Text>
                                            <Text style={[styles.cellTextWide, { width: 75, textAlign: 'center' }, styles.textBold]}>Estado</Text>
                                        </View>

                                        {installments.map((item: any, idx: number) => {
                                            const itemDate = item.fecha_cuota || item.paymentDate || item.dueDate || item.date;
                                            const itemStatus = item.status || (item.estado === 3 ? 'PAGADA' : (item.estado === 2 ? 'PARCIAL' : 'PENDIENTE'));

                                            return (
                                                <View 
                                                    key={idx} 
                                                    style={[
                                                        styles.tableRowWide, 
                                                        idx % 2 === 1 && styles.webTableRowEven,
                                                        itemStatus === 'PAGADA' && styles.rowPaidSoft
                                                    ]}
                                                >
                                                    <Text style={[styles.cellTextWide, { width: 35, textAlign: 'center', fontWeight: '700' }]}>
                                                        {item.numero_cuota || item.paymentNumber || idx + 1}
                                                    </Text>
                                                    <Text style={[styles.cellTextWide, { width: 85, textAlign: 'center' }]}>
                                                        {formatDate(itemDate)}
                                                    </Text>
                                                    <Text style={[styles.cellTextWide, { width: 80, textAlign: 'right' }]}>
                                                        {fmt(item.capital ?? item.principal ?? 0)}
                                                    </Text>
                                                    <Text style={[styles.cellTextWide, { width: 75, textAlign: 'right' }]}>
                                                        {fmt(item.interes ?? item.interest ?? 0)}
                                                    </Text>
                                                    <Text style={[styles.cellTextWide, { width: 80, textAlign: 'right', fontWeight: '700', color: '#0284c7' }]}>
                                                        {fmt(item.monto_cuota ?? item.quota ?? item.amount ?? 0)}
                                                    </Text>
                                                    <Text style={[styles.cellTextWide, { width: 80, textAlign: 'right', color: '#16a34a', fontWeight: '600' }]}>
                                                        {fmt(item.pagado ?? item.paid ?? 0)}
                                                    </Text>
                                                    <Text style={[styles.cellTextWide, { width: 85, textAlign: 'right', fontWeight: '600' }]}>
                                                        {fmt(item.saldo ?? item.balance ?? 0)}
                                                    </Text>
                                                    <View style={{ width: 75, alignItems: 'center' }}>
                                                        <Text style={[
                                                            { fontSize: 10, fontWeight: '800' },
                                                            itemStatus === 'PAGADA' ? { color: '#16a34a' } : itemStatus === 'PARCIAL' ? { color: '#ea580c' } : { color: '#dc2626' }
                                                        ]}>
                                                            {itemStatus}
                                                        </Text>
                                                    </View>
                                                </View>
                                            );
                                        })}

                                        {/* Totales pie financiero */}
                                        <View style={[styles.tableRowWide, styles.tableFooterWide]}>
                                            <Text style={[styles.cellTextWide, { width: 120, fontWeight: '800' }]}>Totales</Text>
                                            <Text style={[styles.cellTextWide, { width: 80, textAlign: 'right', fontWeight: '800' }]}>
                                                {fmt(fullStatement.totals?.plan?.capital)}
                                            </Text>
                                            <Text style={[styles.cellTextWide, { width: 75, textAlign: 'right', fontWeight: '800' }]}>
                                                {fmt(fullStatement.totals?.plan?.interes)}
                                            </Text>
                                            <Text style={[styles.cellTextWide, { width: 80, textAlign: 'right', fontWeight: '800', color: '#0284c7' }]}>
                                                {fmt(fullStatement.totals?.plan?.cuota)}
                                            </Text>
                                            <Text style={[styles.cellTextWide, { width: 80, textAlign: 'right', fontWeight: '800', color: '#16a34a' }]}>
                                                {fmt(fullStatement.totals?.plan?.pagado)}
                                            </Text>
                                            <Text style={[styles.cellTextWide, { width: 85, textAlign: 'right', fontWeight: '800' }]}>
                                                {fmt(fullStatement.totals?.plan?.saldo)}
                                            </Text>
                                            <View style={{ width: 75 }} />
                                        </View>
                                    </View>
                                </ScrollView>
                            </View>
                        )}
                    </ScrollView>

                    {/* Footer con Botón de Acción si se permite pagar */}
                    {onApplyPayment && (
                        <View style={styles.modalFooterBar}>
                            <TouchableOpacity 
                                style={styles.applyPaymentBtn}
                                onPress={() => onApplyPayment(credit)}
                            >
                                <MaterialCommunityIcons name="cash-plus" size={20} color="#ffffff" />
                                <Text style={styles.applyPaymentBtnText}>Aplicar Abono a este Préstamo</Text>
                            </TouchableOpacity>
                        </View>
                    )}
                </View>
            </View>
        </Modal>
    );
}

function StatRow({ label, value }: { label: string; value: string }) {
    return (
        <View style={styles.statRow}>
            <Text style={styles.statLabel}>{label}</Text>
            <Text style={styles.statValue}>{value}</Text>
        </View>
    );
}

function ReportTab({ active, label, icon, onPress }: { active: boolean; label: string; icon: any; onPress: () => void }) {
    return (
        <TouchableOpacity style={[styles.tabBtn, active && styles.tabBtnActive]} onPress={onPress}>
            <MaterialCommunityIcons 
                name={icon} 
                size={16} 
                color={active ? '#ffffff' : '#64748b'} 
                style={{ marginRight: 5 }}
            />
            <Text style={[styles.tabBtnText, active && styles.tabBtnTextActive]}>{label}</Text>
        </TouchableOpacity>
    );
}

function getStatusBadgeStyle(status: string) {
    const s = String(status || '').toLowerCase();
    if (s.includes('activo') || s.includes('vigente')) return { backgroundColor: '#dcfce7', borderColor: '#86efac' };
    if (s.includes('pagad') || s.includes('cancelad')) return { backgroundColor: '#e0f2fe', borderColor: '#7dd3fc' };
    if (s.includes('mora') || s.includes('vencid')) return { backgroundColor: '#fee2e2', borderColor: '#fca5a5' };
    return { backgroundColor: '#f1f5f9', borderColor: '#cbd5e1' };
}

function getQuotaStatusStyle(status: string) {
    if (status === 'PAGADA') return { backgroundColor: '#dcfce7', borderColor: '#86efac' };
    if (status === 'PARCIAL') return { backgroundColor: '#ffedd5', borderColor: '#fdba74' };
    if (status === 'VENCIDA') return { backgroundColor: '#fee2e2', borderColor: '#fca5a5' };
    return { backgroundColor: '#f1f5f9', borderColor: '#cbd5e1' };
}

function getQuotaStatusTextStyle(status: string) {
    if (status === 'PAGADA') return { color: '#15803d' };
    if (status === 'PARCIAL') return { color: '#c2410c' };
    if (status === 'VENCIDA') return { color: '#b91c1c' };
    return { color: '#475569' };
}

const styles = StyleSheet.create({
    overlay: { 
        flex: 1, 
        backgroundColor: 'rgba(15, 23, 42, 0.7)', 
        justifyContent: 'flex-end' 
    },
    container: { 
        backgroundColor: '#ffffff', 
        borderTopLeftRadius: 24, 
        borderTopRightRadius: 24, 
        height: '95%', 
        width: '100%', 
        overflow: 'hidden' 
    },
    
    // Header estilo Web
    webHeader: { 
        paddingHorizontal: 16, 
        paddingVertical: 14, 
        borderBottomWidth: 1, 
        borderBottomColor: '#e2e8f0', 
        backgroundColor: '#ffffff',
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between'
    },
    webHeaderLeft: {
        flex: 1,
        flexDirection: 'row',
        alignItems: 'center',
        gap: 12
    },
    logoBadge: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#f0f9ff',
        paddingHorizontal: 8,
        paddingVertical: 4,
        borderRadius: 8,
        borderWidth: 1,
        borderColor: '#bae6fd',
        gap: 4
    },
    brandName: { 
        fontSize: 14, 
        fontWeight: '900', 
        color: '#0284c7',
        letterSpacing: 0.5
    },
    titleContainer: {
        flex: 1
    },
    webReportTitle: { 
        fontSize: 15, 
        fontWeight: '900', 
        color: '#0f172a',
        letterSpacing: 0.3
    },
    webReportSubtitle: { 
        fontSize: 11, 
        color: '#64748b',
        fontWeight: '500'
    },
    closeBtn: { 
        padding: 6,
        borderRadius: 20,
        backgroundColor: '#f1f5f9'
    },

    // Tabs
    reportTabs: { 
        flexDirection: 'row', 
        backgroundColor: '#f8fafc', 
        padding: 6, 
        gap: 6,
        borderBottomWidth: 1,
        borderBottomColor: '#e2e8f0'
    },
    tabBtn: { 
        flex: 1, 
        flexDirection: 'row',
        paddingVertical: 8, 
        alignItems: 'center', 
        justifyContent: 'center',
        borderRadius: 8 
    },
    tabBtnActive: { 
        backgroundColor: '#0284c7' 
    },
    tabBtnText: { 
        fontSize: 11, 
        fontWeight: '700', 
        color: '#64748b' 
    },
    tabBtnTextActive: { 
        color: '#ffffff' 
    },

    reportContent: { 
        padding: 14, 
        paddingBottom: 40 
    },

    webReportContainer: {
        gap: 12
    },

    // Caja INFORMACIÓN DEL PRÉSTAMO
    infoSectionBox: {
        backgroundColor: '#ffffff',
        borderWidth: 1,
        borderColor: '#cbd5e1',
        borderRadius: 8,
        overflow: 'hidden'
    },
    infoSectionHeader: {
        backgroundColor: '#f1f5f9',
        paddingVertical: 6,
        paddingHorizontal: 10,
        borderBottomWidth: 1,
        borderBottomColor: '#cbd5e1'
    },
    infoSectionHeaderText: {
        fontSize: 11,
        fontWeight: '800',
        color: '#334155',
        letterSpacing: 0.5
    },
    infoSectionRow: {
        flexDirection: 'row',
        padding: 10,
        backgroundColor: '#ffffff'
    },
    infoCol: {
        flex: 1
    },
    infoColLabel: {
        fontSize: 10,
        color: '#64748b',
        fontWeight: '600'
    },
    infoColValue: {
        fontSize: 11,
        fontWeight: '800',
        color: '#0f172a',
        marginTop: 2
    },

    // Tabla DATOS DEL PRÉSTAMO (Estilo Web Table)
    webTableCard: {
        backgroundColor: '#ffffff',
        borderWidth: 1,
        borderColor: '#dee2e6',
        borderRadius: 8,
        overflow: 'hidden'
    },
    tableCardRow: {
        flexDirection: 'row',
        paddingVertical: 6,
        paddingHorizontal: 10,
        borderBottomWidth: 1,
        borderBottomColor: '#dee2e6',
        alignItems: 'center'
    },
    tableCardRowMulti: {
        flexDirection: 'row',
        borderBottomWidth: 1,
        borderBottomColor: '#dee2e6'
    },
    halfCol: {
        flex: 1,
        flexDirection: 'row',
        paddingVertical: 6,
        paddingHorizontal: 10,
        alignItems: 'center',
        borderRightWidth: 0.5,
        borderRightColor: '#dee2e6'
    },
    tableCardLabel: {
        fontSize: 10.5,
        fontWeight: '700',
        color: '#475569',
        marginRight: 6
    },
    tableCardValue: {
        fontSize: 11,
        color: '#1e293b',
        flex: 1
    },
    statusBadge: {
        paddingHorizontal: 6,
        paddingVertical: 2,
        borderRadius: 4,
        borderWidth: 1
    },
    statusBadgeText: {
        fontSize: 10,
        fontWeight: '800',
        color: '#0f172a'
    },

    // Banners de sección
    sectionBannerTeal: {
        backgroundColor: '#1f9cb5',
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: 7,
        borderRadius: 6,
        gap: 6,
        marginTop: 6
    },
    sectionBannerGreen: {
        backgroundColor: '#28a745',
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: 7,
        borderRadius: 6,
        gap: 6
    },
    sectionBannerText: {
        color: '#ffffff',
        fontSize: 12,
        fontWeight: '800',
        letterSpacing: 0.8
    },

    // Estilo de tabla web idéntica
    webTableWrapper: {
        borderWidth: 1,
        borderColor: '#cbd5e1',
        borderRadius: 6,
        overflow: 'hidden',
        marginTop: 4,
        backgroundColor: '#ffffff'
    },
    webTableHeadTeal: {
        flexDirection: 'row',
        backgroundColor: '#1f9cb5',
        paddingVertical: 8,
        paddingHorizontal: 6,
        alignItems: 'center'
    },
    webTableHeadGreen: {
        flexDirection: 'row',
        backgroundColor: '#28a745',
        paddingVertical: 8,
        paddingHorizontal: 6,
        alignItems: 'center'
    },
    thText: {
        color: '#ffffff',
        fontSize: 11,
        fontWeight: '800',
        textAlign: 'center'
    },
    colNrWeb: {
        width: 32,
        textAlign: 'center',
        alignItems: 'center'
    },
    colDateWeb: {
        width: 85,
        textAlign: 'center'
    },
    colAmountWeb: {
        flex: 1,
        textAlign: 'right',
        paddingRight: 6
    },
    colStatusWeb: {
        width: 80,
        alignItems: 'center',
        justifyContent: 'center'
    },
    colAgentWeb: {
        width: 85,
        textAlign: 'center'
    },

    webTableRow: {
        flexDirection: 'row',
        paddingVertical: 8,
        paddingHorizontal: 6,
        borderBottomWidth: 1,
        borderBottomColor: '#e2e8f0',
        alignItems: 'center'
    },
    webTableRowEven: {
        backgroundColor: '#f8fafc'
    },
    rowPaidSoft: {
        backgroundColor: '#f0fdf4'
    },
    tdText: {
        fontSize: 11,
        color: '#334155'
    },
    textDateHighlight: {
        color: '#0f172a',
        fontWeight: '700'
    },
    textBold: {
        fontWeight: '800'
    },

    quotaStatusPill: {
        paddingHorizontal: 6,
        paddingVertical: 2,
        borderRadius: 4,
        borderWidth: 1
    },
    quotaStatusText: {
        fontSize: 9.5,
        fontWeight: '800'
    },

    actionIconBtn: {
        width: 32,
        height: 28,
        alignItems: 'center',
        justifyContent: 'center',
        borderRadius: 4,
        backgroundColor: '#f0f9ff'
    },

    // Totales web en Abonos
    footerTotalRowGreen: {
        flexDirection: 'row',
        backgroundColor: '#d4edda',
        paddingVertical: 8,
        paddingHorizontal: 10,
        borderTopWidth: 1,
        borderTopColor: '#c3e6cb',
        alignItems: 'center',
        justifyContent: 'flex-end'
    },
    footerTotalRowYellow: {
        flexDirection: 'row',
        backgroundColor: '#fff3cd',
        paddingVertical: 8,
        paddingHorizontal: 10,
        borderTopWidth: 1,
        borderTopColor: '#ffeeba',
        alignItems: 'center',
        justifyContent: 'flex-end'
    },
    footerTotalLabel: {
        fontSize: 11,
        fontWeight: '800',
        color: '#1e293b',
        marginRight: 10
    },
    footerTotalValueGreen: {
        fontSize: 12,
        fontWeight: '900',
        color: '#155724'
    },
    footerTotalValueRed: {
        fontSize: 12,
        fontWeight: '900',
        color: '#dc3545'
    },

    noAbonosAlert: {
        backgroundColor: '#fef2f2',
        borderLeftWidth: 4,
        borderLeftColor: '#dc2626',
        padding: 12,
        flexDirection: 'row',
        alignItems: 'center',
        gap: 8,
        justifyContent: 'center'
    },
    noAbonosAlertText: {
        color: '#991b1b',
        fontSize: 11.5,
        fontWeight: '700'
    },

    emptyTableBox: {
        padding: 20,
        alignItems: 'center'
    },
    emptyTableText: {
        color: '#94a3b8',
        fontSize: 12,
        fontStyle: 'italic'
    },

    cancTinyBadge: {
        backgroundColor: '#7c3aed',
        paddingHorizontal: 3,
        paddingVertical: 1,
        borderRadius: 3,
        marginTop: 2
    },
    cancTinyText: {
        color: '#ffffff',
        fontSize: 7.5,
        fontWeight: '800'
    },

    // Tab Consolidado
    consolidatedView: {
        gap: 12
    },
    tableTitle: { 
        fontSize: 13, 
        fontWeight: '800', 
        color: '#0f172a', 
        borderLeftWidth: 3, 
        borderLeftColor: '#0284c7', 
        paddingLeft: 8,
        marginBottom: 8
    },
    statsCard: { 
        padding: 12, 
        borderRadius: 8, 
        backgroundColor: '#f8fafc', 
        borderWidth: 1, 
        borderColor: '#e2e8f0' 
    },
    statRow: { 
        flexDirection: 'row', 
        justifyContent: 'space-between', 
        paddingVertical: 5, 
        borderBottomWidth: 0.5, 
        borderBottomColor: '#e2e8f0' 
    },
    statLabel: { 
        fontSize: 11, 
        fontWeight: '700', 
        color: '#64748b' 
    },
    statValue: { 
        fontSize: 11, 
        fontWeight: '800', 
        color: '#0f172a' 
    },

    tableHeaderWide: { 
        flexDirection: 'row', 
        backgroundColor: '#f1f5f9', 
        paddingVertical: 8, 
        paddingHorizontal: 6, 
        borderTopWidth: 1, 
        borderTopColor: '#e2e8f0', 
        borderBottomWidth: 1, 
        borderBottomColor: '#cbd5e1' 
    },
    tableRowWide: { 
        flexDirection: 'row', 
        paddingVertical: 8, 
        paddingHorizontal: 6, 
        borderBottomWidth: 1, 
        borderBottomColor: '#f1f5f9',
        alignItems: 'center'
    },
    tableFooterWide: {
        backgroundColor: '#f8fafc',
        borderTopWidth: 1.5,
        borderTopColor: '#334155'
    },
    cellTextWide: { 
        fontSize: 11, 
        color: '#334155' 
    },

    // Barra de acción inferior
    modalFooterBar: {
        padding: 12,
        borderTopWidth: 1,
        borderTopColor: '#e2e8f0',
        backgroundColor: '#ffffff'
    },
    applyPaymentBtn: {
        backgroundColor: '#10b981',
        height: 46,
        borderRadius: 10,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        gap: 8,
        shadowColor: '#10b981',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.2,
        shadowRadius: 3,
        elevation: 2
    },
    applyPaymentBtnText: {
        color: '#ffffff',
        fontSize: 14,
        fontWeight: '800'
    }
});
