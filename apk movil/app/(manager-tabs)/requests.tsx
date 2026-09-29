import {
    View, Text, StyleSheet, SafeAreaView, ScrollView, TouchableOpacity,
    RefreshControl, ActivityIndicator, StatusBar, Platform, Modal,
    TextInput, KeyboardAvoidingView,
} from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useState, useCallback, useEffect } from 'react';
import { useFocusEffect } from 'expo-router';
import { sessionService } from '../../services/session';
import { API_ENDPOINTS } from '../../config/api';
import { apiFetch } from '../../config/apiFetch';
import ReasonModal from '../../components/ReasonModal';
import { AlertHelper } from '../../utils/custom-alert-helper';

const fmt = (n: any) =>
    (Number(n) || 0).toLocaleString('es-NI', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const formatDate = (v: any) => {
    if (!v) return 'N/A';
    try {
        const m = String(v).match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (m) return `${m[3]}/${m[2]}/${m[1]}`;
        const d = new Date(v);
        if (!isNaN(d.getTime()))
            return d.toLocaleDateString('es-NI', { day: '2-digit', month: '2-digit', year: 'numeric' });
    } catch (_) { /* */ }
    return 'N/A';
};

// ─── Modal de Edición ────────────────────────────────────────────────────────
interface EditModalProps {
    visible: boolean;
    request: any | null;
    onClose: () => void;
    onSaved: (updated: any) => void;
}

const FRECUENCIAS: Record<string, string> = {
    '1': 'Diario', '2': 'Semanal', '3': 'Quincenal',
    '4': 'Mensual', '5': 'Trimestral', '6': 'Bimestral', '7': 'Catorcenal',
};

function EditRequestModal({ visible, request, onClose, onSaved }: EditModalProps) {
    const [monto, setMonto]         = useState('');
    const [plazo, setPlazo]         = useState('');
    const [tasa, setTasa]           = useState('');
    const [fechaPago, setFechaPago] = useState('');
    const [saving, setSaving]       = useState(false);

    // Poblar los campos cada vez que se abre el modal con una solicitud distinta
    useEffect(() => {
        if (visible && request) {
            setMonto(String(request.amount || ''));
            setPlazo(String(request.termMonths || ''));
            setTasa(String(request.interestRate || ''));
            setFechaPago(
                request.firstPaymentDate
                    ? String(request.firstPaymentDate).substring(0, 10)
                    : ''
            );
        }
    }, [visible, request]);

    if (!request) return null;

    // Recalcular preview en tiempo real
    const montoN   = parseFloat(monto) || 0;
    const plazoN   = parseInt(plazo)   || 0;
    const tasaN    = parseFloat(tasa)  || 0;
    const interesTotal   = Math.round(montoN * (tasaN / 100) * plazoN * 100) / 100;
    const montoFinanciado = Math.round((montoN + interesTotal) * 100) / 100;
    const montoCuota      = plazoN > 0 ? Math.round((montoFinanciado / plazoN) * 100) / 100 : 0;

    const handleSave = async () => {
        if (!monto || !plazo || !tasa || !fechaPago) {
            AlertHelper.alert('Campos requeridos', 'Completa todos los campos antes de guardar.');
            return;
        }
        if (montoN <= 0 || plazoN <= 0 || tasaN < 0) {
            AlertHelper.alert('Valores inválidos', 'Monto y plazo deben ser mayores a 0.');
            return;
        }
        // Validar formato fecha YYYY-MM-DD
        if (!/^\d{4}-\d{2}-\d{2}$/.test(fechaPago)) {
            AlertHelper.alert('Fecha inválida', 'La fecha debe tener el formato AAAA-MM-DD (ej: 2026-10-15).');
            return;
        }
        setSaving(true);
        try {
            const session = await sessionService.getSession();
            const resp = await apiFetch((API_ENDPOINTS as any).update_request, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    creditId:        request.id,
                    userId:          session?.id,
                    monto:           montoN,
                    plazo:           plazoN,
                    tasa:            tasaN,
                    fechaPrimerPago: fechaPago,
                }),
            });
            const result = await resp.json();
            if (result.success) {
                AlertHelper.alert('Guardado', 'Solicitud actualizada correctamente.');
                onSaved(result.data);
                onClose();
            } else {
                AlertHelper.alert('Error', result.message || 'No se pudo actualizar.');
            }
        } catch (e: any) {
            AlertHelper.alert('Error', e.message || 'No se pudo conectar con el servidor.');
        } finally {
            setSaving(false);
        }
    };

    return (
        <Modal visible={visible} animationType="slide" transparent onRequestClose={onClose}>
            <KeyboardAvoidingView
                style={styles.editOverlay}
                behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
            >
                <View style={styles.editContainer}>
                    {/* Header */}
                    <View style={styles.editHeader}>
                        <Text style={styles.editTitle}>Editar Solicitud</Text>
                        <TouchableOpacity onPress={onClose} hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}>
                            <MaterialCommunityIcons name="close" size={24} color="#64748b" />
                        </TouchableOpacity>
                    </View>
                    <Text style={styles.editClientName}>{request.clientName}</Text>

                    <ScrollView showsVerticalScrollIndicator={false} style={{ flex: 1 }}>
                        {/* Monto */}
                        <Text style={styles.editLabel}>Monto Principal (C$)</Text>
                        <TextInput
                            style={styles.editInput}
                            value={monto}
                            onChangeText={setMonto}
                            keyboardType="decimal-pad"
                            placeholder="Ej: 5000"
                            placeholderTextColor="#94a3b8"
                        />

                        {/* Plazo */}
                        <Text style={styles.editLabel}>Plazo (meses)</Text>
                        <TextInput
                            style={styles.editInput}
                            value={plazo}
                            onChangeText={setPlazo}
                            keyboardType="number-pad"
                            placeholder="Ej: 12"
                            placeholderTextColor="#94a3b8"
                        />

                        {/* Tasa */}
                        <Text style={styles.editLabel}>Tasa de Interés Mensual (%)</Text>
                        <TextInput
                            style={styles.editInput}
                            value={tasa}
                            onChangeText={setTasa}
                            keyboardType="decimal-pad"
                            placeholder="Ej: 5"
                            placeholderTextColor="#94a3b8"
                        />

                        {/* Fecha primer pago */}
                        <Text style={styles.editLabel}>Fecha Primer Pago (AAAA-MM-DD)</Text>
                        <TextInput
                            style={styles.editInput}
                            value={fechaPago}
                            onChangeText={setFechaPago}
                            keyboardType="default"
                            placeholder="Ej: 2026-10-15"
                            placeholderTextColor="#94a3b8"
                            maxLength={10}
                        />

                        {/* Preview calculado */}
                        {montoN > 0 && plazoN > 0 && (
                            <View style={styles.previewBox}>
                                <Text style={styles.previewTitle}>Vista previa del cálculo</Text>
                                <View style={styles.previewRow}>
                                    <Text style={styles.previewLabel}>Interés Total:</Text>
                                    <Text style={styles.previewValue}>C$ {fmt(interesTotal)}</Text>
                                </View>
                                <View style={styles.previewRow}>
                                    <Text style={styles.previewLabel}>Monto Total (C+I):</Text>
                                    <Text style={[styles.previewValue, { color: '#0ea5e9' }]}>C$ {fmt(montoFinanciado)}</Text>
                                </View>
                                <View style={styles.previewRow}>
                                    <Text style={styles.previewLabel}>Cuota Periódica:</Text>
                                    <Text style={[styles.previewValue, { color: '#10b981', fontWeight: '800' }]}>C$ {fmt(montoCuota)}</Text>
                                </View>
                            </View>
                        )}
                    </ScrollView>

                    {/* Botón guardar */}
                    <TouchableOpacity
                        style={[styles.saveButton, saving && { opacity: 0.6 }]}
                        onPress={handleSave}
                        disabled={saving}
                    >
                        {saving
                            ? <ActivityIndicator color="#fff" size="small" />
                            : <><MaterialCommunityIcons name="content-save" size={20} color="#fff" /><Text style={styles.saveButtonText}>GUARDAR CAMBIOS</Text></>
                        }
                    </TouchableOpacity>
                </View>
            </KeyboardAvoidingView>
        </Modal>
    );
}

