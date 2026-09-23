import React from 'react';
import { Modal, View, Text, StyleSheet, TouchableOpacity, ScrollView, ActivityIndicator, Platform } from 'react-native';
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
}

const fmt = (n: number) => n.toLocaleString('es-NI', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

export default function ReceiptModal({ visible, onClose, receipt }: ReceiptModalProps) {
    const [printing, setPrinting] = React.useState(false);

    if (!receipt) return null;

    const handlePrint = async () => {
        setPrinting(true);
        try {
            const savedPrinter = await AsyncStorage.getItem('selectedPrinter') || 'Default';
            const savedTarget = await AsyncStorage.getItem('selectedPrinterTarget');
            
            // Usamos la dirección física (MAC) para conectar directamente
            await thermalPrinterService.printReceipt(savedTarget || savedPrinter, receipt);
        } catch (e) {
            AlertHelper.alert('Error de Impresión', 'No se pudo conectar con la impresora. Asegúrate de que el Bluetooth esté encendido y la impresora vinculada.');
        } finally {
            setPrinting(false);
        }
    };

    return (
        <Modal visible={visible} animationType="fade" transparent onRequestClose={onClose}>
            <View style={styles.overlay}>
                <View style={styles.container}>
                    {/* Header */}
                    <View style={styles.header}>
                        <Text style={styles.headerTitle}>Recibo de Pago</Text>
                        <TouchableOpacity onPress={onClose}>
                            <MaterialCommunityIcons name="close" size={24} color="#64748b" />
                        </TouchableOpacity>
                    </View>

                    <ScrollView showsVerticalScrollIndicator={false}>
                        {/* Receipt Body */}
                        <View style={styles.receipt}>
                            <Text style={styles.brand}>CREDINICA</Text>
                            <Text style={styles.subtitle}>RECIBO</Text>
                            <Text style={styles.copy}>COPIA: CLIENTE</Text>

                            <View style={styles.divider} />

                            <Row label="No. Recibo:" value={receipt.transactionNumber} />
                            <Row label="No. Crédito:" value={receipt.creditNumber} />
                            <Row label="Fecha Pago:" value={receipt.paymentDate} />

                            <View style={styles.divider} />

                            <Text style={styles.clientLabel}>CLIENTE:</Text>
                            <Text style={styles.clientName}>{receipt.clientName.toUpperCase()}</Text>
                            <Text style={styles.clientCode}>CÓDIGO: {receipt.clientCode}</Text>

                            <View style={styles.divider} />

                            <Row label="Cuota del Día:" value={`C$ ${fmt(receipt.cuotaDelDia)}`} />
                            <Row label="Mora / Atraso:" value={`C$ ${fmt(receipt.montoAtrasado)}`} />
                            <Row label="Días Mora:" value={receipt.diasMora.toString()} />
                            <View style={styles.subDivider} />
                            <Row label="Total a pagar:" value={`C$ ${fmt(receipt.totalAPagar)}`} bold />

                            <View style={styles.divider} />

                            <Row label="Cancelación Total:" value={`C$ ${fmt(receipt.montoCancelacion)}`} />

                            {/* Total cobrado */}
                            <View style={styles.totalBox}>
                                <Text style={styles.totalLabel}>MONTO RECIBIDO</Text>
                                <Text style={styles.totalAmount}>C$ {fmt(receipt.amountPaid)}</Text>
                            </View>

                            <View style={[styles.conceptBadge, (receipt.is_cancelacion || (receipt.concepto && receipt.concepto.includes('CANCELACIÓN'))) && styles.conceptBadgeCancelacion]}>
                                <Text style={[styles.concept, (receipt.is_cancelacion || (receipt.concepto && receipt.concepto.includes('CANCELACIÓN'))) && styles.conceptCancelacion]}>
                                    CONCEPTO: {receipt.concepto || (receipt.is_cancelacion ? 'CANCELACIÓN DE CRÉDITO' : 'ABONO DE CRÉDITO')}
                                </Text>
                            </View>

                            <View style={styles.balanceBox}>
                                <Row label="Saldo Anterior:" value={`C$ ${fmt(receipt.saldoAnterior)}`} />
                                <Row label="Nuevo Saldo:" value={`C$ ${fmt(receipt.nuevoSaldo)}`} bold />
                            </View>

                            <View style={styles.divider} />

                            <View style={styles.footerCenter}>
                                <Text style={styles.thanks}>¡GRACIAS POR SU PAGO!</Text>
                                <Text style={styles.legalNotice}>PROHIBIDO EL PAGO SIN RECIBO</Text>
                                <Text style={styles.keepReceipt}>CONSERVE ESTE DOCUMENTO</Text>
                            </View>

                            <View style={styles.signatureArea}>
                                <View style={styles.signatureLine} />
                                <Text style={styles.sucursal}>{receipt.sucursal.toUpperCase()}</Text>
                                <Text style={styles.managedBy}>{receipt.managedBy.toUpperCase()}</Text>
                                <Text style={styles.role}>{receipt.role.toUpperCase()}</Text>
                            </View>
                        </View>
                    </ScrollView>

                    {/* Print Button */}
                    <TouchableOpacity style={styles.printButton} onPress={handlePrint} disabled={printing}>
                        {printing ? (
                            <ActivityIndicator color="#fff" size="small" />
                        ) : (
                            <>
                                <MaterialCommunityIcons name="printer" size={20} color="#fff" />
                                <Text style={styles.printText}>IMPRIMIR RECIBO</Text>
                            </>
                        )}
                    </TouchableOpacity>
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
    overlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.6)', justifyContent: 'center', alignItems: 'center', padding: 16 },
    container: { backgroundColor: '#fff', borderRadius: 16, width: '100%', maxHeight: '90%', overflow: 'hidden' },
    header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: 16, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    headerTitle: { fontSize: 16, fontWeight: '700', color: '#1e293b' },
    receipt: { padding: 20 },
    brand: { fontSize: 22, fontWeight: '900', textAlign: 'center', letterSpacing: 2, color: '#1e293b' },
    subtitle: { fontSize: 11, textAlign: 'center', color: '#64748b', fontWeight: '700', marginBottom: 2 },
    copy: { fontSize: 11, textAlign: 'center', color: '#64748b', marginBottom: 4 },
    divider: { borderTopWidth: 1, borderTopColor: '#000', borderStyle: 'dashed', marginVertical: 10 },
    subDivider: { borderTopWidth: 0.5, borderTopColor: '#e2e8f0', marginVertical: 6 },
    row: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 4 },
    rowLabel: { fontSize: 13, color: '#475569' },
    rowValue: { fontSize: 13, color: '#1e293b' },
    bold: { fontWeight: '800', color: '#1e293b' },
    clientLabel: { fontSize: 11, color: '#64748b', marginBottom: 2 },
    clientName: { fontSize: 15, fontWeight: '800', color: '#1e293b' },
    clientCode: { fontSize: 12, color: '#475569', marginBottom: 4 },
    totalBox: { borderWidth: 2, borderColor: '#000', padding: 12, marginVertical: 12, alignItems: 'center', borderRadius: 4 },
    totalLabel: { fontSize: 11, color: '#475569', marginBottom: 4 },
    totalAmount: { fontSize: 24, fontWeight: '900', color: '#1e293b' },
    conceptBadge: { backgroundColor: '#f1f5f9', paddingVertical: 4, paddingHorizontal: 8, borderRadius: 6, marginBottom: 8, alignSelf: 'center' },
    conceptBadgeCancelacion: { backgroundColor: '#ede9fe', borderWidth: 1, borderColor: '#c4b5fd' },
    concept: { textAlign: 'center', fontStyle: 'italic', fontSize: 11, color: '#64748b', fontWeight: '700' },
    conceptCancelacion: { color: '#6d28d9', fontWeight: '900', fontStyle: 'normal' },
    balanceBox: { backgroundColor: '#f8fafc', padding: 10, borderRadius: 8, marginBottom: 8 },
    footerCenter: { alignItems: 'center', marginVertical: 10 },
    thanks: { textAlign: 'center', fontSize: 13, fontWeight: '700' },
    legalNotice: { textAlign: 'center', fontSize: 11, color: '#ef4444', fontWeight: '800', marginTop: 4 },
    keepReceipt: { textAlign: 'center', fontWeight: '800', fontSize: 11, marginTop: 4 },
    signatureArea: { alignItems: 'center', marginTop: 30, paddingHorizontal: 20 },
    signatureLine: { borderTopWidth: 1, borderTopColor: '#000', width: '80%', marginBottom: 8 },
    sucursal: { fontWeight: '800', fontSize: 13 },
    managedBy: { fontWeight: '800', fontSize: 13, marginTop: 4 },
    role: { fontSize: 11, color: '#64748b' },
    printButton: { flexDirection: 'row', backgroundColor: '#0ea5e9', margin: 16, padding: 14, borderRadius: 12, alignItems: 'center', justifyContent: 'center', gap: 8 },
    printText: { color: '#fff', fontWeight: '800', fontSize: 14 },
});

