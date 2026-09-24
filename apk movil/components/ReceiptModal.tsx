import React from 'react';
import { Modal, View, Text, StyleSheet, TouchableOpacity, ScrollView, ActivityIndicator, Image, Platform } from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { thermalPrinterService } from '../services/thermal-printer';
import { AlertHelper } from '../utils/custom-alert-helper';

interface ReceiptModalProps {
    visible: boolean;
    onClose: () => void;
    receipt: ReceiptData | null;
}

export interface ReceiptData {
    transactionNumber: string;
    creditNumber: string;
    clientName: string;
    clientCode: string;
    paymentDate: string;
    cuotaDelDia: number;
    montoAtrasado: number;
    diasMora: number;
    totalAPagar: number;
    montoCancelacion: number;
    amountPaid: number;
    saldoAnterior: number;
    nuevoSaldo: number;
    managedBy: string;
    sucursal: string;
    role: string;
    concepto?: string;
    is_cancelacion?: boolean;
    tipo_abono?: number;
    is_reimpresion?: boolean;
}

const fmt = (n: number) => {
    const val = Number(n || 0).toFixed(2);
    return val.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};

export default function ReceiptModal({ visible, onClose, receipt }: ReceiptModalProps) {
    const [printing, setPrinting] = React.useState(false);

    if (!receipt) return null;

    const isCancel = receipt.is_cancelacion || 
        (receipt.concepto && receipt.concepto.toUpperCase().includes('CANCEL')) ||
        (receipt.nuevoSaldo === 0 && (receipt.saldoAnterior || 0) > 0);

    const handlePrint = async () => {
        setPrinting(true);
        try {
            const savedPrinter = await AsyncStorage.getItem('selectedPrinter') || 'Default';
            const savedTarget = await AsyncStorage.getItem('selectedPrinterTarget');
            
            await thermalPrinterService.printReceipt(savedTarget || savedPrinter, receipt);
            AlertHelper.alert('Éxito', 'Recibo impreso correctamente');
        } catch (e: any) {
            console.error('[PRINT] Error al imprimir:', e);
            AlertHelper.alert('Error de Impresión', e.message || 'No se pudo conectar con la impresora. Asegúrate de que el Bluetooth esté encendido y la impresora vinculada.');
        } finally {
            setPrinting(false);
        }
    };

    const todayDate = new Date().toLocaleDateString('es-NI', { day: '2-digit', month: '2-digit', year: 'numeric' });

    return (
        <Modal visible={visible} animationType="fade" transparent onRequestClose={onClose}>
            <View style={styles.overlay}>
                <View style={styles.container}>
                    {/* Header Modal */}
                    <View style={styles.header}>
                        <View style={styles.headerTitleRow}>
                            <MaterialCommunityIcons name="receipt" size={20} color="#0ea5e9" />
                            <Text style={styles.headerTitle}>Recibo de Pago</Text>
                        </View>
                        <TouchableOpacity onPress={onClose} style={styles.closeBtn} hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}>
                            <MaterialCommunityIcons name="close" size={24} color="#64748b" />
                        </TouchableOpacity>
                    </View>

                    <ScrollView showsVerticalScrollIndicator={true} contentContainerStyle={styles.scrollContent}>
                        {/* Ticket con borde idéntico a la versión web */}
                        <View style={styles.ticketCard}>
                            {/* Logo de CrediNica centrado antes de CREDINICA */}
                            <Image 
                                source={require('../assets/images/credinica.png')} 
                                style={styles.logoImage} 
                                resizeMode="contain" 
                            />
                            
                            <Text style={styles.brand}>CREDINICA</Text>
                            <Text style={styles.subtitle}>ESTADO DE CUENTA / RECIBO</Text>
                            <Text style={styles.printDateText}>Fecha Impresión: {todayDate}</Text>

                            <View style={styles.divider} />

                            <Row label="No. Recibo:" value={receipt.transactionNumber} bold />
                            <Row label="No. Crédito:" value={receipt.creditNumber} />
                            <Row label="Fecha Pago:" value={receipt.paymentDate} />

                            <View style={styles.divider} />

                            <Text style={styles.clientLabel}>CLIENTE:</Text>
                            <Text style={styles.clientName}>{receipt.clientName.toUpperCase()}</Text>
                            {receipt.clientCode ? (
                                <Text style={styles.clientCode}>CÓDIGO: {receipt.clientCode}</Text>
                            ) : null}

                            <View style={styles.divider} />

                            <Row label="Cuota del Día:" value={`C$ ${fmt(receipt.cuotaDelDia)}`} />
                            <Row label="Mora / Atraso:" value={`C$ ${fmt(receipt.montoAtrasado)}`} />
                            <Row label="Días Mora:" value={receipt.diasMora.toString()} />
                            
                            <View style={styles.subDividerDotted} />
                            
                            <Row label="Total a pagar:" value={`C$ ${fmt(receipt.totalAPagar)}`} bold />

                            <View style={styles.divider} />

                            {/* Total Cobrado / Monto Recibido */}
                            <View style={styles.totalBox}>
                                <Text style={styles.totalLabel}>MONTO RECIBIDO</Text>
                                <Text style={styles.totalAmount}>C$ {fmt(receipt.amountPaid)}</Text>
                            </View>

                            <View style={[styles.conceptBadge, isCancel && styles.conceptBadgeCancelacion]}>
                                <Text style={[styles.concept, isCancel && styles.conceptCancelacion]}>
                                    {isCancel ? 'CONCEPTO: CANCELACIÓN DE CRÉDITO' : (receipt.concepto ? `CONCEPTO: ${receipt.concepto.toUpperCase()}` : 'CONCEPTO: ABONO DE CRÉDITO')}
                                </Text>
                            </View>

                            <View style={styles.balanceBox}>
                                <Row label="Saldo Anterior:" value={`C$ ${fmt(receipt.saldoAnterior)}`} />
                                <Row label="Nuevo Saldo:" value={`C$ ${fmt(receipt.nuevoSaldo)}`} bold />
                            </View>

                            {/* Recuadro verde si el crédito fue cancelado (igual a la web) */}
                            {isCancel && (
                                <View style={styles.cancelledBox}>
                                    <MaterialCommunityIcons name="check-circle" size={18} color="#15803d" />
                                    <Text style={styles.cancelledText}>✓ CRÉDITO CANCELADO</Text>
                                </View>
                            )}

                            <View style={styles.divider} />

                            <View style={styles.footerCenter}>
                                <Text style={styles.thanks}>¡GRACIAS POR SU PAGO!</Text>
                                <Text style={styles.keepReceipt}>CONSERVE ESTE DOCUMENTO</Text>
                            </View>

                            {receipt.managedBy ? (
                                <View style={styles.agentRow}>
                                    <Text style={styles.agentLabel}>Agente: </Text>
                                    <Text style={styles.agentValue}>{receipt.managedBy.toUpperCase()}</Text>
                                </View>
                            ) : null}

                            {receipt.sucursal ? (
                                <View style={styles.agentRow}>
                                    <Text style={styles.agentLabel}>Sucursal: </Text>
                                    <Text style={styles.agentValue}>{receipt.sucursal.toUpperCase()}</Text>
                                </View>
                            ) : null}

                            {receipt.is_reimpresion && (
                                <View style={styles.reprintBadge}>
                                    <Text style={styles.reprintText}>Reimpresión</Text>
                                    <Text style={styles.reprintSubtext}>{new Date().toLocaleString('es-NI')}</Text>
                                </View>
                            )}
                        </View>
                    </ScrollView>

                    {/* Botón de impresión con safe-area inferior para que nunca se oculte tras los botones del sistema */}
                    <View style={styles.footerContainer}>
                        <TouchableOpacity style={styles.printButton} onPress={handlePrint} disabled={printing} activeOpacity={0.8}>
                            {printing ? (
                                <ActivityIndicator color="#fff" size="small" />
                            ) : (
                                <>
                                    <MaterialCommunityIcons name="printer" size={22} color="#fff" />
                                    <Text style={styles.printText}>IMPRIMIR RECIBO</Text>
                                </>
                            )}
                        </TouchableOpacity>
                    </View>
                </View>
            </View>
        </Modal>
    );
}

