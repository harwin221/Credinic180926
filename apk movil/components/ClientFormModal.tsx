import { View, Text, StyleSheet, Modal, TouchableOpacity, ScrollView, TextInput, ActivityIndicator, Platform, KeyboardAvoidingView } from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useState, useEffect } from 'react';
import { API_ENDPOINTS } from '../config/api';
import { apiFetch } from '../config/apiFetch';
import CustomAlert from './CustomAlert';

interface ClientFormModalProps {
    visible: boolean;
    onClose: () => void;
    onSuccess: (newClient: any) => void;
}

interface Municipio {
    id_enc: string;
    nombre: string;
}

interface DepartamentoData {
    departamento: string;
    municipios: Municipio[];
}

const ESTADOS_CIVILES = [
    { label: 'Soltero(a)', value: 0 },
    { label: 'Casado(a)', value: 1 },
    { label: 'Unión Libre', value: 2 },
    { label: 'Viudo(a)', value: 3 },
    { label: 'Divorciado(a)', value: 4 },
];

export default function ClientFormModal({ visible, onClose, onSuccess }: ClientFormModalProps) {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isLoadingLocations, setIsLoadingLocations] = useState(false);
    const [locations, setLocations] = useState<DepartamentoData[]>([]);
    
    // Sub-modales para pickers personalizados
    const [showLocationPicker, setShowLocationPicker] = useState(false);
    const [showCivilStatusPicker, setShowCivilStatusPicker] = useState(false);
    
    const [locationSearch, setLocationSearch] = useState('');
    const [selectedLocationName, setSelectedLocationName] = useState('');

    const [alert, setAlert] = useState<{
        visible: boolean;
        type: 'success' | 'error' | 'warning' | 'info';
        title: string;
        message: string;
        onConfirm?: () => void;
    }>({
        visible: false,
        type: 'info',
        title: '',
        message: '',
    });

    const [formData, setFormData] = useState({
        nombres: '',
        apellidos: '',
        cedula: '',
        telefono1: '',
        telefono2: '',
        direccion: '',
        sexo: 0, // 0 = Masculino, 1 = Femenino
        estado_civil: 0, // 0 = Soltero, 1 = Casado, etc
        dep_mun: '', // ID encriptado de municipio
    });

    // Resetear formulario y cargar ubicaciones del backend al abrir el modal
    useEffect(() => {
        if (visible) {
            setFormData({
                nombres: '',
                apellidos: '',
                cedula: '',
                telefono1: '',
                telefono2: '',
                direccion: '',
                sexo: 0,
                estado_civil: 0,
                dep_mun: '',
            });
            setSelectedLocationName('');
            setLocationSearch('');
            fetchLocations();
        }
    }, [visible]);

    const fetchLocations = async () => {
        setIsLoadingLocations(true);
        try {
            const response = await apiFetch(`${API_ENDPOINTS.base}/api/mobile/departamentos-municipios`, {
                method: 'GET'
            });
            const result = await response.json();
            if (result.success) {
                setLocations(result.data);
            } else {
                console.warn('[CLIENT_FORM] Error al cargar ubicaciones:', result.message);
            }
        } catch (error) {
            console.error('[CLIENT_FORM] Error de red ubicaciones:', error);
        } finally {
            setIsLoadingLocations(false);
        }
    };

    const handleSubmit = async () => {
        if (!formData.nombres.trim()) {
            setAlert({ visible: true, type: 'warning', title: 'Campo Requerido', message: 'El nombre es obligatorio' });
            return;
        }
        if (!formData.apellidos.trim()) {
            setAlert({ visible: true, type: 'warning', title: 'Campo Requerido', message: 'El apellido es obligatorio' });
            return;
        }
        if (!formData.cedula.trim()) {
            setAlert({ visible: true, type: 'warning', title: 'Campo Requerido', message: 'La cédula es obligatoria' });
            return;
        }
        if (!formData.dep_mun) {
            setAlert({ visible: true, type: 'warning', title: 'Campo Requerido', message: 'Debe seleccionar un Municipio/Departamento' });
            return;
        }

        setIsSubmitting(true);
        try {
            const response = await apiFetch(`${API_ENDPOINTS.base}/api/mobile/mobile_create_client`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData),
            });

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('[CLIENT_FORM] Response not JSON:', text);
                setAlert({
                    visible: true,
                    type: 'error',
                    title: 'Error de Servidor',
                    message: 'El servidor respondió de forma incorrecta.',
                });
                return;
            }

            const result = await response.json();

            if (result.success) {
                const createdClient = result.data;
                const formattedClient = {
                    id: createdClient.id,
                    name: createdClient.full_name,
                    clientNumber: createdClient.cedula,
                    cedula: createdClient.cedula,
                    phone: formData.telefono1 || formData.telefono2 || '',
                    address: formData.direccion || '',
                };

                setAlert({
                    visible: true,
                    type: 'success',
                    title: '¡Éxito!',
                    message: 'Cliente registrado correctamente en el sistema.',
                    onConfirm: () => {
                        onClose();
                        onSuccess(formattedClient);
                    },
                });
            } else {
                setAlert({
                    visible: true,
                    type: 'error',
                    title: 'Error',
                    message: result.message || 'No se pudo crear el cliente.',
                });
            }
        } catch (error) {
            console.error('[CLIENT_FORM] Error:', error);
            setAlert({
                visible: true,
                type: 'error',
                title: 'Error de Red',
                message: 'No se pudo conectar con el servidor.',
            });
        } finally {
            setIsSubmitting(false);
        }
    };

    // Filtrar municipios según búsqueda
    const getFilteredLocations = () => {
        if (!locationSearch.trim()) return locations;
        
        const query = locationSearch.toLowerCase();
        return locations.map(dept => {
            const matches = dept.municipios.filter(mun => 
                mun.nombre.toLowerCase().includes(query) || 
                dept.departamento.toLowerCase().includes(query)
            );
            return {
                ...dept,
                municipios: matches
            };
        }).filter(dept => dept.municipios.length > 0);
    };

    const selectedCivilStatusLabel = ESTADOS_CIVILES.find(e => e.value === formData.estado_civil)?.label || 'Soltero(a)';

    return (
        <Modal
            animationType="slide"
            transparent={true}
            visible={visible}
            onRequestClose={onClose}
        >
            <View style={styles.modalOverlay}>
                <View style={styles.modalContainer}>
                    <View style={styles.modalHeader}>
                        <Text style={styles.modalTitle}>Registrar Nuevo Cliente</Text>
                        <TouchableOpacity onPress={onClose} style={{ padding: 4 }}>
                            <MaterialCommunityIcons name="close" size={24} color="#64748b" />
                        </TouchableOpacity>
                    </View>

                    <KeyboardAvoidingView
                        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
                        style={styles.keyboardView}
                        keyboardVerticalOffset={Platform.OS === 'ios' ? 0 : 20}
                    >
                        <ScrollView 
                            showsVerticalScrollIndicator={false} 
                            style={styles.formScroll}
                            contentContainerStyle={styles.formScrollContent}
                            keyboardShouldPersistTaps="handled"
                        >
                            {/* Nombres */}
                            <Text style={styles.label}>Nombres *</Text>
                            <TextInput
                                style={styles.input}
                                placeholder="Ej: Juan Antonio"
                                placeholderTextColor="#94a3b8"
                                value={formData.nombres}
                                onChangeText={(text) => setFormData({ ...formData, nombres: text })}
                            />

                            {/* Apellidos */}
                            <Text style={styles.label}>Apellidos *</Text>
                            <TextInput
                                style={styles.input}
                                placeholder="Ej: Pérez Rodríguez"
                                placeholderTextColor="#94a3b8"
                                value={formData.apellidos}
                                onChangeText={(text) => setFormData({ ...formData, apellidos: text })}
                            />

                            {/* Cédula */}
                            <Text style={styles.label}>Cédula de Identidad *</Text>
                            <TextInput
                                style={styles.input}
                                placeholder="Ej: 001-150990-1002A"
                                placeholderTextColor="#94a3b8"
                                autoCapitalize="characters"
                                value={formData.cedula}
                                onChangeText={(text) => setFormData({ ...formData, cedula: text.toUpperCase() })}
                            />

                            {/* Fila para Sexo y Estado Civil */}
                            <View style={styles.formRow}>
                                <View style={{ flex: 1 }}>
                                    <Text style={styles.label}>Sexo</Text>
                                    <View style={styles.genderContainer}>
                                        <TouchableOpacity 
                                            style={[styles.genderButton, formData.sexo === 0 && styles.genderButtonActive]} 
                                            onPress={() => setFormData({ ...formData, sexo: 0 })}
                                        >
                                            <MaterialCommunityIcons name="gender-male" size={20} color={formData.sexo === 0 ? '#fff' : '#64748b'} />
                                            <Text style={[styles.genderText, formData.sexo === 0 && styles.genderTextActive]}>Masc</Text>
                                        </TouchableOpacity>
                                        <TouchableOpacity 
                                            style={[styles.genderButton, formData.sexo === 1 && styles.genderButtonActive]} 
                                            onPress={() => setFormData({ ...formData, sexo: 1 })}
                                        >
                                            <MaterialCommunityIcons name="gender-female" size={20} color={formData.sexo === 1 ? '#fff' : '#64748b'} />
                                            <Text style={[styles.genderText, formData.sexo === 1 && styles.genderTextActive]}>Fem</Text>
                                        </TouchableOpacity>
                                    </View>
                                </View>

                                <View style={{ flex: 1, marginLeft: 12 }}>
                                    <Text style={styles.label}>Estado Civil</Text>
                                    <TouchableOpacity 
                                        style={styles.selectorField} 
                                        onPress={() => setShowCivilStatusPicker(true)}
                                    >
                                        <Text style={styles.selectorFieldText}>{selectedCivilStatusLabel}</Text>
                                        <MaterialCommunityIcons name="chevron-down" size={20} color="#64748b" />
                                    </TouchableOpacity>
                                </View>
                            </View>

                            {/* Departamento / Municipio */}
                            <Text style={styles.label}>Departamento / Municipio *</Text>
                            <TouchableOpacity 
                                style={[styles.selectorField, !formData.dep_mun && styles.selectorFieldEmpty]} 
                                onPress={() => setShowLocationPicker(true)}
                            >
                                <View style={styles.selectorFieldLeft}>
                                    <MaterialCommunityIcons name="map-marker-radius" size={18} color={formData.dep_mun ? '#0ea5e9' : '#94a3b8'} />
                                    <Text style={[styles.selectorFieldText, !formData.dep_mun && styles.selectorFieldTextPlaceholder]} numberOfLines={1}>
                                        {selectedLocationName || 'Seleccionar municipio...'}
                                    </Text>
                                </View>
                                {isLoadingLocations ? (
                                    <ActivityIndicator size="small" color="#0ea5e9" />
                                ) : (
                                    <MaterialCommunityIcons name="chevron-down" size={20} color="#64748b" />
                                )}
                            </TouchableOpacity>

                            {/* Teléfono 1 */}
                            <Text style={styles.label}>Teléfono Principal</Text>
                            <TextInput
                                style={styles.input}
                                placeholder="Ej: 88887777"
                                placeholderTextColor="#94a3b8"
                                keyboardType="numeric"
                                value={formData.telefono1}
                                onChangeText={(text) => setFormData({ ...formData, telefono1: text.replace(/[^0-9]/g, '') })}
                            />

                            {/* Teléfono 2 */}
                            <Text style={styles.label}>Teléfono Alternativo</Text>
                            <TextInput
                                style={styles.input}
                                placeholder="Ej: 55554444"
                                placeholderTextColor="#94a3b8"
                                keyboardType="numeric"
                                value={formData.telefono2}
                                onChangeText={(text) => setFormData({ ...formData, telefono2: text.replace(/[^0-9]/g, '') })}
                            />

                            {/* Dirección */}
                            <Text style={styles.label}>Dirección Domiciliar</Text>
                            <TextInput
                                style={[styles.input, styles.textArea]}
                                placeholder="Ej: De los semáforos de la danto 3 cuadras al norte, mano derecha"
                                placeholderTextColor="#94a3b8"
                                multiline
                                numberOfLines={3}
                                value={formData.direccion}
                                onChangeText={(text) => setFormData({ ...formData, direccion: text })}
                            />
                        </ScrollView>
                    </KeyboardAvoidingView>

                    {/* Botón de guardar */}
                    <View style={styles.buttonContainer}>
                        <TouchableOpacity
                            style={[styles.submitButton, isSubmitting && styles.submitButtonDisabled]}
                            onPress={handleSubmit}
                            disabled={isSubmitting}
                        >
                            {isSubmitting ? (
                                <ActivityIndicator color="#fff" />
                            ) : (
                                <>
                                    <MaterialCommunityIcons name="account-check" size={20} color="#fff" />
                                    <Text style={styles.submitButtonText}>Registrar Cliente</Text>
                                </>
                            )}
                        </TouchableOpacity>
                    </View>
                </View>
            </View>

            {/* Custom Modal Picker para Departamento / Municipio */}
            <Modal
                animationType="fade"
                transparent={true}
                visible={showLocationPicker}
                onRequestClose={() => setShowLocationPicker(false)}
            >
                <View style={styles.subOverlay}>
                    <View style={styles.subContainer}>
                        <View style={styles.subHeader}>
                            <Text style={styles.subTitle}>Seleccionar Ubicación</Text>
                            <TouchableOpacity onPress={() => setShowLocationPicker(false)}>
                                <MaterialCommunityIcons name="close" size={24} color="#64748b" />
                            </TouchableOpacity>
                        </View>

                        {/* Buscador interno */}
                        <View style={styles.subSearchWrapper}>
                            <MaterialCommunityIcons name="magnify" size={20} color="#94a3b8" />
                            <TextInput
                                style={styles.subSearchInput}
                                placeholder="Buscar municipio o departamento..."
                                placeholderTextColor="#94a3b8"
                                value={locationSearch}
                                onChangeText={setLocationSearch}
                            />
                            {locationSearch.length > 0 && (
                                <TouchableOpacity onPress={() => setLocationSearch('')}>
                                    <MaterialCommunityIcons name="close-circle" size={18} color="#94a3b8" />
                                </TouchableOpacity>
                            )}
                        </View>

                        <ScrollView style={{ flex: 1 }} keyboardShouldPersistTaps="handled">
                            {getFilteredLocations().map((dept, dIdx) => (
                                <View key={`dept_${dIdx}`} style={styles.deptGroup}>
                                    <Text style={styles.deptHeader}>{dept.departamento}</Text>
                                    {dept.municipios.map((mun, mIdx) => (
                                        <TouchableOpacity
                                            key={`mun_${mun.id_enc}_${mIdx}`}
                                            style={[styles.munItem, formData.dep_mun === mun.id_enc && styles.munItemActive]}
                                            onPress={() => {
                                                setFormData({ ...formData, dep_mun: mun.id_enc });
                                                setSelectedLocationName(`${mun.nombre}, ${dept.departamento}`);
                                                setShowLocationPicker(false);
                                            }}
                                        >
                                            <Text style={[styles.munItemText, formData.dep_mun === mun.id_enc && styles.munItemTextActive]}>
                                                {mun.nombre}
                                            </Text>
                                            {formData.dep_mun === mun.id_enc && (
                                                <MaterialCommunityIcons name="check" size={18} color="#0ea5e9" />
                                            )}
                                        </TouchableOpacity>
                                    ))}
                                </View>
                            ))}
                            {getFilteredLocations().length === 0 && (
                                <Text style={styles.emptySearchText}>No se encontraron resultados.</Text>
                            )}
                        </ScrollView>
                    </View>
                </View>
            </Modal>

            {/* Custom Modal Picker para Estado Civil */}
            <Modal
                animationType="fade"
                transparent={true}
                visible={showCivilStatusPicker}
                onRequestClose={() => setShowCivilStatusPicker(false)}
            >
                <View style={styles.subOverlay}>
                    <View style={styles.subContainerCompact}>
                        <View style={styles.subHeader}>
                            <Text style={styles.subTitle}>Seleccionar Estado Civil</Text>
                            <TouchableOpacity onPress={() => setShowCivilStatusPicker(false)}>
                                <MaterialCommunityIcons name="close" size={24} color="#64748b" />
                            </TouchableOpacity>
                        </View>

                        <ScrollView style={{ paddingVertical: 10 }}>
                            {ESTADOS_CIVILES.map((estado) => (
                                <TouchableOpacity
                                    key={`civil_${estado.value}`}
                                    style={[styles.munItem, formData.estado_civil === estado.value && styles.munItemActive]}
                                    onPress={() => {
                                        setFormData({ ...formData, estado_civil: estado.value });
                                        setShowCivilStatusPicker(false);
                                    }}
                                >
                                    <Text style={[styles.munItemText, formData.estado_civil === estado.value && styles.munItemTextActive]}>
                                        {estado.label}
                                    </Text>
                                    {formData.estado_civil === estado.value && (
                                        <MaterialCommunityIcons name="check" size={18} color="#0ea5e9" />
                                    )}
                                </TouchableOpacity>
                            ))}
                        </ScrollView>
                    </View>
                </View>
            </Modal>

            <CustomAlert
                visible={alert.visible}
                type={alert.type}
                title={alert.title}
                message={alert.message}
                onClose={() => {
                    setAlert({ ...alert, visible: false });
                    if (alert.onConfirm) alert.onConfirm();
                }}
            />
        </Modal>
    );
}

