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

export default function ClientFormModal({ visible, onClose, onSuccess }: ClientFormModalProps) {
    const [isSubmitting, setIsSubmitting] = useState(false);
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
    });

    useEffect(() => {
        if (visible) {
            setFormData({
                nombres: '',
                apellidos: '',
                cedula: '',
                telefono1: '',
                telefono2: '',
                direccion: '',
            });
        }
    }, [visible]);

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

        setIsSubmitting(true);
        try {
            const response = await apiFetch(`${API_ENDPOINTS.base}/api/mobile/mobile_create_client`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData),
            });

            // Verificar respuesta JSON
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
                // El backend retorna: { id, full_name, cedula }
                // Mapeamos para que coincida con el objeto de cliente esperado en clients.tsx:
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
                                value={formData.cedula}
                                onChangeText={(text) => setFormData({ ...formData, cedula: text })}
                            />

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
});