function Row({ label, value, bold = false }: { label: string; value: string; bold?: boolean }) {
    return (
        <View style={styles.row}>
            <Text style={[styles.rowLabel, bold && styles.bold]}>{label}</Text>
            <Text style={[styles.rowValue, bold && styles.bold]}>{value}</Text>
        </View>
    );
}

const styles = StyleSheet.create({
    overlay: { 
        flex: 1, 
        backgroundColor: 'rgba(0,0,0,0.65)', 
        justifyContent: 'center', 
        alignItems: 'center', 
        padding: 16 
    },
    container: { 
        backgroundColor: '#fff', 
        borderRadius: 16, 
        width: '100%', 
        maxHeight: '92%', 
        overflow: 'hidden',
        display: 'flex',
        flexDirection: 'column'
    },
    header: { 
        flexDirection: 'row', 
        justifyContent: 'space-between', 
        alignItems: 'center', 
        paddingHorizontal: 16,
        paddingVertical: 14, 
        borderBottomWidth: 1, 
        borderBottomColor: '#f1f5f9' 
    },
    headerTitleRow: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 8,
    },
    headerTitle: { 
        fontSize: 16, 
        fontWeight: '800', 
        color: '#1e293b' 
    },
    closeBtn: {
        padding: 4,
    },
    scrollContent: {
        paddingVertical: 10,
    },
    // Ticket con borde exacto como en la web: border: solid 1px black
    ticketCard: { 
        marginHorizontal: 16,
        marginVertical: 6,
        padding: 14,
        borderWidth: 1.5,
        borderColor: '#000000',
        borderRadius: 6,
        backgroundColor: '#ffffff',
    },
    logoImage: {
        width: 140,
        height: 52,
        alignSelf: 'center',
        marginBottom: 6,
    },
    brand: { 
        fontSize: 20, 
        fontWeight: '900', 
        textAlign: 'center', 
        letterSpacing: 2, 
        color: '#0f172a' 
    },
    subtitle: { 
        fontSize: 11, 
        textAlign: 'center', 
        color: '#64748b', 
        fontWeight: '700', 
        marginBottom: 4 
    },
    printDateText: {
        fontSize: 12,
        textAlign: 'center',
        color: '#475569',
        fontWeight: '600',
        marginBottom: 6,
    },
    divider: { 
        borderTopWidth: 1, 
        borderTopColor: '#000000', 
        borderStyle: 'dashed', 
        marginVertical: 10 
    },
    subDividerDotted: { 
        borderTopWidth: 1, 
        borderTopColor: '#64748b', 
        borderStyle: 'dotted', 
        marginVertical: 8 
    },
    row: { 
        flexDirection: 'row', 
        justifyContent: 'space-between', 
        marginBottom: 4 
    },
    rowLabel: { 
        fontSize: 13, 
        color: '#334155' 
    },
    rowValue: { 
        fontSize: 13, 
        color: '#0f172a',
        fontWeight: '500' 
    },
    bold: { 
        fontWeight: '800', 
        color: '#0f172a' 
    },
    clientLabel: { 
        fontSize: 12, 
        color: '#64748b', 
        textAlign: 'center',
        fontWeight: '600',
        marginBottom: 2 
    },
    clientName: { 
        fontSize: 15, 
        fontWeight: '900', 
        color: '#0f172a',
        textAlign: 'center',
        letterSpacing: 0.3,
    },
    clientCode: { 
        fontSize: 12, 
        color: '#475569', 
        textAlign: 'center',
        marginTop: 2,
    },
    totalBox: { 
        borderWidth: 2, 
        borderColor: '#000000', 
        paddingVertical: 10, 
        paddingHorizontal: 8,
        marginVertical: 10, 
        alignItems: 'center', 
        borderRadius: 4 
    },
    totalLabel: { 
        fontSize: 12, 
        fontWeight: '800', 
        color: '#334155', 
        marginBottom: 4 
    },
    totalAmount: { 
        fontSize: 26, 
        fontWeight: '900', 
        color: '#0f172a' 
    },
    conceptBadge: { 
        backgroundColor: '#f1f5f9', 
        paddingVertical: 5, 
        paddingHorizontal: 10, 
        borderRadius: 6, 
        marginBottom: 8, 
        alignSelf: 'center' 
    },
    conceptBadgeCancelacion: { 
        backgroundColor: '#dcfce7', 
        borderWidth: 1, 
        borderColor: '#86efac' 
    },
    concept: { 
        textAlign: 'center', 
        fontStyle: 'italic', 
        fontSize: 11, 
        color: '#475569', 
        fontWeight: '700' 
    },
    conceptCancelacion: { 
        color: '#15803d', 
        fontWeight: '900', 
        fontStyle: 'normal' 
    },
    balanceBox: { 
        backgroundColor: '#f8fafc', 
        padding: 10, 
        borderRadius: 6, 
        marginBottom: 8 
    },
    cancelledBox: {
        backgroundColor: '#dcfce7',
        borderWidth: 1,
        borderColor: '#86efac',
        borderRadius: 6,
        padding: 10,
        marginVertical: 8,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        gap: 6,
    },
    cancelledText: {
        fontSize: 14,
        fontWeight: '900',
        color: '#15803d',
    },
    footerCenter: { 
        alignItems: 'center', 
        marginVertical: 6 
    },
    thanks: { 
        textAlign: 'center', 
        fontSize: 14, 
        fontWeight: '900',
        color: '#0f172a',
    },
    keepReceipt: { 
        textAlign: 'center', 
        fontWeight: '700', 
        fontSize: 11, 
        color: '#64748b',
        marginTop: 2 
    },
    agentRow: {
        flexDirection: 'row',
        justifyContent: 'center',
        marginTop: 6,
    },
    agentLabel: {
        fontSize: 12,
        fontWeight: '700',
        color: '#475569',
    },
    agentValue: {
        fontSize: 12,
        fontWeight: '800',
        color: '#0f172a',
    },
    reprintBadge: {
        borderTopWidth: 1,
        borderTopColor: '#e2e8f0',
        marginTop: 10,
        paddingTop: 6,
        alignItems: 'center',
    },
    reprintText: {
        fontSize: 12,
        fontWeight: '700',
        color: '#64748b',
    },
    reprintSubtext: {
        fontSize: 10,
        color: '#94a3b8',
    },
    // Contenedor del botón inferior con padding para barra de navegación del móvil
    footerContainer: {
        paddingHorizontal: 16,
        paddingTop: 10,
        paddingBottom: Platform.OS === 'android' ? 24 : 16,
        borderTopWidth: 1,
        borderTopColor: '#f1f5f9',
        backgroundColor: '#ffffff',
    },
    printButton: { 
        flexDirection: 'row', 
        backgroundColor: '#0ea5e9', 
        paddingVertical: 14, 
        borderRadius: 12, 
        alignItems: 'center', 
        justifyContent: 'center', 
        gap: 8,
        shadowColor: '#0ea5e9',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.3,
        shadowRadius: 4,
        elevation: 3,
    },
    printText: { 
        color: '#fff', 
        fontWeight: '800', 
        fontSize: 15,
        letterSpacing: 0.5,
    },
});