// ─── Pantalla principal ───────────────────────────────────────────────────────
export default function RequestsScreen() {
    const [isLoading, setIsLoading]           = useState(true);
    const [refreshing, setRefreshing]         = useState(false);
    const [requests, setRequests]             = useState<any[]>([]);
    const [activeTab, setActiveTab]           = useState<'pending' | 'rejected'>('pending');
    const [showReasonModal, setShowReasonModal] = useState(false);
    const [requestToReject, setRequestToReject] = useState<{ id: string; name: string } | null>(null);
    const [editingRequest, setEditingRequest] = useState<any | null>(null);

    useFocusEffect(useCallback(() => { fetchRequests(); }, []));

    const fetchRequests = async () => {
        try {
            const session = await sessionService.getSession();
            if (!session?.id) return;
            const resp   = await apiFetch(`${API_ENDPOINTS.mobile_requests}?userId=${session.id}`);
            const result = await resp.json();
            if (result.success) setRequests(result.requests || []);
        } catch (error) {
            console.error('Error fetching requests:', error);
        } finally {
            setIsLoading(false);
            setRefreshing(false);
        }
    };

    const onRefresh = () => { setRefreshing(true); fetchRequests(); };

    const pendingRequests  = requests.filter(r => r.status === 'Pending');
    const rejectedRequests = requests.filter(r => r.status === 'Rejected');
    const currentList      = activeTab === 'pending' ? pendingRequests : rejectedRequests;

    const handleApprove = async (requestId: string) => {
        AlertHelper.alert('Aprobar Solicitud', '¿Estás seguro de aprobar esta solicitud de crédito?', [
            { text: 'Cancelar', style: 'cancel' },
            {
                text: 'Aprobar',
                onPress: async () => {
                    try {
                        const session = await sessionService.getSession();
                        const resp = await apiFetch(API_ENDPOINTS.approve_credit, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ creditId: requestId, userId: session?.id }),
                        });
                        const result = await resp.json();
                        if (result.success) {
                            AlertHelper.alert('Éxito', 'Solicitud aprobada correctamente');
                            fetchRequests();
                        } else {
                            AlertHelper.alert('Error', result.message || 'No se pudo aprobar la solicitud');
                        }
                    } catch (error) {
                        AlertHelper.alert('Error', 'No se pudo conectar con el servidor');
                    }
                },
            },
        ]);
    };

    const handleReject = (requestId: string, clientName: string) => {
        setRequestToReject({ id: requestId, name: clientName });
        setShowReasonModal(true);
    };

    const handleRejectConfirm = async (reason: string) => {
        setShowReasonModal(false);
        if (!requestToReject) return;
        try {
            const session = await sessionService.getSession();
            const resp = await apiFetch(API_ENDPOINTS.reject_credit, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ creditId: requestToReject.id, userId: session?.id, reason }),
            });
            const result = await resp.json();
            if (result.success) {
                AlertHelper.alert('Éxito', 'Solicitud rechazada correctamente');
                fetchRequests();
            } else {
                AlertHelper.alert('Error', result.message || 'No se pudo rechazar la solicitud');
            }
        } catch (error) {
            AlertHelper.alert('Error', 'No se pudo conectar con el servidor');
        } finally {
            setRequestToReject(null);
        }
    };

    // Actualizar la solicitud en el estado local sin re-fetch
    const handleSaved = (updated: any) => {
        setRequests(prev => prev.map(r =>
            String(r.id) === String(updated.id)
                ? {
                    ...r,
                    amount:           updated.amount,
                    termMonths:       updated.termMonths,
                    interestRate:     updated.interestRate,
                    totalAmount:      updated.totalAmount,
                    installmentAmount: updated.installmentAmount,
                    firstPaymentDate: updated.firstPaymentDate,
                  }
                : r
        ));
    };

    if (isLoading) {
        return (
            <SafeAreaView style={styles.container}>
                <StatusBar barStyle="dark-content" backgroundColor="#ffffff" translucent={false} />
                <View style={styles.loadingContainer}>
                    <ActivityIndicator size="large" color="#0ea5e9" />
                </View>
            </SafeAreaView>
        );
    }

    return (
        <SafeAreaView style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor="#ffffff" translucent={false} />

            <View style={styles.header}>
                <Text style={styles.headerTitle}>Solicitudes</Text>
                <Text style={styles.headerSubtitle}>
                    {activeTab === 'pending'
                        ? `${pendingRequests.length} pendientes`
                        : `${rejectedRequests.length} rechazadas hoy`}
                </Text>
            </View>

            {/* Tabs */}
            <View style={styles.tabsContainer}>
                <TouchableOpacity
                    style={[styles.tab, activeTab === 'pending' && styles.tabActive]}
                    onPress={() => setActiveTab('pending')}
                >
                    <Text style={[styles.tabText, activeTab === 'pending' && styles.tabTextActive]}>
                        Pendientes ({pendingRequests.length})
                    </Text>
                </TouchableOpacity>
                <TouchableOpacity
                    style={[styles.tab, activeTab === 'rejected' && styles.tabActive]}
                    onPress={() => setActiveTab('rejected')}
                >
                    <Text style={[styles.tabText, activeTab === 'rejected' && styles.tabTextActive]}>
                        Rechazadas ({rejectedRequests.length})
                    </Text>
                </TouchableOpacity>
            </View>

            <ScrollView
                contentContainerStyle={styles.scrollContent}
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={['#0ea5e9']} />}
            >
                {currentList.length > 0 ? currentList.map((request) => (
                    <View key={request.id} style={styles.requestCard}>
                        {/* ── Encabezado ── */}
                        <View style={styles.requestHeader}>
                            <View style={styles.clientInfo}>
                                <MaterialCommunityIcons
                                    name="account-circle"
                                    size={40}
                                    color={request.status === 'Rejected' ? '#ef4444' : '#8b5cf6'}
                                />
                                <View style={styles.clientDetails}>
                                    <Text style={styles.clientName}>{request.clientName}</Text>
                                    <Text style={styles.clientCode}>#{request.creditNumber}</Text>
                                </View>
                            </View>
                            <View style={[styles.statusBadge, request.status === 'Rejected' && styles.statusBadgeRed]}>
                                <Text style={[styles.statusText, request.status === 'Rejected' && styles.statusTextRed]}>
                                    {request.status === 'Pending' ? 'PENDIENTE' : 'RECHAZADA'}
                                </Text>
                            </View>
                        </View>

                        {/* ── Detalles ── */}
                        <View style={styles.requestDetails}>
                            {/* Saldo pendiente — advertencia */}
                            {request.status === 'Pending' && (request.outstandingBalance ?? 0) > 0 && (
                                <View style={[styles.detailRow, styles.warningRow]}>
                                    <Text style={styles.detailLabelWarning}>Saldo Pendiente:</Text>
                                    <Text style={styles.detailValueWarning}>C$ {fmt(request.outstandingBalance)}</Text>
                                </View>
                            )}

                            {/* Monto principal */}
                            <View style={styles.detailRow}>
                                <Text style={styles.detailLabel}>Monto Solicitado:</Text>
                                <Text style={styles.detailValue}>C$ {fmt(request.amount)}</Text>
                            </View>

                            {/* Monto total (C+I) */}
                            {(request.totalAmount ?? 0) > 0 && (
                                <View style={styles.detailRow}>
                                    <Text style={styles.detailLabel}>Total a Pagar (C+I):</Text>
                                    <Text style={[styles.detailValue, { color: '#0ea5e9' }]}>C$ {fmt(request.totalAmount)}</Text>
                                </View>
                            )}

                            {/* Cuota */}
                            {(request.installmentAmount ?? 0) > 0 && (
                                <View style={styles.detailRow}>
                                    <Text style={styles.detailLabel}>Cuota:</Text>
                                    <Text style={[styles.detailValue, { color: '#10b981', fontWeight: '800' }]}>C$ {fmt(request.installmentAmount)}</Text>
                                </View>
                            )}

                            {/* Plazo */}
                            <View style={styles.detailRow}>
                                <Text style={styles.detailLabel}>Plazo:</Text>
                                <Text style={styles.detailValue}>{request.termMonths || 0} meses</Text>
                            </View>

                            {/* Tasa de interés */}
                            {(request.interestRate ?? 0) > 0 && (
                                <View style={styles.detailRow}>
                                    <Text style={styles.detailLabel}>Tasa Interés Mensual:</Text>
                                    <Text style={styles.detailValue}>{request.interestRate}%</Text>
                                </View>
                            )}

                            {/* Fecha primer pago */}
                            {request.firstPaymentDate && (
                                <View style={styles.detailRow}>
                                    <Text style={styles.detailLabel}>Fecha Primer Pago:</Text>
                                    <Text style={styles.detailValue}>{formatDate(request.firstPaymentDate)}</Text>
                                </View>
                            )}

                            {/* Gestor */}
                            <View style={styles.detailRow}>
                                <Text style={styles.detailLabel}>Gestor:</Text>
                                <Text style={styles.detailValue}>{request.collectionsManager || 'N/A'}</Text>
                            </View>

                            {/* Fecha solicitud */}
                            <View style={styles.detailRow}>
                                <Text style={styles.detailLabel}>Fecha Solicitud:</Text>
                                <Text style={styles.detailValue}>
                                    {request.applicationDate
                                        ? new Date(request.applicationDate).toLocaleDateString('es-NI')
                                        : 'N/A'}
                                </Text>
                            </View>

                            {/* Motivo rechazo */}
                            {request.status === 'Rejected' && (
                                <View style={styles.rejectionInfo}>
                                    <Text style={styles.rejectionLabel}>Motivo del Rechazo:</Text>
                                    <Text style={styles.rejectionText}>{request.rejectionReason || 'Sin motivo especificado'}</Text>
                                    <Text style={styles.rejectionFooter}>Rechazado por: {request.rejectedBy || 'N/A'}</Text>
                                </View>
                            )}
                        </View>

                        {/* ── Botones de acción (solo pendientes) ── */}
                        {request.status === 'Pending' && (
                            <View style={styles.actionButtons}>
                                <TouchableOpacity
                                    style={styles.editButton}
                                    onPress={() => setEditingRequest(request)}
                                >
                                    <MaterialCommunityIcons name="pencil" size={18} color="#fff" />
                                    <Text style={styles.buttonText}>Editar</Text>
                                </TouchableOpacity>
                                <TouchableOpacity
                                    style={styles.rejectButton}
                                    onPress={() => handleReject(request.id, request.clientName)}
                                >
                                    <MaterialCommunityIcons name="close-circle" size={18} color="#fff" />
                                    <Text style={styles.buttonText}>Rechazar</Text>
                                </TouchableOpacity>
                                <TouchableOpacity
                                    style={styles.approveButton}
                                    onPress={() => handleApprove(request.id)}
                                >
                                    <MaterialCommunityIcons name="check-circle" size={18} color="#fff" />
                                    <Text style={styles.buttonText}>Aprobar</Text>
                                </TouchableOpacity>
                            </View>
                        )}
                    </View>
                )) : (
                    <View style={styles.emptyContainer}>
                        <MaterialCommunityIcons
                            name={activeTab === 'pending' ? 'file-document-outline' : 'file-cancel-outline'}
                            size={64}
                            color="#cbd5e1"
                        />
                        <Text style={styles.emptyText}>
                            {activeTab === 'pending'
                                ? 'No hay solicitudes pendientes'
                                : 'No hay solicitudes rechazadas hoy'}
                        </Text>
                    </View>
                )}
            </ScrollView>

            {/* Modal rechazo */}
            <ReasonModal
                visible={showReasonModal}
                title="Rechazar Solicitud"
                message={`¿Por qué deseas rechazar la solicitud de ${requestToReject?.name}?`}
                onCancel={() => { setShowReasonModal(false); setRequestToReject(null); }}
                onConfirm={handleRejectConfirm}
            />

            {/* Modal edición */}
            <EditRequestModal
                visible={editingRequest !== null}
                request={editingRequest}
                onClose={() => setEditingRequest(null)}
                onSaved={handleSaved}
            />
        </SafeAreaView>
    );
}

