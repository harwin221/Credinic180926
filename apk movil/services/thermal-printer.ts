import { Platform, PermissionsAndroid, Alert, Linking } from 'react-native';
import { ReceiptData } from '../components/ReceiptModal';

// Función para limpiar texto y convertirlo a ASCII puro
function cleanAscii(str: string): string {
    if (!str) return '';
    return str
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/¡/g, '!')
        .replace(/¿/g, '?')
        .replace(/ñ/g, 'n')
        .replace(/Ñ/g, 'N')
        .replace(/[^\x20-\x7E\n\r]/g, '')
        .trim();
}

class ThermalPrinterService {
    private initialized = false;

    private getBLEPrinterInstance(): any {
        try {
            const module = require('react-native-thermal-receipt-printer');
            return module.BLEPrinter || module.default?.BLEPrinter || null;
        } catch (error) {
            console.warn('[PRINT] Error al cargar el módulo BLEPrinter:', error);
            return null;
        }
    }

    async requestBluetoothPermissions(): Promise<boolean> {
        if (Platform.OS !== 'android') return true;

        try {
            const apiLevel = Platform.Version;

            if (typeof apiLevel === 'number' && apiLevel >= 31) {
                const granted = await PermissionsAndroid.requestMultiple([
                    'android.permission.BLUETOOTH_SCAN' as any,
                    'android.permission.BLUETOOTH_CONNECT' as any,
                    'android.permission.ACCESS_FINE_LOCATION' as any,
                ]);

                const isOk = (
                    (granted as any)['android.permission.BLUETOOTH_SCAN'] === PermissionsAndroid.RESULTS.GRANTED &&
                    (granted as any)['android.permission.BLUETOOTH_CONNECT'] === PermissionsAndroid.RESULTS.GRANTED
                );

                if (!isOk) {
                    Alert.alert(
                        "Permisos Necesarios",
                        "Para conectar la impresora, activa el permiso de 'Dispositivos Cercanos'.",
                        [{ text: "Abrir Ajustes", onPress: () => Linking.openSettings() }, { text: "OK" }]
                    );
                }
                return isOk;
            } else {
                const granted = await PermissionsAndroid.requestMultiple([
                    'android.permission.BLUETOOTH_ADMIN' as any,
                    PermissionsAndroid.PERMISSIONS.ACCESS_FINE_LOCATION,
                ]);
                return (granted as any)[PermissionsAndroid.PERMISSIONS.ACCESS_FINE_LOCATION] === PermissionsAndroid.RESULTS.GRANTED;
            }
        } catch (error) {
            console.error('[PRINT] Permission Error:', error);
            return false;
        }
    }

    async initPrinter(): Promise<any> {
        const BLEPrinter = this.getBLEPrinterInstance();
        if (!BLEPrinter) {
            throw new Error('La función de impresión Bluetooth requiere ejecutar la APK compilada con módulos nativos.');
        }

        if (this.initialized) return BLEPrinter;

        const hasPermissions = await this.requestBluetoothPermissions();
        if (!hasPermissions) throw new Error('Permisos de Bluetooth no concedidos');

        await BLEPrinter.init();
        this.initialized = true;
        console.log('[PRINT] BLE Printer initialized');
        return BLEPrinter;
    }

    async findPrinters(): Promise<any[]> {
        try {
            const BLEPrinter = await this.initPrinter();
            if (!BLEPrinter) return [];

            console.log('[PRINT] Buscando impresoras BLE...');
            const devices = await BLEPrinter.getDeviceList();

            return (devices || []).map((d: any) => ({
                name: d.device_name || 'Impresora BT',
                address: d.inner_mac_address,
                info: 'BLE',
                isNative: true
            }));
        } catch (error: any) {
            console.error('[PRINT] Error listando impresoras:', error);
            Alert.alert('Impresora Bluetooth', error.message || 'No se pudieron buscar impresoras Bluetooth.');
            return [];
        }
    }

