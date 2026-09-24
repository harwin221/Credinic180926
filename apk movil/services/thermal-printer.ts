import { Platform, PermissionsAndroid, Alert, Linking } from 'react-native';
import { ReceiptData } from '../components/ReceiptModal';

// Función para limpiar texto y convertirlo a ASCII puro
// Esto evita que caracteres UTF-8 de 2 bytes (como ¡, á, é, í, ó, ú, ñ)
// sean interpretados como ideogramas chinos (GB2312) por impresoras térmicas Bluetooth
function cleanAscii(str: string): string {
    if (!str) return '';
    return str
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '') // Quita acentos (á->a, é->e, etc.)
        .replace(/¡/g, '!')
        .replace(/¿/g, '?')
        .replace(/ñ/g, 'n')
        .replace(/Ñ/g, 'N')
        .replace(/[^\x20-\x7E\n\r]/g, '') // Conserva solo caracteres ASCII imprimibles y saltos de línea
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

    /**
     * Inicializa la impresora BLE y solicita permisos si es necesario
     */
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

    /**
     * Inicializa la librería BLE
     */
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

    /**
     * Obtiene la lista de impresoras BLE disponibles
     */
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

    /**
     * Imprime un recibo usando BLE con formato idéntico a la web,
     * bordes limpios y sin caracteres extraños ni chinos.
     */
    async printReceipt(printerAddress: string, receipt: ReceiptData): Promise<void> {
        try {
            const BLEPrinter = await this.initPrinter();
            if (!BLEPrinter) {
                throw new Error('Módulo de impresora térmica no disponible en este dispositivo.');
            }

            console.log('[PRINT] Conectando a:', printerAddress);
            await BLEPrinter.connectPrinter(printerAddress);

            const fmt = (n: number) => {
                const val = (n || 0).toFixed(2);
                return val.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            };

            const LINE_WIDTH = 32;

            const center = (text: string, width = LINE_WIDTH) => {
                const clean = cleanAscii(text);
                const spaces = Math.max(0, width - clean.length);
                const left = Math.floor(spaces / 2);
                const right = spaces - left;
                return ' '.repeat(left) + clean + ' '.repeat(right);
            };

            const leftRight = (left: string, right: string, width = LINE_WIDTH) => {
                const cleanLeft = cleanAscii(left);
                const cleanRight = cleanAscii(right);
                const total = cleanLeft.length + cleanRight.length;
                if (total >= width) return cleanLeft + ' ' + cleanRight;
                const spaces = width - total;
                return cleanLeft + ' '.repeat(spaces) + cleanRight;
            };

            const borderLine = '+' + '-'.repeat(LINE_WIDTH - 2) + '+';
            const separator = '-'.repeat(LINE_WIDTH);
            const dottedSeparator = '.'.repeat(LINE_WIDTH);

            // Secuencia de inicialización ESC/POS:
            // 1. ESC @ (\x1b@): Reinicializar impresora
            // 2. FS . (\x1c.): Cancelar modo de caracteres chinos (Kanji mode OFF)
            // 3. ESC t 0 (\x1bt\x00): Seleccionar tabla de caracteres estándar (PC437 / USA)
            let receiptText = '\x1b@\x1c.\x1bt\x00';

            // Encabezado con marco superior tipo ticket
            receiptText += borderLine + '\n';
            receiptText += '|' + center('CREDINICA', LINE_WIDTH - 2) + '|\n';
            receiptText += '|' + center('RECIBO DE PAGO', LINE_WIDTH - 2) + '|\n';
            receiptText += borderLine + '\n';

            // Datos generales
            const todayStr = new Date().toLocaleDateString('es-NI');
            receiptText += leftRight('Fecha Impresion:', todayStr) + '\n';
            receiptText += separator + '\n';
            receiptText += leftRight('No. Recibo:', receipt.transactionNumber || '') + '\n';
            receiptText += leftRight('No. Credito:', receipt.creditNumber || '') + '\n';
            receiptText += leftRight('Fecha Pago:', receipt.paymentDate || '') + '\n';
            receiptText += separator + '\n';

            // Cliente
            receiptText += 'CLIENTE:\n';
            receiptText += cleanAscii(receipt.clientName || '').toUpperCase() + '\n';
            if (receipt.clientCode) {
                receiptText += leftRight('CODIGO:', receipt.clientCode) + '\n';
            }
            receiptText += separator + '\n';

            // Montos de Cuota y Mora
            receiptText += leftRight('Cuota del Dia:', 'C$ ' + fmt(receipt.cuotaDelDia)) + '\n';
            receiptText += leftRight('Mora / Atraso:', 'C$ ' + fmt(receipt.montoAtrasado)) + '\n';
            receiptText += leftRight('Dias Mora:', (receipt.diasMora ?? 0).toString()) + '\n';
            receiptText += dottedSeparator + '\n';
            receiptText += leftRight('Total a pagar:', 'C$ ' + fmt(receipt.totalAPagar)) + '\n';
            receiptText += separator + '\n';

            // Monto recibido destacado
            receiptText += center('MONTO RECIBIDO') + '\n';
            receiptText += center('C$ ' + fmt(receipt.amountPaid)) + '\n';

            const isCancel = (receipt as any).is_cancelacion || 
                ((receipt as any).concepto && (receipt as any).concepto.includes('CANCEL')) ||
                ((receipt.nuevoSaldo === 0 && (receipt.saldoAnterior || 0) > 0));

            const conceptStr = isCancel 
                ? 'CONCEPTO: CANCELACION DE CREDITO' 
                : ((receipt as any).concepto ? 'CONCEPTO: ' + cleanAscii((receipt as any).concepto).toUpperCase() : 'CONCEPTO: ABONO DE CREDITO');
            
            receiptText += center(conceptStr) + '\n';
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

            // Agente y Sucursal
            if (receipt.managedBy) {
                receiptText += leftRight('Agente:', cleanAscii(receipt.managedBy).toUpperCase()) + '\n';
            }
            if (receipt.sucursal) {
                receiptText += leftRight('Sucursal:', cleanAscii(receipt.sucursal).toUpperCase()) + '\n';
            }

            // Pie de ticket con borde
            receiptText += borderLine + '\n';

            // 4 saltos de línea para que el papel salga más allá de la cuchilla de corte
            receiptText += '\n\n\n\n';

            await BLEPrinter.printText(receiptText);
            console.log('[PRINT] Impresión finalizada correctamente sin caracteres chinos.');
        } catch (error: any) {
            console.error('[PRINT] Error de impresión:', error);
            throw new Error(error.message || 'Error de conexión con la impresora. Verifica que esté encendida y cerca.');
        }
    }
}

export const thermalPrinterService = new ThermalPrinterService();
