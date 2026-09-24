import { View, Text, StyleSheet, TextInput, TouchableOpacity, ScrollView, SafeAreaView, ActivityIndicator, StatusBar, Platform } from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useState, useCallback, useEffect } from 'react';
import React from 'react';
import { API_ENDPOINTS } from '../../config/api';
import { apiFetch } from '../../config/apiFetch';
import { sessionService } from '../../services/session';
import { AlertHelper } from '../../utils/custom-alert-helper';
import ClientDetailModal from '../../components/ClientDetailModal';
import ReceiptModal, { ReceiptData } from '../../components/ReceiptModal';

export default function SearchScreen() {
    const [searchQuery, setSearchQuery] = useState('');
    const [isSearching, setIsSearching] = useState(false);
    const [searchResults, setSearchResults] = useState<any[]>([]);
    const [selectedCredit, setSelectedCredit] = useState<any>(null);
    const [isDetailVisible, setIsDetailVisible] = useState(false);
    const [receiptData, setReceiptData] = useState<ReceiptData | null>(null);
    const [isReceiptVisible, setIsReceiptVisible] = useState(false);

    const handleSearch = useCallback(async (term?: string) => {
        const q = (term ?? searchQuery).trim();
        if (q.length < 2) {
            setSearchResults([]);
            return;
        }

        setIsSearching(true);
        try {
            const resp = await apiFetch(`${API_ENDPOINTS.mobile_search}?q=${encodeURIComponent(q)}`);
            const result = await resp.json();

            if (result.success) {
                setSearchResults(result.data || []);
            } else {
                setSearchResults([]);
            }
        } catch (error) {
            console.error('Search error:', error);
        } finally {
            setIsSearching(false);
        }
    }, [searchQuery]);

    // Búsqueda automática al escribir con debounce de 400ms
    React.useEffect(() => {
        if (searchQuery.trim().length < 2) {
            setSearchResults([]);
            return;
        }
        const timer = setTimeout(() => {
            handleSearch(searchQuery.trim());
        }, 400);
        return () => clearTimeout(timer);
    }, [searchQuery]);

    const handleSelectCredit = async (item: any) => {
        try {
            const resp = await apiFetch(`${API_ENDPOINTS.mobile_credit_detail}?creditId=${item.id}`);
            const result = await resp.json();
            
            if (result.success) {
                setSelectedCredit(result.data);
                setIsDetailVisible(true);
            } else {
                AlertHelper.alert('Error', 'No se pudo cargar el detalle del crédito');
            }
        } catch (error) {
            console.error('Error loading credit detail:', error);
            AlertHelper.alert('Error', 'No se pudo cargar el detalle del crédito');
        }
    };

    // ─── Reimprimir Recibo (lógica idéntica al botón reimprimir de la web) ──────
    const handleReprintReceipt = async (payment: any, credit: any) => {
        const session = await sessionService.getSession();
        if (!session) return;

        try {
            console.log('[REPRINT] Intentando reimprimir:', { 
                creditId: credit?.id, 
                paymentId: payment?.id,
                transactionNumber: payment?.transactionNumber || payment?.receiptNumber
            });

            const abonoId = payment?.id;
            let result: any = null;

            // 1. Intentar GET /api/mobile/recibo/{id}
            if (abonoId) {
                try {
                    const resp = await apiFetch(`${API_ENDPOINTS.base}/api/mobile/recibo/${abonoId}`);
                    const json = await resp.json();
                    if (json.success && json.data) {
                        result = json;
                    }
                } catch (e) {
                    console.warn('[REPRINT] Falló GET /recibo/{id}, probando POST:', e);
                }
            }

            // 2. Si no, intentar POST /api/mobile/recibo con abono_id / prestamo_id
            if (!result || !result.success) {
                const response = await apiFetch(API_ENDPOINTS.mobile_recibo || `${API_ENDPOINTS.base}/api/mobile/recibo`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        abono_id: abonoId,
                        paymentId: abonoId,
                        prestamo_id: credit?.id,
                        creditId: credit?.id,
                        userId: session.id,
                    })
                });
                result = await response.json();
            }

            if (result && result.success && result.data) {
                // Cerrar el detalle primero para que el recibo sea visible
                setIsDetailVisible(false);
                setTimeout(() => {
                    setReceiptData({ ...result.data, is_reimpresion: true });
                    setIsReceiptVisible(true);
                }, 300);
            } else {
                console.error('[REPRINT] Error en respuesta del servidor:', result);
                AlertHelper.alert('Error', result?.message || result?.error || 'No se pudo generar el recibo');
            }
        } catch (error) {
            console.error('[REPRINT] Error al reimprimir:', error);
            AlertHelper.alert('Error', 'No se pudo conectar con el servidor para reimprimir');
        }
    };

    return (
        <SafeAreaView style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor="#ffffff" translucent={false} />
            
            <View style={styles.header}>
                <Text style={styles.headerTitle}>Consulta de Clientes</Text>
                <Text style={styles.headerSubtitle}>Módulo de Consultas y Estados de Cuenta</Text>
            </View>

            <View style={styles.searchContainer}>
                <View style={styles.searchWrapper}>
                    <MaterialCommunityIcons name="magnify" size={24} color="#64748b" style={styles.searchIcon} />
                    <TextInput
                        style={styles.searchInput}
                        placeholder="Nombre, cédula o código del cliente..."
                        placeholderTextColor="#94a3b8"
                        value={searchQuery}
                        onChangeText={setSearchQuery}
                        onSubmitEditing={handleSearch}
                        returnKeyType="search"
                    />
                    {searchQuery.length > 0 && (
                        <TouchableOpacity onPress={() => { setSearchQuery(''); setSearchResults([]); }}>
                            <MaterialCommunityIcons name="close-circle" size={20} color="#94a3b8" />
                        </TouchableOpacity>
                    )}
                </View>
                <TouchableOpacity 
                    style={styles.searchButton}
                    onPress={handleSearch}
                    disabled={isSearching}
                >
                    {isSearching ? (
                        <ActivityIndicator color="#ffffff" size="small" />
                    ) : (
                        <Text style={styles.searchButtonText}>Buscar</Text>
                    )}
                </TouchableOpacity>
            </View>

            <ScrollView 
                contentContainerStyle={styles.scrollContent}
                keyboardShouldPersistTaps="handled"
                showsVerticalScrollIndicator={false}
            >
                {isSearching ? (
                    <View style={styles.loadingContainer}>
                        <ActivityIndicator size="large" color="#0ea5e9" />
                        <Text style={styles.loadingText}>Buscando en la base de datos...</Text>
                    </View>
                ) : searchResults.length > 0 ? (
                    searchResults.map((item) => (
                        <TouchableOpacity 
                            key={`search_result_${item.id}`} 
                            style={styles.resultCard}
                            onPress={() => handleSelectCredit(item)}
                            activeOpacity={0.7}
                        >
                            <View style={styles.resultHeader}>
                                <MaterialCommunityIcons 
                                    name="account" 
                                    size={40} 
                                    color="#0ea5e9" 
                                />
                                <View style={styles.resultInfo}>
                                    <Text style={styles.clientName}>{item.clientName}</Text>
                                    <Text style={styles.creditNumber}>Crédito: {item.creditNumber}</Text>
                                    {item.gestor && (
                                        <Text style={styles.gestorName}>Gestor: {item.gestor}</Text>
                                    )}
                                </View>
                            </View>

                            <View style={styles.resultDetails}>
                                <View style={styles.detailRow}>
                                    <Text style={styles.detailLabel}>Saldo Pendiente:</Text>
                                    <Text style={styles.detailValueRed}>C$ {Number(item.remainingBalance || 0).toLocaleString('es-NI', { minimumFractionDigits: 2 })}</Text>
                                </View>
                                <View style={styles.detailRow}>
                                    <Text style={styles.detailLabel}>Cuota del Día:</Text>
                                    <Text style={styles.detailValue}>C$ {Number(item.dueTodayAmount || 0).toLocaleString('es-NI', { minimumFractionDigits: 2 })}</Text>
                                </View>
                                {Number(item.overdueAmount || 0) > 0 && (
                                    <View style={styles.detailRow}>
                                        <Text style={styles.detailLabel}>En Mora:</Text>
                                        <Text style={styles.detailValueOrange}>C$ {Number(item.overdueAmount || 0).toLocaleString('es-NI', { minimumFractionDigits: 2 })}</Text>
                                    </View>
                                )}
                            </View>

                            <View style={styles.actionButton}>
                                <MaterialCommunityIcons 
                                    name="file-chart-outline" 
                                    size={20} 
                                    color="#0ea5e9" 
                                />
                                <Text style={styles.actionButtonText}>
                                    Ver Reporte / Estado de Cuenta
                                </Text>
                            </View>
                        </TouchableOpacity>
                    ))
                ) : searchQuery.length >= 2 && !isSearching ? (
                    <View style={styles.emptyContainer}>
                        <MaterialCommunityIcons name="account-search" size={64} color="#cbd5e1" />
                        <Text style={styles.emptyText}>No se encontraron resultados</Text>
                        <Text style={styles.emptySubtext}>Intenta con otro término de búsqueda</Text>
                    </View>
                ) : (
                    <View style={styles.emptyContainer}>
                        <MaterialCommunityIcons 
                            name="file-search-outline" 
                            size={64} 
                            color="#cbd5e1" 
                        />
                        <Text style={styles.emptyText}>
                            Consulta estados de cuenta
                        </Text>
                        <Text style={styles.emptySubtext}>Ingresa nombre, cédula o código del cliente</Text>
                    </View>
                )}
            </ScrollView>

            <ClientDetailModal
                visible={isDetailVisible}
                onClose={() => setIsDetailVisible(false)}
                credit={selectedCredit}
                onReprintReceipt={handleReprintReceipt}
            />

            <ReceiptModal
                visible={isReceiptVisible}
                onClose={() => setIsReceiptVisible(false)}
                receipt={receiptData}
            />
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#ffffff',
        paddingTop: Platform.OS === 'android' ? StatusBar.currentHeight : 0,
    },
    header: {
        padding: 20,
        paddingTop: 15,
        borderBottomWidth: 1,
        borderBottomColor: '#f1f5f9',
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
    searchContainer: {
        flexDirection: 'row',
        padding: 15,
        gap: 10,
    },
    searchWrapper: {
        flex: 1,
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#f8fafc',
        borderRadius: 12,
        paddingHorizontal: 12,
        borderWidth: 1,
        borderColor: '#e2e8f0',
    },
    searchIcon: {
        marginRight: 8,
    },
    searchInput: {
        flex: 1,
        height: 48,
        fontSize: 15,
        color: '#334155',
    },
    searchButton: {
        backgroundColor: '#0ea5e9',
        paddingHorizontal: 24,
        borderRadius: 12,
        justifyContent: 'center',
        alignItems: 'center',
        minWidth: 90,
    },
    searchButtonText: {
        color: '#ffffff',
        fontSize: 15,
        fontWeight: '700',
    },
    scrollContent: {
        padding: 15,
        paddingBottom: 90,
    },
    loadingContainer: {
        paddingVertical: 60,
        alignItems: 'center',
    },
    loadingText: {
        marginTop: 10,
        fontSize: 14,
        color: '#64748b',
    },
    resultCard: {
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
    resultHeader: {
        flexDirection: 'row',
        marginBottom: 15,
    },
    resultInfo: {
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
        color: '#0ea5e9',
        marginTop: 2,
        fontWeight: '600',
    },
    gestorName: {
        fontSize: 12,
        color: '#64748b',
        marginTop: 4,
    },
    resultDetails: {
        backgroundColor: '#f8fafc',
        borderRadius: 12,
        padding: 12,
        marginBottom: 12,
    },
    detailRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        marginBottom: 6,
    },
    detailLabel: {
        fontSize: 13,
        color: '#64748b',
    },
    detailValue: {
        fontSize: 13,
        fontWeight: '600',
        color: '#334155',
    },
    detailValueRed: {
        fontSize: 13,
        fontWeight: '700',
        color: '#e11d48',
    },
    detailValueOrange: {
        fontSize: 13,
        fontWeight: '700',
        color: '#f97316',
    },
    actionButton: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: '#f0f9ff',
        paddingVertical: 12,
        borderRadius: 12,
        gap: 8,
        borderWidth: 1,
        borderColor: '#bae6fd',
    },
    actionButtonText: {
        fontSize: 14,
        fontWeight: '700',
        color: '#0284c7',
    },
    emptyContainer: {
        paddingVertical: 60,
        alignItems: 'center',
    },
    emptyText: {
        fontSize: 16,
        color: '#64748b',
        marginTop: 15,
        fontWeight: '600',
    },
    emptySubtext: {
        fontSize: 14,
        color: '#94a3b8',
        marginTop: 8,
    },
});