const styles = StyleSheet.create({
    modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end', alignItems: 'center' },
    modalContainer: { 
        backgroundColor: '#fff', 
        borderTopLeftRadius: 20, 
        borderTopRightRadius: 20, 
        padding: 20, 
        height: '85%', 
        width: '100%' 
    },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
    modalTitle: { fontSize: 18, fontWeight: '800', color: '#1e293b' },
    keyboardView: { flex: 1 },
    formScroll: { flex: 1 },
    formScrollContent: { paddingBottom: 20 },
    label: { fontSize: 13, fontWeight: '700', color: '#334155', marginTop: 14, marginBottom: 6, textTransform: 'uppercase', letterSpacing: 0.5 },
    input: {
        backgroundColor: '#f8fafc',
        borderRadius: 10,
        paddingHorizontal: 14,
        paddingVertical: 12,
        fontSize: 15,
        color: '#334155',
        borderWidth: 1,
        borderColor: '#e2e8f0',
        minHeight: 48,
    },
    formRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
    },
    genderContainer: {
        flexDirection: 'row',
        backgroundColor: '#f1f5f9',
        borderRadius: 10,
        padding: 3,
        height: 48,
        alignItems: 'center',
    },
    genderButton: {
        flex: 1,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        height: '100%',
        borderRadius: 8,
        gap: 4,
    },
    genderButtonActive: {
        backgroundColor: '#0ea5e9',
    },
    genderText: {
        fontSize: 13,
        fontWeight: '700',
        color: '#64748b',
    },
    genderTextActive: {
        color: '#fff',
    },
    selectorField: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        backgroundColor: '#f8fafc',
        borderRadius: 10,
        paddingHorizontal: 14,
        height: 48,
        borderWidth: 1,
        borderColor: '#e2e8f0',
    },
    selectorFieldEmpty: {
        borderColor: '#e2e8f0',
    },
    selectorFieldLeft: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 8,
        flex: 1,
    },
    selectorFieldText: {
        fontSize: 14,
        fontWeight: '600',
        color: '#334155',
    },
    selectorFieldTextPlaceholder: {
        color: '#94a3b8',
    },
    textArea: {
        minHeight: 80,
        textAlignVertical: 'top',
    },
    buttonContainer: { 
         marginTop: 10, 
         paddingBottom: Platform.OS === 'ios' ? 20 : 10,
    },
    submitButton: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: '#0ea5e9',
        paddingVertical: 14,
        borderRadius: 12,
        gap: 8,
    },
    submitButtonDisabled: { backgroundColor: '#94a3b8' },
    submitButtonText: { fontSize: 15, fontWeight: '700', color: '#fff' },
    
    // Sub-modales de Pickers
    subOverlay: {
        flex: 1,
        backgroundColor: 'rgba(0,0,0,0.5)',
        justifyContent: 'center',
        alignItems: 'center',
        padding: 20,
    },
    subContainer: {
        backgroundColor: '#fff',
        borderRadius: 16,
        width: '100%',
        height: '70%',
        padding: 20,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.25,
        shadowRadius: 10,
        elevation: 10,
    },
    subContainerCompact: {
        backgroundColor: '#fff',
        borderRadius: 16,
        width: '100%',
        maxHeight: '50%',
        padding: 20,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.25,
        shadowRadius: 10,
        elevation: 10,
    },
    subHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 15,
    },
    subTitle: {
        fontSize: 16,
        fontWeight: '800',
        color: '#1e293b',
    },
    subSearchWrapper: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#f1f5f9',
        borderRadius: 10,
        paddingHorizontal: 10,
        height: 44,
        marginBottom: 15,
        gap: 6,
    },
    subSearchInput: {
        flex: 1,
        fontSize: 14,
        color: '#334155',
        height: '100%',
    },
    deptGroup: {
        marginBottom: 16,
    },
    deptHeader: {
        fontSize: 12,
        fontWeight: '800',
        color: '#0ea5e9',
        backgroundColor: '#eff6ff',
        paddingVertical: 4,
        paddingHorizontal: 10,
        borderRadius: 6,
        marginBottom: 6,
        textTransform: 'uppercase',
    },
    munItem: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingVertical: 12,
        paddingHorizontal: 10,
        borderBottomWidth: 1,
        borderBottomColor: '#f1f5f9',
    },
    munItemActive: {
        backgroundColor: '#f0f9ff',
    },
    munItemText: {
        fontSize: 14,
        color: '#475569',
        fontWeight: '500',
    },
    munItemTextActive: {
        color: '#0ea5e9',
        fontWeight: '700',
    },
    emptySearchText: {
        textAlign: 'center',
        color: '#94a3b8',
        fontSize: 14,
        marginTop: 20,
    },
});
