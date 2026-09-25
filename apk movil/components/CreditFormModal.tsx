import { View, Text, StyleSheet, Modal, TouchableOpacity, ScrollView, TextInput, ActivityIndicator, Platform, KeyboardAvoidingView } from 'react-native';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useState, useEffect } from 'react';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
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
    { label: 'Vivienda (Compra, Mejora, Ampliación, Remodelación, Otros)', value: '4' },
    { label: 'Construcción', value: '5' },
    { label: 'Industria', value: '6' },
    { label: 'Pesca', value: '7' },
    { label: 'Agricultura y Ganadería', value: '8' },
    { label: 'Otros', value: '9' },
];

const WEEK_DAYS = [
    { label: 'Lunes', value: '1' },
    { label: 'Martes', value: '2' },
    { label: 'Miércoles', value: '3' },
    { label: 'Jueves', value: '4' },
    { label: 'Viernes', value: '5' },
    { label: 'Sábado', value: '6' },
];

interface DropdownOption {
    label: string;
    value: string;
}

interface DropdownSelectorProps {
    label: string;
    options: DropdownOption[];
    selectedValue: string;
    onSelect: (value: string) => void;
    placeholder?: string;
}

function DropdownSelector({ label, options, selectedValue, onSelect, placeholder = 'Seleccione...' }: DropdownSelectorProps) {
    const [isOpen, setIsOpen] = useState(false);
    const selectedOption = options.find(opt => opt.value === selectedValue);

    return (
        <View style={{ marginBottom: 14 }}>
            <Text style={styles.label}>{label}</Text>
            <TouchableOpacity 
                style={styles.dropdownButton} 
                onPress={() => setIsOpen(true)}
            >
                <Text style={[styles.dropdownButtonText, !selectedOption && { color: '#94a3b8' }]}>
                    {selectedOption ? selectedOption.label : placeholder}
                </Text>
                <MaterialCommunityIcons name="chevron-down" size={20} color="#64748b" />
            </TouchableOpacity>

            <Modal
                visible={isOpen}
                transparent={true}
                animationType="fade"
                onRequestClose={() => setIsOpen(false)}
            >
                <TouchableOpacity 
                    style={styles.dropdownOverlay} 
                    activeOpacity={1} 
                    onPress={() => setIsOpen(false)}
                >
                    <View style={styles.dropdownModalContainer}>
                        <View style={styles.dropdownHeader}>
                            <Text style={styles.dropdownTitle}>Seleccione {label}</Text>
                            <TouchableOpacity onPress={() => setIsOpen(false)}>
                                <MaterialCommunityIcons name="close" size={20} color="#64748b" />
                            </TouchableOpacity>
                        </View>
                        <ScrollView style={styles.dropdownList} keyboardShouldPersistTaps="handled">
                            {options.map(opt => (
                                <TouchableOpacity
                                    key={opt.value}
                                    style={[
                                        styles.dropdownOption,
                                        opt.value === selectedValue && styles.dropdownOptionActive
                                    ]}
                                    onPress={() => {
                                        onSelect(opt.value);
                                        setIsOpen(false);
                                    }}
                                >
                                    <Text style={[
                                        styles.dropdownOptionText,
                                        opt.value === selectedValue && styles.dropdownOptionTextActive
                                    ]}>
                                        {opt.label}
                                    </Text>
                                    {opt.value === selectedValue && (
                                        <MaterialCommunityIcons name="check" size={18} color="#0ea5e9" />
                                    )}
                                </TouchableOpacity>
                            ))}
                        </ScrollView>
                    </View>
                </TouchableOpacity>
            </Modal>
        </View>
    );
}

export default function CreditFormModal({ visible, onClose, client, onSuccess }: CreditFormModalProps) {
    const insets = useSafeAreaInsets();
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
                            {/* Tipo de Préstamo - Dropdown */}
                            <DropdownSelector
                                label="Tipo de Préstamo"
                                options={LOAN_TYPES}
                                selectedValue={formData.tipoPrestamo}
                                onSelect={(val) => setFormData({ ...formData, tipoPrestamo: val })}
                                placeholder="Seleccione Tipo de Préstamo"
                            />

                            {/* Tipo de Destino - Dropdown */}
                            <DropdownSelector
                                label="Tipo de Destino"
                                options={DESTINATION_TYPES}
                                selectedValue={formData.tipoDestino}
                                onSelect={(val) => setFormData({ ...formData, tipoDestino: val })}
                                placeholder="Seleccione Tipo de Destino"
                            />

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

                            {/* Día de la Semana Pactado (Solo para Semanal o Catorcenal) - Dropdown */}
                            {(formData.paymentFrequency === 'Semanal' || formData.paymentFrequency === 'Catorcenal') && (
                                <DropdownSelector
                                    label="Día de la Semana pactado"
                                    options={WEEK_DAYS}
                                    selectedValue={formData.diaSemanaPreferido}
                                    onSelect={(val) => setFormData({ ...formData, diaSemanaPreferido: val })}
                                    placeholder="Seleccione Día de la Semana"
                                />
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
                    <View style={[styles.buttonContainer, { paddingBottom: Math.max(insets.bottom, 24) + 8 }]}>
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
    pickerContainer: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginBottom: 14 },
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

    // Estilos del Dropdown Selector (Menú desplegable)
    dropdownButton: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        backgroundColor: '#f8fafc',
        borderRadius: 10,
        paddingHorizontal: 14,
        paddingVertical: 12,
        borderWidth: 1,
        borderColor: '#e2e8f0',
        minHeight: 48,
    },
    dropdownButtonText: {
        fontSize: 15,
        color: '#334155',
        fontWeight: '600',
    },
    dropdownOverlay: {
        flex: 1,
        backgroundColor: 'rgba(0,0,0,0.5)',
        justifyContent: 'center',
        alignItems: 'center',
        padding: 20,
    },
    dropdownModalContainer: {
        backgroundColor: '#fff',
        borderRadius: 16,
        width: '100%',
        maxHeight: '75%',
        padding: 18,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.2,
        shadowRadius: 10,
        elevation: 6,
    },
    dropdownHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        borderBottomWidth: 1,
        borderBottomColor: '#f1f5f9',
        paddingBottom: 12,
        marginBottom: 12,
    },
    dropdownTitle: {
        fontSize: 15,
        fontWeight: '800',
        color: '#0f172a',
        textTransform: 'uppercase',
    },
    dropdownList: {
        maxHeight: 350,
    },
    dropdownOption: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingVertical: 14,
        paddingHorizontal: 12,
        borderRadius: 10,
        marginBottom: 6,
        backgroundColor: '#f8fafc',
        borderWidth: 1,
        borderColor: '#f1f5f9',
    },
    dropdownOptionActive: {
        backgroundColor: '#ecfafd',
        borderColor: '#bae6fd',
    },
    dropdownOptionText: {
        fontSize: 14,
        color: '#334155',
        fontWeight: '600',
    },
    dropdownOptionTextActive: {
        color: '#0369a1',
        fontWeight: '800',
    },

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
