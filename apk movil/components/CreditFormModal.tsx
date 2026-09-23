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

const LOAN_TYPES = [
    { label: 'Nuevo', value: '1' },
    { label: 'Représtamo', value: '2' },
    { label: 'Reactivación', value: '3' },
    { label: 'Reestructuración', value: '4' },
];

const DESTINATION_TYPES = [
    { label: 'Comercio', value: '1' },
    { label: 'Personales/Consumo', value: '2' },
    { label: 'Servicios', value: '3' },
    { label: 'Vivienda (Compra/Mejora)', value: '4' },
    { label: 'Construcción', value: '5' },
    { label: 'Industria', value: '6' },
    { label: 'Pesca', value: '7' },
    { label: 'Agropecuario', value: '8' },
    { label: 'Otros', value: '9' },
];

const WEEK_DAYS = [
    { label: 'Lun', value: '1' },
    { label: 'Mar', value: '2' },
    { label: 'Mié', value: '3' },
    { label: 'Jue', value: '4' },
    { label: 'Vie', value: '5' },
    { label: 'Sáb', value: '6' },
];

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
        interestRate: '15', // Tasa de interés mensual estándar por defecto (15%)
        termMonths: '3', // Plazo mensual estándar por defecto (3 meses)
        paymentFrequency: 'Semanal',
        firstPaymentDate: new Date(),
        tipoPrestamo: '1', // 1 = Nuevo por defecto
        tipoDestino: '2', // 2 = Personales/Consumo por defecto
        diaSemanaPreferido: '', // Día de la semana preferido
        diaPagoPreferido: '', // Día preferido del mes (1-31)
    });

    // Resetear formulario cuando se abre el modal
    useEffect(() => {
        if (visible) {
            // Calcular fecha de primer pago (7 días desde hoy)
            const nextWeek = new Date();
            nextWeek.setDate(nextWeek.getDate() + 7);
            
            // Si el cliente viene de Représtamos, sugerir Tipo de Préstamo como Représtamo (value: '2')
            const isReprestamoTab = client?.isReprestamo === true || (client?.activeCredits === 0 && client?.totalSaldo === 0);

            setFormData({
                amount: '',
                interestRate: '15',
                termMonths: '3',
                paymentFrequency: 'Semanal',
                firstPaymentDate: nextWeek,
                tipoPrestamo: isReprestamoTab ? '2' : '1',
                tipoDestino: '2',
                diaSemanaPreferido: '',
                diaPagoPreferido: '',
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

        // Validación de Día de la Semana para Semanal / Catorcenal
        if ((formData.paymentFrequency === 'Semanal' || formData.paymentFrequency === 'Catorcenal') && !formData.diaSemanaPreferido) {
            setAlert({
                visible: true,
                type: 'warning',
                title: 'Campo Requerido',
                message: 'Debes seleccionar el Día de la Semana pactado.',
            });
            return;
        }

        // Validación de Día Preferido para Quincenal
        if (formData.paymentFrequency === 'Quincenal') {
            const dayNum = parseInt(formData.diaPagoPreferido);
            if (!formData.diaPagoPreferido || isNaN(dayNum) || dayNum < 1 || dayNum > 31) {
                setAlert({
                    visible: true,
                    type: 'warning',
                    title: 'Día Inválido',
                    message: 'Debes ingresar un día de pago preferido válido (entre 1 y 31).',
                });
                return;
            }
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
                tipoPrestamo: formData.tipoPrestamo,
                tipoDestino: formData.tipoDestino,
                diaSemanaPreferido: formData.diaSemanaPreferido ? parseInt(formData.diaSemanaPreferido) : null,
                diaPagoPreferido: formData.diaPagoPreferido ? parseInt(formData.diaPagoPreferido) : null,
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
                            {/* Tipo de Préstamo */}
                            <Text style={styles.label}>Tipo de Préstamo</Text>
                            <View style={styles.pillContainer}>
                                {LOAN_TYPES.map(type => (
                                    <TouchableOpacity
                                        key={type.value}
                                        style={[styles.pillOption, formData.tipoPrestamo === type.value && styles.pillOptionActive]}
                                        onPress={() => setFormData({ ...formData, tipoPrestamo: type.value })}
                                    >
                                        <Text style={[styles.pillOptionText, formData.tipoPrestamo === type.value && styles.pillOptionTextActive]}>
                                            {type.label}
                                        </Text>
                                    </TouchableOpacity>
                                ))}
                            </View>

                            {/* Tipo de Destino */}
                            <Text style={styles.label}>Tipo de Destino</Text>
                            <View style={styles.gridContainer}>
                                {DESTINATION_TYPES.map(dest => (
                                    <TouchableOpacity
                                        key={dest.value}
                                        style={[styles.gridOption, formData.tipoDestino === dest.value && styles.gridOptionActive]}
                                        onPress={() => setFormData({ ...formData, tipoDestino: dest.value })}
                                    >
                                        <Text style={[styles.gridOptionText, formData.tipoDestino === dest.value && styles.gridOptionTextActive]}>
                                            {dest.label}
                                        </Text>
                                    </TouchableOpacity>
                                ))}
                            </View>

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
                                        onPress={() => {
                                            // Limpiar campos dependientes al cambiar frecuencia
                                            setFormData({ 
                                                ...formData, 
                                                paymentFrequency: freq,
                                                diaSemanaPreferido: '',
                                                diaPagoPreferido: '',
                                            });
                                        }}
                                    >
                                        <Text style={[styles.pickerOptionText, formData.paymentFrequency === freq && styles.pickerOptionTextActive]}>
                                            {freq}
                                        </Text>
                                    </TouchableOpacity>
                                ))}
                            </View>

                            {/* Día de la Semana (Solo para Semanal o Catorcenal) */}
                            {(formData.paymentFrequency === 'Semanal' || formData.paymentFrequency === 'Catorcenal') && (
                                <>
                                    <Text style={styles.label}>Día de la Semana pactado</Text>
                                    <View style={styles.pickerContainer}>
                                        {WEEK_DAYS.map(day => (
                                            <TouchableOpacity
                                                key={day.value}
                                                style={[styles.dayOption, formData.diaSemanaPreferido === day.value && styles.dayOptionActive]}
                                                onPress={() => setFormData({ ...formData, diaSemanaPreferido: day.value })}
                                            >
                                                <Text style={[styles.dayOptionText, formData.diaSemanaPreferido === day.value && styles.dayOptionTextActive]}>
                                                    {day.label}
                                                </Text>
                                            </TouchableOpacity>
                                        ))}
                                    </View>
                                </>
                            )}

                            {/* Día Preferido (Solo para Quincenal) */}
                            {formData.paymentFrequency === 'Quincenal' && (
                                <>
                                    <Text style={styles.label}>Día Preferido (Quincenal): Día del mes (1-31)</Text>
                                    <TextInput
                                        style={styles.input}
                                        placeholder="Ej: 15"
                                        keyboardType="numeric"
                                        maxLength={2}
                                        value={formData.diaPagoPreferido}
                                        onChangeText={(text) => {
                                            // Solo números
                                            const cleaned = text.replace(/[^0-9]/g, '');
                                            setFormData({ ...formData, diaPagoPreferido: cleaned });
                                        }}
                                    />
                                </>
                            )}

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
        height: '92%', 
        width: '100%' 
    },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
    modalTitle: { fontSize: 18, fontWeight: '800', color: '#1e293b' },
    clientInfo: { fontSize: 14, color: '#64748b', marginBottom: 16, fontWeight: '600' },
    keyboardView: { flex: 1 },
    formScroll: { flex: 1 },
    formScrollContent: { paddingBottom: 30 },
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
    pickerContainer: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginBottom: 4 },
    pickerOption: {
        paddingHorizontal: 14,
        paddingVertical: 10,
        borderRadius: 20,
        backgroundColor: '#f1f5f9',
        borderWidth: 1,
        borderColor: '#e2e8f0',
    },
    pickerOptionActive: {
        backgroundColor: '#0ea5e9',
        borderColor: '#0ea5e9',
    },
    pickerOptionText: { fontSize: 13, color: '#64748b', fontWeight: '700' },
    pickerOptionTextActive: { color: '#fff' },
    
    // Contenedor de píldoras para Tipo Préstamo
    pillContainer: { flexDirection: 'row', flexWrap: 'wrap', gap: 6 },
    pillOption: {
        flex: 1,
        minWidth: '45%',
        paddingVertical: 10,
        borderRadius: 10,
        backgroundColor: '#f8fafc',
        borderWidth: 1,
        borderColor: '#cbd5e1',
        alignItems: 'center',
        justifyContent: 'center',
    },
    pillOptionActive: {
        backgroundColor: '#0f172a',
        borderColor: '#0f172a',
    },
    pillOptionText: { fontSize: 13, color: '#475569', fontWeight: '600' },
    pillOptionTextActive: { color: '#fff', fontWeight: '700' },

    // Contenedor Grid para Tipo Destino
    gridContainer: { flexDirection: 'row', flexWrap: 'wrap', gap: 6 },
    gridOption: {
        width: '48%',
        paddingVertical: 10,
        paddingHorizontal: 8,
        borderRadius: 8,
        backgroundColor: '#f8fafc',
        borderWidth: 1,
        borderColor: '#cbd5e1',
        justifyContent: 'center',
    },
    gridOptionActive: {
        backgroundColor: '#0284c7',
        borderColor: '#0284c7',
    },
    gridOptionText: { fontSize: 12, color: '#475569', fontWeight: '600' },
    gridOptionTextActive: { color: '#fff', fontWeight: '700' },

    // Opciones del Día de la semana
    dayOption: {
        flex: 1,
        minWidth: '30%',
        paddingVertical: 10,
        borderRadius: 8,
        backgroundColor: '#f1f5f9',
        borderWidth: 1,
        borderColor: '#cbd5e1',
        alignItems: 'center',
    },
    dayOptionActive: {
        backgroundColor: '#f59e0b',
        borderColor: '#f59e0b',
    },
    dayOptionText: { fontSize: 13, color: '#475569', fontWeight: '600' },
    dayOptionTextActive: { color: '#fff', fontWeight: '700' },

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
         marginTop: 10, 
         paddingBottom: Platform.OS === 'ios' ? 20 : 10,
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
