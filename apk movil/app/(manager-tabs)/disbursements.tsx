import { View, Text, StyleSheet, SafeAreaView, ScrollView, TouchableOpacity, RefreshControl, ActivityIndicator, StatusBar, Platform, Modal } from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useState, useCallback } from 'react';
import { useFocusEffect } from 'expo-router';
import { sessionService } from '../../services/session';
import { API_ENDPOINTS } from '../../config/api';
import { apiFetch } from '../../config/apiFetch';
import ReasonModal from '../../components/ReasonModal';
import { AlertHelper } from '../../utils/custom-alert-helper';

type TabType = 'pending' | 'disbursed' | 'denied';

export default function DisbursementsScreen() {
    const [isLoading, setIsLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [allDisbursements, setAllDisbursements] = useState<any[]>([]);
    const [selectedCredit, setSelectedCredit] = useState<any | null>(null);
    const [isModalVisible, setIsModalVisible] = useState(false);
    const [activeTab, setActiveTab] = useState<TabType>('pending');
    const [showReasonModal, setShowReasonModal] = useState(false);
    const [creditToDeny, setCreditToDeny] = useState<{ id: string; name: string } | null>(null);

    useFocusEffect(
        useCallback(() => {
            fetchDisbursements();
        }, [])
    );

    const fetchDisbursements = async () => {
        try {
            const session = await sessionService.getSession();
            if (!session?.id) return;

            const resp = await apiFetch(`${API_ENDPOINTS.mobile_disbursements}?userId=${session.id}`);
            const result = await resp.json();
            
            if (result.success) {
                setAllDisbursements(result.disbursements || []);
            }
        } catch (error) {
            console.error('Error fetching disbursements:', error);
        } finally {
            setIsLoading(false);
            setRefreshing(false);
        }
    };

    // Filtrar créditos por estado (el backend ya filtra por fecha)
    const pendingDisbursements = allDisbursements.filter(c => c.status === 'Approved');
    const disbursedToday = allDisbursements.filter(c => c.status === 'Active');
    const deniedToday = allDisbursements.filter(c => c.status === 'Rejected');

    // Obtener la lista actual según la pestaña activa
    const getCurrentList = () => {
        switch (activeTab) {
            case 'pending': return pendingDisbursements;
            case 'disbursed': return disbursedToday;
            case 'denied': return deniedToday;
            default: return [];
        }
    };

    const disbursements = getCurrentList();

    const onRefresh = () => {
        setRefreshing(true);
        fetchDisbursements();
    };

    const openModal = (credit: any) => {
        console.log('[DISBURSEMENTS] Opening modal for credit:', credit);
        console.log('[DISBURSEMENTS] outstandingBalance:', credit.outstandingBalance);
        console.log('[DISBURSEMENTS] amount:', credit.amount);
        console.log('[DISBURSEMENTS] netDisbursementAmount:', credit.netDisbursementAmount);
        setSelectedCredit(credit);
        setIsModalVisible(true);
    };

    const closeModal = () => {
        setIsModalVisible(false);
        setSelectedCredit(null);
    };

    const handleDisburse = async (creditId: string) => {
        console.log('[DISBURSEMENTS] handleDisburse called with creditId:', creditId);
        closeModal();
        
        console.log('[DISBURSEMENTS] Showing confirmation alert...');
        AlertHelper.alert(
            'Confirmar Desembolso',
            '¿Estás seguro de marcar este crédito como desembolsado?',
            [
                { 
                    text: 'Cancelar', 
                    style: 'cancel',
                    onPress: () => {
                        console.log('[DISBURSEMENTS] Cancelado por el usuario');
                    }
                },
                {
                    text: 'Desembolsar',
                    onPress: async () => {
                        console.log('[DISBURSEMENTS] ¡Botón Desembolsar presionado!');
                        console.log('[DISBURSEMENTS] Confirmado, enviando petición...');
                        try {
                            const session = await sessionService.getSession();
                            console.log('[DISBURSEMENTS] Session:', session?.id);
                            
                            const payload = {
                                creditId,
                                userId: session?.id
                            };
                            console.log('[DISBURSEMENTS] Payload:', payload);
                            console.log('[DISBURSEMENTS] Endpoint:', API_ENDPOINTS.disburse_credit);
                            
                            const resp = await apiFetch(API_ENDPOINTS.disburse_credit, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(payload)
                            });
                            
                            console.log('[DISBURSEMENTS] Response status:', resp.status);
                            const result = await resp.json();
                            console.log('[DISBURSEMENTS] Response:', result);
                            
                            if (result.success) {
                                AlertHelper.alert('Éxito', 'Crédito desembolsado correctamente');
                                fetchDisbursements();
                            } else {
                                AlertHelper.alert('Error', result.message || 'No se pudo desembolsar el crédito');
                            }
                        } catch (error) {
                            console.error('[DISBURSEMENTS] Error al desembolsar:', error);
                            AlertHelper.alert('Error', 'No se pudo conectar con el servidor');
                        }
                    }
                }
            ]
        );
        console.log('[DISBURSEMENTS] Alert should be visible now');
    };

    const handleDeny = (creditId: string, clientName: string) => {
        closeModal();
        setCreditToDeny({ id: creditId, name: clientName });
        setShowReasonModal(true);
    };

    const handleDenyConfirm = async (reason: string) => {
        setShowReasonModal(false);
        
        if (!creditToDeny) return;

        try {
            const session = await sessionService.getSession();
            const resp = await apiFetch(API_ENDPOINTS.deny_disbursement, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    creditId: creditToDeny.id,
                    userId: session?.id,
                    reason: reason
                })
            });
            const result = await resp.json();
            
            if (result.success) {
                AlertHelper.alert('Éxito', 'Desembolso denegado correctamente');
                fetchDisbursements();
            } else {
                AlertHelper.alert('Error', result.message || 'No se pudo denegar el desembolso');
            }
        } catch (error) {
            console.error('Error al denegar:', error);
            AlertHelper.alert('Error', 'No se pudo conectar con el servidor');
        } finally {
            setCreditToDeny(null);
        }
    };

    const handleDenyCancel = () => {
        setShowReasonModal(false);
        setCreditToDeny(null);
    };

    const formatAddress = (credit: any) => {
        const parts = [
            credit.department,
            credit.municipality,
            credit.neighborhood,
            credit.address
        ].filter(Boolean);
        return parts.join(', ') || 'Sin dirección';
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
                <Text style={styles.headerTitle}>Desembolsos</Text>
                <Text style={styles.headerSubtitle}>
                    {activeTab === 'pending' && `${pendingDisbursements.length} pendientes`}
                    {activeTab === 'disbursed' && `${disbursedToday.length} desembolsados hoy`}
                    {activeTab === 'denied' && `${deniedToday.length} denegados hoy`}
                </Text>
            </View>

            {/* Tabs */}
            <View style={styles.tabsContainer}>
                <TouchableOpacity 
                    style={[styles.tab, activeTab === 'pending' && styles.tabActive]}
                    onPress={() => setActiveTab('pending')}
                >
                    <Text style={[styles.tabText, activeTab === 'pending' && styles.tabTextActive]}>
                        Pendientes ({pendingDisbursements.length})
                    </Text>
                </TouchableOpacity>

                <TouchableOpacity 
                    style={[styles.tab, activeTab === 'disbursed' && styles.tabActive]}
                    onPress={() => setActiveTab('disbursed')}
                >
                    <Text style={[styles.tabText, activeTab === 'disbursed' && styles.tabTextActive]}>
                        Desembolsados ({disbursedToday.length})
                    </Text>
                </TouchableOpacity>

                <TouchableOpacity 
                    style={[styles.tab, activeTab === 'denied' && styles.tabActive]}
                    onPress={() => setActiveTab('denied')}
                >
                    <Text style={[styles.tabText, activeTab === 'denied' && styles.tabTextActive]}>
                        Denegados ({deniedToday.length})
                    </Text>
                </TouchableOpacity>
            </View>

            <ScrollView
                contentContainerStyle={styles.scrollContent}
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={['#0ea5e9']} />}
            >
                {disbursements.length > 0 ? (
                    disbursements.map((credit) => (
                        <TouchableOpacity 
                            key={credit.id} 
                            style={styles.creditItem}
                            onPress={() => openModal(credit)}
                        >
                            <View style={styles.creditItemHeader}>
                                <View style={styles.clientInfo}>
                                    <MaterialCommunityIcons name="account-circle" size={40} color="#10b981" />
                                    <View style={styles.clientDetails}>
                                        <Text style={styles.clientName}>{credit.clientName}</Text>
                                        <Text style={styles.creditNumber}>#{credit.creditNumber}</Text>
                                    </View>
                                </View>
                                <View style={styles.statusBadge}>
                                    <Text style={styles.statusText}>APROBADO</Text>
                                </View>
                            </View>

                            <View style={styles.creditItemBody}>
                                <View style={styles.addressRow}>
                                    <MaterialCommunityIcons name="map-marker" size={14} color="#64748b" />
                                    <Text style={styles.addressText} numberOfLines={1}>
                                        {credit.department && credit.municipality 
                                            ? `${credit.department}, ${credit.municipality}`
                                            : 'Sin ubicación'}
                                    </Text>
                                </View>

                                <View style={styles.infoRow}>
                                    <Text style={styles.infoLabel}>Monto Aprobado:</Text>
                                    <Text style={styles.infoValue}>C$ {Number(credit.amount || 0).toLocaleString('es-NI', { minimumFractionDigits: 2 })}</Text>
                                </View>

                                {credit.netDisbursementAmount !== undefined && credit.netDisbursementAmount !== credit.amount && (
                                    <View style={styles.infoRow}>
                                        <Text style={styles.infoLabel}>Monto Neto:</Text>
                                        <Text style={styles.infoValueHighlight}>C$ {Number(credit.netDisbursementAmount || 0).toLocaleString('es-NI', { minimumFractionDigits: 2 })}</Text>
                                    </View>
                                )}
                            </View>

                            <View style={styles.chevronContainer}>
                                <MaterialCommunityIcons name="chevron-right" size={24} color="#cbd5e1" />
                            </View>
                        </TouchableOpacity>
                    ))
                ) : (
                    <View style={styles.emptyContainer}>
                        <MaterialCommunityIcons name="cash-check" size={64} color="#cbd5e1" />
                        <Text style={styles.emptyText}>No hay desembolsos pendientes</Text>
                    </View>
                )}
            </ScrollView>

            {/* Modal de Detalles */}
            <Modal
                visible={isModalVisible}
                animationType="fade"
                transparent={true}
                onRequestClose={closeModal}
            >
                <View style={styles.modalOverlay}>
                    <View style={styles.modalContent}>
                        <ScrollView showsVerticalScrollIndicator={false}>
                            {/* Header del Modal */}
                            <View style={styles.modalHeader}>
                                <Text style={styles.modalTitle}>{selectedCredit?.clientName}</Text>
                                <TouchableOpacity onPress={closeModal} style={styles.closeButton}>
                                    <MaterialCommunityIcons name="close" size={24} color="#64748b" />
                                </TouchableOpacity>
                            </View>

                            {/* Si es un crédito rechazado, mostrar solo información básica y motivo */}
                            {selectedCredit?.status === 'Rejected' ? (
                                <>
                                    {/* Información Básica */}
                                    <View style={styles.detailsSection}>
                                        <View style={styles.detailRow}>
                                            <Text style={styles.detailLabel}>Monto Solicitado</Text>
                                            <Text style={styles.detailValue}>C$ {Number(selectedCredit?.amount || 0).toLocaleString('es-NI', { minimumFractionDigits: 2 })}</Text>
                                        </View>

                                        <View style={styles.detailRow}>
                                            <Text style={styles.detailLabel}>Gestor</Text>
                                            <Text style={styles.detailValue}>{selectedCredit?.collectionsManager || 'N/A'}</Text>
                                        </View>

                                        <View style={styles.detailRow}>
                                            <Text style={styles.detailLabel}>Fecha de Rechazo</Text>
                                            <Text style={styles.detailValue}>
                                                {selectedCredit?.approvalDate 
                                                    ? new Date(selectedCredit.approvalDate).toLocaleDateString('es-NI')
                                                    : 'N/A'}
                                            </Text>
                                        </View>

                                        {selectedCredit?.rejectedBy && (
                                            <View style={styles.detailRow}>
                                                <Text style={styles.detailLabel}>Rechazado Por</Text>
                                                <Text style={styles.detailValue}>{selectedCredit.rejectedBy}</Text>
                                            </View>
                                        )}
                                    </View>

                                    {/* Motivo del Rechazo */}
                                    <View style={styles.rejectionSection}>
                                        <Text style={styles.rejectionTitle}>Motivo del Rechazo</Text>
                                        <Text style={styles.rejectionReason}>
                                            {selectedCredit?.rejectionReason || 'No se especificó un motivo'}
                                        </Text>
                                    </View>
                                </>
                            ) : (
                                <>
                                    {/* Dirección Completa */}
                                    <View style={styles.addressSection}>
                                        <MaterialCommunityIcons name="map-marker" size={16} color="#0369a1" />
                                        <Text style={styles.addressFullText}>
                                            {selectedCredit ? formatAddress(selectedCredit) : ''}
                                        </Text>
                                    </View>

                                    {/* Detalles del Crédito */}
                                    <View style={styles.detailsSection}>
                                        <View style={styles.detailRow}>
                                            <Text style={styles.detailLabel}>Monto Aprobado</Text>
                                            <Text style={styles.detailValue}>C$ {Number(selectedCredit?.amount || 0).toLocaleString('es-NI', { minimumFractionDigits: 2 })}</Text>
                                        </View>
                                        {Number(selectedCredit?.outstandingBalance || 0) > 0 && (
                                            <View style={styles.detailRow}>
                                                <Text style={styles.detailLabel}>Saldo Pendiente</Text>
                                                <Text style={styles.detailValueWarning}>C$ {Number(selectedCredit?.outstandingBalance || 0).toLocaleString('es-NI', { minimumFractionDigits: 2 })}</Text>
                                            </View>
                                        )}
                                        <View style={[styles.detailRow, styles.netRow]}>
                                            <Text style={styles.detailLabelBold}>Monto Neto a Entregar</Text>
                                            <Text style={styles.detailValueHighlight}>C$ {Number(selectedCredit?.netDisbursementAmount || 0).toLocaleString('es-NI', { minimumFractionDigits: 2 })}</Text>
                                        </View>
                                        <View style={styles.detailRow}>
                                            <Text style={styles.detailLabel}>Total a Pagar</Text>
                                            <Text style={styles.detailValue}>C$ {Number(selectedCredit?.totalAmount || selectedCredit?.totalInstallmentAmount || 0).toLocaleString('es-NI', { minimumFractionDigits: 2 })}</Text>
                                        </View>
                                        <View style={styles.detailRow}>
                                            <Text style={styles.detailLabel}>Valor de Cuota</Text>
                                            <Text style={styles.detailValueBold}>C$ {Number(selectedCredit?.installmentAmount || 0).toLocaleString('es-NI', { minimumFractionDigits: 2 })}</Text>
                                        </View>
                                        <View style={styles.detailRow}>
                                            <Text style={styles.detailLabel}>Plazo</Text>
                                            <Text style={styles.detailValue}>{selectedCredit?.termMonths || 0} meses</Text>
                                        </View>
                                        <View style={styles.detailRow}>
                                            <Text style={styles.detailLabel}>Frecuencia</Text>
                                            <Text style={styles.detailValue}>{selectedCredit?.paymentFrequency || 'N/A'}</Text>
                                        </View>
                                        <View style={styles.detailRow}>
                                            <Text style={styles.detailLabel}>Tasa de Interés</Text>
                                            <Text style={styles.detailValue}>{selectedCredit?.interestRate || 0}%</Text>
                                        </View>
                                        <View style={styles.detailRow}>
                                            <Text style={styles.detailLabel}>Gestor</Text>
                                            <Text style={styles.detailValue}>{selectedCredit?.collectionsManager || 'N/A'}</Text>
                                        </View>
                                        <View style={styles.detailRow}>
                                            <Text style={styles.detailLabel}>Fecha Primera Cuota</Text>
                                            <Text style={styles.detailValue}>
                                                {selectedCredit?.firstPaymentDate
                                                     ? (typeof selectedCredit.firstPaymentDate === 'string' && selectedCredit.firstPaymentDate.length === 10
                                                         ? selectedCredit.firstPaymentDate.split('-').reverse().join('/')
                                                         : new Date(selectedCredit.firstPaymentDate).toLocaleDateString('es-NI'))
                                                    : 'N/A'}
                                            </Text>
                                        </View>
                                    </View>

                                    {/* Botones de Acción - Solo para créditos pendientes */}
                                    {selectedCredit?.status === 'Approved' && (
                                        <View style={styles.buttonContainer}>
                                            <TouchableOpacity 
                                                style={styles.denyButton}
                                                onPress={() => selectedCredit && handleDeny(selectedCredit.id, selectedCredit.clientName)}
                                            >
                                                <MaterialCommunityIcons name="close-circle" size={18} color="#fff" />
                                                <Text style={styles.buttonText}>Denegar</Text>
                                            </TouchableOpacity>

                                            <TouchableOpacity 
                                                style={styles.disburseButton}
                                                onPress={() => selectedCredit && handleDisburse(selectedCredit.id)}
                                            >
                                                <MaterialCommunityIcons name="cash-check" size={18} color="#fff" />
                                                <Text style={styles.buttonText}>Desembolsar</Text>
                                            </TouchableOpacity>
                                        </View>
                                    )}
                                </>
                            )}
                        </ScrollView>
                    </View>
                </View>
            </Modal>

            {/* Modal para pedir motivo de denegación */}
            <ReasonModal
                visible={showReasonModal}
                title="Denegar Desembolso"
                message={`¿Por qué deseas denegar el desembolso para ${creditToDeny?.name}?`}
                onCancel={handleDenyCancel}
                onConfirm={handleDenyConfirm}
            />
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#f8fafc',
        paddingTop: Platform.OS === 'android' ? StatusBar.currentHeight : 0,
    },
    loadingContainer: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
    },
    header: {
        padding: 20,
        paddingTop: 15,
        backgroundColor: '#ffffff',
    },
    headerTitle: {
        fontSize: 22,
        fontWeight: '800',
        color: '#334155',
    },
    headerSubtitle: {
        fontSize: 14,
        color: '#64748b',
        marginTop: 4,
    },
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
    tabActive: {
        borderBottomColor: '#10b981',
    },
    tabText: {
        fontSize: 13,
        fontWeight: '600',
        color: '#64748b',
    },
    tabTextActive: {
        color: '#10b981',
        fontWeight: '700',
    },
    scrollContent: {
        padding: 15,
    },
    creditItem: {
        backgroundColor: '#ffffff',
        borderRadius: 12,
        padding: 16,
        marginBottom: 12,
        borderWidth: 1,
        borderColor: '#e2e8f0',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 1 },
        shadowOpacity: 0.05,
        shadowRadius: 2,
        elevation: 2,
    },
    creditItemHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 12,
    },
    clientInfo: {
        flexDirection: 'row',
        alignItems: 'center',
        flex: 1,
    },
    clientDetails: {
        marginLeft: 12,
        flex: 1,
    },
    clientName: {
        fontSize: 16,
        fontWeight: '700',
        color: '#334155',
    },
    creditNumber: {
        fontSize: 13,
        color: '#64748b',
        marginTop: 2,
    },
    statusBadge: {
        backgroundColor: '#d1fae5',
        paddingHorizontal: 10,
        paddingVertical: 5,
        borderRadius: 12,
    },
    statusText: {
        fontSize: 11,
        fontWeight: '700',
        color: '#059669',
    },
    creditItemBody: {
        gap: 8,
    },
    addressRow: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 6,
        marginBottom: 4,
    },
    addressText: {
        fontSize: 13,
        color: '#64748b',
        flex: 1,
    },
    infoRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
    },
    infoLabel: {
        fontSize: 13,
        color: '#64748b',
    },
    infoValue: {
        fontSize: 13,
        fontWeight: '600',
        color: '#334155',
    },
    infoValueHighlight: {
        fontSize: 14,
        fontWeight: '800',
        color: '#10b981',
    },
    chevronContainer: {
        position: 'absolute',
        right: 16,
        top: '50%',
        marginTop: -12,
    },
    emptyContainer: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
        paddingVertical: 60,
    },
    emptyText: {
        fontSize: 16,
        color: '#94a3b8',
        marginTop: 15,
    },
    // Modal Styles
    modalOverlay: {
        flex: 1,
        backgroundColor: 'rgba(0, 0, 0, 0.6)',
        justifyContent: 'center',
        alignItems: 'center',
        paddingHorizontal: 16,
        paddingVertical: 20,
    },
    modalContent: {
        backgroundColor: '#ffffff',
        borderRadius: 24,
        width: '100%',
        maxHeight: '90%',
        overflow: 'hidden',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 6 },
        shadowOpacity: 0.25,
        shadowRadius: 12,
        elevation: 10,
    },
    modalHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingHorizontal: 20,
        paddingTop: 18,
        paddingBottom: 14,
        borderBottomWidth: 1,
        borderBottomColor: '#f1f5f9',
    },
    modalTitle: {
        fontSize: 17,
        fontWeight: '800',
        color: '#1e293b',
        flex: 1,
        letterSpacing: 0.3,
    },
    closeButton: {
        padding: 6,
        borderRadius: 20,
        backgroundColor: '#f1f5f9',
        marginLeft: 8,
    },
    addressSection: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#f0f9ff',
        paddingHorizontal: 14,
        paddingVertical: 10,
        marginHorizontal: 18,
        marginTop: 14,
        borderRadius: 10,
        borderWidth: 1,
        borderColor: '#bae6fd',
        gap: 8,
    },
    addressFullText: {
        fontSize: 13,
        color: '#0369a1',
        flex: 1,
        fontWeight: '500',
    },
    warningSection: {
        backgroundColor: '#fef3c7',
        padding: 14,
        marginHorizontal: 18,
        marginTop: 12,
        borderRadius: 10,
        alignItems: 'center',
    },
    warningTitle: {
        fontSize: 13,
        color: '#92400e',
        fontWeight: '600',
        marginBottom: 2,
    },
    warningAmount: {
        fontSize: 18,
        color: '#92400e',
        fontWeight: '800',
    },
    detailsSection: {
        paddingHorizontal: 18,
        paddingTop: 10,
        paddingBottom: 6,
    },
    detailRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingVertical: 9,
        borderBottomWidth: 1,
        borderBottomColor: '#f1f5f9',
    },
    netRow: {
        backgroundColor: '#f0fdf4',
        marginHorizontal: -10,
        paddingHorizontal: 10,
        borderRadius: 8,
        borderBottomWidth: 0,
        marginVertical: 4,
    },
    detailLabel: {
        fontSize: 13,
        color: '#64748b',
        fontWeight: '500',
    },
    detailLabelBold: {
        fontSize: 13,
        color: '#0f766e',
        fontWeight: '700',
    },
    detailValue: {
        fontSize: 13,
        fontWeight: '600',
        color: '#334155',
    },
    detailValueBold: {
        fontSize: 14,
        fontWeight: '700',
        color: '#0f172a',
    },
    detailValueHighlight: {
        fontSize: 15,
        fontWeight: '800',
        color: '#059669',
    },
    detailValueWarning: {
        fontSize: 13,
        fontWeight: '700',
        color: '#d97706',
    },
    buttonContainer: {
        flexDirection: 'row',
        gap: 12,
        paddingHorizontal: 18,
        paddingTop: 12,
        paddingBottom: 18,
    },
    denyButton: {
        flex: 1,
        flexDirection: 'row',
        backgroundColor: '#ef4444',
        paddingVertical: 13,
        borderRadius: 12,
        alignItems: 'center',
        justifyContent: 'center',
        gap: 6,
        elevation: 2,
        shadowColor: '#ef4444',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.2,
        shadowRadius: 3,
    },
    disburseButton: {
        flex: 1,
        flexDirection: 'row',
        backgroundColor: '#059669',
        paddingVertical: 13,
        borderRadius: 12,
        alignItems: 'center',
        justifyContent: 'center',
        gap: 6,
        elevation: 2,
        shadowColor: '#059669',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.2,
        shadowRadius: 3,
    },
    buttonText: {
        color: '#ffffff',
        fontSize: 14,
        fontWeight: '700',
    },
    rejectionSection: {
        backgroundColor: '#fef2f2',
        padding: 16,
        marginHorizontal: 18,
        marginTop: 12,
        marginBottom: 20,
        borderRadius: 12,
        borderWidth: 1,
        borderColor: '#fecaca',
    },
    rejectionTitle: {
        fontSize: 14,
        fontWeight: '700',
        color: '#991b1b',
        marginBottom: 8,
    },
    rejectionReason: {
        fontSize: 14,
        color: '#7f1d1d',
        lineHeight: 20,
    },
});