// ─── Estilos ─────────────────────────────────────────────────────────────────
const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#ffffff',
        paddingTop: Platform.OS === 'android' ? StatusBar.currentHeight : 0,
    },
    loadingContainer: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    header: { padding: 20, paddingTop: 15, backgroundColor: '#ffffff' },
    headerTitle: { fontSize: 22, fontWeight: '800', color: '#334155' },
    headerSubtitle: { fontSize: 14, color: '#64748b', marginTop: 4 },
    tabsContainer: {
        flexDirection: 'row',
        backgroundColor: '#ffffff',
        borderBottomWidth: 1,
        borderBottomColor: '#e2e8f0',
    },
    tab: {
        flex: 1,
        paddingVertical: 12,
        alignItems: 'center',
        borderBottomWidth: 2,
        borderBottomColor: 'transparent',
    },
    tabActive: { borderBottomColor: '#8b5cf6' },
    tabText: { fontSize: 13, fontWeight: '600', color: '#64748b' },
    tabTextActive: { color: '#8b5cf6', fontWeight: '700' },
    scrollContent: { padding: 15 },
    requestCard: {
        backgroundColor: '#ffffff',
        borderRadius: 16,
        padding: 16,
        marginBottom: 15,
        borderWidth: 1,
        borderColor: '#e2e8f0',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 4,
        elevation: 2,
    },
    requestHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 15,
    },
    clientInfo: { flexDirection: 'row', alignItems: 'center', flex: 1 },
    clientDetails: { marginLeft: 12, flex: 1 },
    clientName: { fontSize: 15, fontWeight: '700', color: '#334155' },
    clientCode: { fontSize: 12, color: '#64748b', marginTop: 2 },
    statusBadge: { backgroundColor: '#ede9fe', paddingHorizontal: 10, paddingVertical: 5, borderRadius: 12 },
    statusBadgeRed: { backgroundColor: '#fee2e2' },
    statusText: { fontSize: 11, fontWeight: '700', color: '#7c3aed' },
    statusTextRed: { color: '#ef4444' },
    requestDetails: { backgroundColor: '#f8fafc', borderRadius: 12, padding: 12, marginBottom: 12 },
    detailRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 7 },
    detailLabel: { fontSize: 13, color: '#64748b' },
    detailValue: { fontSize: 13, fontWeight: '600', color: '#334155' },
    warningRow: {
        backgroundColor: '#fef3c7',
        marginHorizontal: -12,
        paddingHorizontal: 12,
        paddingVertical: 8,
        marginBottom: 8,
    },
    detailLabelWarning: { fontSize: 13, color: '#92400e', fontWeight: '600' },
    detailValueWarning: { fontSize: 14, fontWeight: '800', color: '#92400e' },
    rejectionInfo: {
        marginTop: 8,
        padding: 10,
        backgroundColor: '#fee2e2',
        borderRadius: 8,
        borderLeftWidth: 3,
        borderLeftColor: '#ef4444',
    },
    rejectionLabel: { fontSize: 12, fontWeight: '700', color: '#991b1b', marginBottom: 4 },
    rejectionText: { fontSize: 13, color: '#7f1d1d', lineHeight: 18 },
    rejectionFooter: { fontSize: 11, color: '#b91c1c', marginTop: 6, fontStyle: 'italic' },
    actionButtons: { flexDirection: 'row', gap: 8 },
    editButton: {
        flex: 1,
        flexDirection: 'row',
        backgroundColor: '#f97316',
        paddingVertical: 11,
        borderRadius: 12,
        alignItems: 'center',
        justifyContent: 'center',
        gap: 5,
    },
    rejectButton: {
        flex: 1,
        flexDirection: 'row',
        backgroundColor: '#ef4444',
        paddingVertical: 11,
        borderRadius: 12,
        alignItems: 'center',
        justifyContent: 'center',
        gap: 5,
    },
    approveButton: {
        flex: 1,
        flexDirection: 'row',
        backgroundColor: '#10b981',
        paddingVertical: 11,
        borderRadius: 12,
        alignItems: 'center',
        justifyContent: 'center',
        gap: 5,
    },
    buttonText: { color: '#ffffff', fontSize: 12, fontWeight: '700' },
    emptyContainer: { flex: 1, justifyContent: 'center', alignItems: 'center', paddingVertical: 60 },
    emptyText: { fontSize: 16, color: '#94a3b8', marginTop: 15 },

    // ── Modal de edición ──
    editOverlay: {
        flex: 1,
        backgroundColor: 'rgba(0,0,0,0.5)',
        justifyContent: 'flex-end',
    },
    editContainer: {
        backgroundColor: '#ffffff',
        borderTopLeftRadius: 24,
        borderTopRightRadius: 24,
        padding: 20,
        paddingBottom: Platform.OS === 'android' ? 32 : 24,
        maxHeight: '90%',
    },
    editHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 4,
    },
    editTitle: { fontSize: 18, fontWeight: '800', color: '#334155' },
    editClientName: { fontSize: 13, color: '#64748b', marginBottom: 20 },
    editLabel: { fontSize: 13, fontWeight: '600', color: '#475569', marginBottom: 6, marginTop: 12 },
    editInput: {
        borderWidth: 1,
        borderColor: '#cbd5e1',
        borderRadius: 10,
        paddingHorizontal: 14,
        paddingVertical: 10,
        fontSize: 15,
        color: '#334155',
        backgroundColor: '#f8fafc',
    },
    previewBox: {
        marginTop: 20,
        backgroundColor: '#f0fdf4',
        borderRadius: 12,
        padding: 14,
        borderWidth: 1,
        borderColor: '#bbf7d0',
    },
    previewTitle: { fontSize: 12, fontWeight: '700', color: '#166534', marginBottom: 10 },
    previewRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 6 },
    previewLabel: { fontSize: 13, color: '#166534' },
    previewValue: { fontSize: 13, fontWeight: '700', color: '#166534' },
    saveButton: {
        flexDirection: 'row',
        backgroundColor: '#8b5cf6',
        paddingVertical: 14,
        borderRadius: 14,
        alignItems: 'center',
        justifyContent: 'center',
        gap: 8,
        marginTop: 20,
        marginBottom: 8,
    },
    saveButtonText: { color: '#ffffff', fontSize: 15, fontWeight: '800' },
});