    async printReceipt(printerAddress: string, receipt: ReceiptData): Promise<void> {
        try {
            const BLEPrinter = await this.initPrinter();
            if (!BLEPrinter) {
                throw new Error('Módulo de impresora térmica no disponible en este dispositivo.');
            }

            console.log('[PRINT] Conectando a:', printerAddress);
            if (typeof BLEPrinter.connectPrinter === 'function') {
                await BLEPrinter.connectPrinter(printerAddress);
            }

            const fmt = (n: any) => {
                const num = parseFloat(n) || 0;
                return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            };

            const LINE_WIDTH = 32;

            const center = (text: string, width = LINE_WIDTH) => {
                const clean = cleanAscii(String(text || ''));
                const spaces = Math.max(0, width - clean.length);
                const left = Math.floor(spaces / 2);
                const right = spaces - left;
                return ' '.repeat(left) + clean + ' '.repeat(right);
            };

            const leftRight = (left: string, right: string, width = LINE_WIDTH) => {
                const cleanLeft = cleanAscii(String(left || ''));
                const cleanRight = cleanAscii(String(right || ''));
                const total = cleanLeft.length + cleanRight.length;
                if (total >= width) return cleanLeft + ' ' + cleanRight;
                const spaces = width - total;
                return cleanLeft + ' '.repeat(spaces) + cleanRight;
            };

            const borderLine = '+' + '-'.repeat(LINE_WIDTH - 2) + '+';
            const separator = '-'.repeat(LINE_WIDTH);
            const dottedSeparator = '.'.repeat(LINE_WIDTH);

            // ESC @ reinicializar, FS . cancelar modo chino, ESC t 0 tabla ASCII estándar
            let receiptText = '\x1b@\x1c.\x1bt\x00';

            // ── Encabezado: solo CREDINICA (sin "RECIBO DE PAGO") ──
            receiptText += borderLine + '\n';
            receiptText += '|' + center('CREDINICA', LINE_WIDTH - 2) + '|\n';
            receiptText += borderLine + '\n';

            // Fecha impresión
            const todayStr = new Date().toLocaleDateString('es-NI');
            receiptText += center('Fecha Impresion: ' + todayStr) + '\n';
            receiptText += separator + '\n';

            // Datos del recibo
            receiptText += leftRight('No. Recibo:', String(receipt.transactionNumber || '')) + '\n';
            receiptText += leftRight('No. Credito:', String(receipt.creditNumber || '')) + '\n';
            receiptText += leftRight('Fecha Pago:', String(receipt.paymentDate || '')) + '\n';
            receiptText += separator + '\n';

            // Cliente centrado, sin código
            receiptText += center('CLIENTE:') + '\n';
            receiptText += center(cleanAscii(String(receipt.clientName || '')).toUpperCase()) + '\n';
            receiptText += separator + '\n';

            // Montos
            receiptText += leftRight('Cuota del Dia:', 'C$ ' + fmt(receipt.cuotaDelDia)) + '\n';
            receiptText += leftRight('Mora / Atraso:', 'C$ ' + fmt(receipt.montoAtrasado)) + '\n';
            receiptText += leftRight('Dias Mora:', String(receipt.diasMora ?? 0)) + '\n';
            receiptText += dottedSeparator + '\n';
            receiptText += leftRight('Total a pagar:', 'C$ ' + fmt(receipt.totalAPagar)) + '\n';
            receiptText += separator + '\n';

            // Monto recibido — más grande con doble espacio arriba/abajo
            receiptText += center('MONTO RECIBIDO') + '\n';
            receiptText += '\n';
            receiptText += center('C$ ' + fmt(receipt.amountPaid)) + '\n';
            receiptText += '\n';

            const isCancel = (receipt as any).is_cancelacion ||
                ((receipt as any).concepto && String((receipt as any).concepto).includes('CANCEL')) ||
                (Number(receipt.nuevoSaldo) === 0 && Number(receipt.saldoAnterior || 0) > 0);

            // Concepto — siempre visible igual que el recibo web
            if (isCancel) {
                receiptText += center('CONCEPTO: CANCELACION DE CREDITO') + '\n';
            } else {
                receiptText += center('CONCEPTO: ABONO DE CREDITO') + '\n';
            }

            receiptText += separator + '\n';

            // Saldos
            receiptText += leftRight('Saldo Anterior:', 'C$ ' + fmt(receipt.saldoAnterior)) + '\n';
            receiptText += leftRight('Nuevo Saldo:', 'C$ ' + fmt(receipt.nuevoSaldo)) + '\n';

            if (isCancel) {
                receiptText += separator + '\n';
                receiptText += center('*** CREDITO CANCELADO ***') + '\n';
            }

            receiptText += separator + '\n';
            receiptText += center('!GRACIAS POR SU PAGO!') + '\n';
            receiptText += center('CONSERVE ESTE DOCUMENTO') + '\n';
            receiptText += separator + '\n';

            // Solo agente, sin sucursal
            if (receipt.managedBy) {
                receiptText += center('Agente: ' + cleanAscii(String(receipt.managedBy)).toUpperCase()) + '\n';
            }

            receiptText += borderLine + '\n';

            if (typeof BLEPrinter.printText === 'function') {
                await BLEPrinter.printText(receiptText);
            } else if (typeof BLEPrinter.printBill === 'function') {
                await BLEPrinter.printBill(receiptText);
            } else {
                throw new Error('El módulo BLEPrinter no tiene método de impresión disponible.');
            }
            console.log('[PRINT] Impresión finalizada correctamente.');
        } catch (error: any) {
            console.error('[PRINT] Error de impresión:', error);
            throw new Error(error.message || 'Error de conexión con la impresora. Verifica que esté encendida y cerca.');
        }
    }
}

export const thermalPrinterService = new ThermalPrinterService();
