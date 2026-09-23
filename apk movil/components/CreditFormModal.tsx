import { View, Text, StyleSheet, Modal, TouchableOpacity, ScrollView, TextInput, ActivityIndicator, Platform, KeyboardAvoidingView } from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useState, useEffect } from 'react';
import { sessionService } from '../services/session';
import { API_ENDPOINTS } from '../config/api';
import { apiFetch } from '../config/apiFetch';
import DateTimePicker from '@react-native-community/datetimepicker';
import CustomAlert from './CustomAlert';

interface CreditFormModalProps {
    visible: boolean;
    onClose: () => void;
    client: any;
    onSuccess: () => void;
}

const PAYMENT_FREQUENCIES = ['Diario', 'Semanal', 'Catorcenal', 'Quincenal'];

export default function CreditFormModal({ visible, onClose, client, onSuccess }: CreditFormModalProps) {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [showDatePicker, setShowDatePicker] = useState(false);
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
        amount: '',
        interestRate: '15', // Tasa de interés mensual estándar por defecto
        termMonths: '3', // Plazo mensual estándar por defecto
        paymentFrequency: 'Semanal',
        firstPaymentDate: new Date(),
    });

    // Resetear formulario cuando se abre el modal
    useEffect(() => {
        if (visible) {
            // Calcular fecha de primer pago (7 días desde hoy)
            const nextWeek = new Date();
            nextWeek.setDate(nextWeek.getDate() + 7);
            
            setFormData({
                amount: '',
                interestRate: '15',
                termMonths: '3',
                paymentFrequency: 'Semanal',
                firstPaymentDate: nextWeek,
            });
        }
    }, [visible]);

    const handleDateChange = (event: any, selectedDate?: Date) => {
        setShowDatePicker(Platform.OS === 'ios'); // En iOS mantener abierto
        if (selectedDate) {
            setFormData({ ...formData, firstPaymentDate: selectedDate });
        }
    };

    const formatDate = (date: Date) => {
        return date.toLocaleDateString('es-NI', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        });
    };

    const handleSubmit = async () => {
        // Validaciones básicas
        if (!formData.amount || parseFloat(formData.amount) < 1000) {
            setAlert({
                visible: true,
                type: 'warning',
                title: 'Monto Inválido',
                message: 'El monto mínimo es C$1,000',
            });
            return;
        }

        if (!formData.interestRate || parseFloat(formData.interestRate) < 1) {
            setAlert({
                visible: true,
                type: 'warning',
                title: 'Tasa Inválida',
                message: 'La tasa de interés debe ser al menos 1%',
            });
            return;
        }

        if (!formData.termMonths || parseFloat(formData.termMonths) < 0.5) {
            setAlert({
                visible: true,
                type: 'warning',
                title: 'Plazo Inválido',
                message: 'El plazo mínimo es 0.5 meses',
            });
            return;
        }

        setIsSubmitting(true);
        try {
            const session = await sessionService.getSession();
            if (!session) {
                setAlert({
                    visible: true,
                    type: 'error',
                    title: 'Error de Sesión',
                    message: 'No se pudo obtener la sesión del usuario',
                });
                setIsSubmitting(false);
                return;
            }

            // Preparar datos para enviar
            const creditData = {
                clientId: client.id,
                amount: parseFloat(formData.amount),
                interestRate: parseFloat(formData.interestRate),
                termMonths: parseFloat(formData.termMonths),
                paymentFrequency: formData.paymentFrequency,
                firstPaymentDate: formData.firstPaymentDate.toISOString(),
            };

            const response = await apiFetch(`${API_ENDPOINTS.mobile_create_credit}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(creditData),
            });

            const result = await response.json();

            if (result.success) {
                setAlert({
                    visible: true,
                    type: 'success',
                    title: '¡Éxito!',
                    message: 'Solicitud de crédito creada exitosamente',
                    onConfirm: () => {
                        onClose();
                        onSuccess();
                    },
                });
            } else {
                setAlert({
                    visible: true,
                    type: 'error',
                    title: 'Error',
                    message: result.message || 'No se pudo crear la solicitud',
                });
            }
        } catch (error) {
            console.error('[CREDIT_FORM] Submit error:', error);
            setAlert({
                visible: true,
                type: 'error',
                title: 'Error de Red',
                message: 'No se pudo conectar con el servidor',
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
                        <Text style={styles.modalTitle}>Nueva Solicitud de Crédito</Text>
                        <TouchableOpacity onPress={onClose} style={{ padding: 4 }}>
                            <MaterialCommunityIcons name="close" size={24} color="#64748b" />
                        </TouchableOpacity>
                    </View>

                    <Text style={styles.clientInfo}>Cliente: {client?.name}</Text>

                    <KeyboardAvoidingView
                        behavior="padding"
                        style={styles.keyboardView}
                        keyboardVerticalOffset={Platform.OS === 'ios' ? 0 : 20}
                    >
                        <ScrollView 
                            showsVerticalScrollIndicator={false} 
                            style={styles.formScroll}
                            contentContainerStyle={styles.formScrollContent}
                            keyboardShouldPersistTaps="handled"
                        >
                            {/* Monto */}
                            <Text style={styles.label}>Monto del Crédito (C$)</Text>
                            <TextInput
                                style={styles.input}
                                placeholder="Ej: 4000"
                                keyboardType="numeric"
                                value={formData.amount}
                                onChangeText={(text) => setFormData({ ...formData, amount: text })}
                            />

                            {/* Tasa de Interés */}
                            <Text style={styles.label}>Tasa de Interés Mensual (%)</Text>
                            <TextInput
                                style={styles.input}
                                placeholder="Ej: 15"
                                keyboardType="numeric"
                                value={formData.interestRate}
                                onChangeText={(text) => setFormData({ ...formData, interestRate: text })}
                            />

                            {/* Plazo */}
                            <Text style={styles.label}>Plazo (meses)</Text>
                            <TextInput
                                style={styles.input}
                                placeholder="Ej: 3"
                                keyboardType="numeric"
                                value={formData.termMonths}
                                onChangeText={(text) => setFormData({ ...formData, termMonths: text })}
                            />

                            {/* Frecuencia de Pago */}
                            <Text style={styles.label}>Frecuencia de Pago</Text>
                            <View style={styles.pickerContainer}>
                                {PAYMENT_FREQUENCIES.map(freq => (
                                    <TouchableOpacity
                                        key={freq}
                                        style={[styles.pickerOption, formData.paymentFrequency === freq && styles.pickerOptionActive]}
                                        onPress={() => setFormData({ ...formData, paymentFrequency: freq })}
                                    >
                                        <Text style={[styles.pickerOptionText, formData.paymentFrequency === freq && styles.pickerOptionTextActive]}>
                                            {freq}
                                        </Text>
                                    </TouchableOpacity>
                                ))}
                            </View>

                            {/* Fecha de Primer Pago */}
                            <Text style={styles.label}>Fecha de Primer Pago</Text>
                            <TouchableOpacity 
                                style={styles.dateButton}
                                onPress={() => setShowDatePicker(true)}
                            >
                                <MaterialCommunityIcons name="calendar" size={20} color="#0ea5e9" />
                                <Text style={styles.dateButtonText}>{formatDate(formData.firstPaymentDate)}</Text>
                            </TouchableOpacity>

                            {showDatePicker && (
                                <DateTimePicker
                                    value={formData.firstPaymentDate}
                                    mode="date"
                                    display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                                    onChange={handleDateChange}
                                    minimumDate={new Date()}
                                />
                            )}
                        </ScrollView>
                    </KeyboardAvoidingView>

                    {/* Botón de submit */}
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
                                    <MaterialCommunityIcons name="check-circle" size={20} color="#fff" />
                                    <Text style={styles.submitButtonText}>Crear Solicitud</Text>
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
        height: '80%', 
        width: '100%' 
    },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
    modalTitle: { fontSize: 18, fontWeight: '800', color: '#1e293b' },
    clientInfo: { fontSize: 14, color: '#64748b', marginBottom: 16, fontWeight: '600' },
    keyboardView: { flex: 1 },
    formScroll: { flex: 1 },
    formScrollContent: { paddingBottom: 20 },
    label: { fontSize: 14, fontWeight: '700', color: '#334155', marginTop: 16, marginBottom: 8 },
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
    pickerContainer: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
    pickerOption: {
        paddingHorizontal: 12,
        paddingVertical: 8,
        borderRadius: 16,
        backgroundColor: '#f1f5f9',
        borderWidth: 1,
        borderColor: '#e2e8f0',
    },
    pickerOptionActive: {
        backgroundColor: '#0ea5e9',
        borderColor: '#0ea5e9',
    },
    pickerOptionText: { fontSize: 12, color: '#64748b', fontWeight: '600' },
    pickerOptionTextActive: { color: '#fff' },
    dateButton: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#f8fafc',
        borderRadius: 8,
        paddingHorizontal: 12,
        paddingVertical: 12,
        borderWidth: 1,
        borderColor: '#e2e8f0',
        gap: 8,
    },
    dateButtonText: {
        fontSize: 14,
        color: '#334155',
        fontWeight: '600',
    },
    buttonContainer: { 
         marginTop: 16, 
         paddingBottom: 10,
    },
    submitButton: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: '#10b981',
        paddingVertical: 14,
        borderRadius: 12,
        gap: 8,
    },
    submitButtonDisabled: { backgroundColor: '#94a3b8' },
    submitButtonText: { fontSize: 15, fontWeight: '700', color: '#fff' },
});
