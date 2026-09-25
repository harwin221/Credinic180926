import * as SQLite from 'expo-sqlite';

// Usar la nueva API de expo-sqlite (SDK 54+)
const db = SQLite.openDatabaseSync('credinica_offline.db');

// Función para inicializar las tablas de la base de datos offline
export const initOfflineDatabase = () => {
    try {
        // Crear tablas si no existen preservando los datos offline existentes
        db.execSync(
            `CREATE TABLE IF NOT EXISTS offline_clients (
                id TEXT PRIMARY KEY NOT NULL,
                clientNumber TEXT,
                name TEXT,
                cedula TEXT,
                phone TEXT,
                address TEXT,
                neighborhood TEXT,
                municipality TEXT,
                department TEXT,
                isNew INTEGER DEFAULT 0
            );`
        );
        db.execSync(
            `CREATE TABLE IF NOT EXISTS offline_credits (
                id TEXT PRIMARY KEY NOT NULL,
                creditNumber TEXT,
                clientName TEXT,
                clientId TEXT,
                amount REAL,
                remainingBalance REAL,
                dueTodayAmount REAL,
                overdueAmount REAL,
                lateDays INTEGER,
                paymentFrequency TEXT,
                collectionsManager TEXT,
                details TEXT,
                paymentPlan TEXT,
                totalCapital REAL DEFAULT 0,
                totalInteres REAL DEFAULT 0,
                promedioAtraso REAL DEFAULT 0,
                estado INTEGER DEFAULT 1,
                tipo_abono INTEGER DEFAULT 1,
                filas TEXT
            );`
        );
        db.execSync(
            `CREATE TABLE IF NOT EXISTS offline_abonos (
                id TEXT PRIMARY KEY NOT NULL,
                clientId TEXT NOT NULL,
                creditId TEXT,
                abonoId INTEGER,
                receiptNumber TEXT,
                monto REAL DEFAULT 0,
                fecha TEXT,
                detalle TEXT
            );`
        );
        db.execSync(
            `CREATE INDEX IF NOT EXISTS idx_offline_abonos_client ON offline_abonos (clientId);`
        );
        db.execSync(
            `CREATE INDEX IF NOT EXISTS idx_offline_credits_client ON offline_credits (clientId);`
        );
        db.execSync(
            `CREATE TABLE IF NOT EXISTS pending_payments (
                timestamp TEXT PRIMARY KEY NOT NULL,
                creditId TEXT NOT NULL,
                paymentData TEXT NOT NULL,
                userId TEXT NOT NULL
            );`
        );
        db.execSync(
            `CREATE TABLE IF NOT EXISTS pending_credits (
                timestamp INTEGER PRIMARY KEY NOT NULL,
                creditData TEXT NOT NULL,
                userId TEXT NOT NULL
            );`
        );
        db.execSync(
            `CREATE TABLE IF NOT EXISTS config (
                key TEXT PRIMARY KEY NOT NULL,
                value TEXT
            );`
        );

        migrateSchemaIfNeeded();
        addCreditColumnsIfNeeded();

        console.log('[DB] Base de datos inicializada correctamente');
    } catch (error) {
        console.error('[DB] Error inicializando base de datos:', error);
    }
};

/**
 * Migración de esquema.
 *
 * `pending_payments.timestamp` se creó como INTEGER, pero los pagos offline se
 * registran con un id tipo "OFFLINE-<epoch>" (PaymentModal.tsx), y SQLite lanza
 * 'datatype mismatch' al insertar ese string en una columna INTEGER.
 *
 * `CREATE TABLE IF NOT EXISTS` no altera tablas ya existentes, así que hay que
 * migrar explícitamente las bases instaladas en los dispositivos.
 */
// Declarada como `function` (no const) a propósito: `initOfflineDatabase()` se
// invoca a nivel de módulo, y solo las declaraciones de función se elevan.
function migrateSchemaIfNeeded() {
    try {
        const cols: any[] = db.getAllSync('PRAGMA table_info(pending_payments)');
        const tsCol = cols.find((c: any) => c.name === 'timestamp');

        if (!tsCol) return; // La tabla aún no existe; se creará con el esquema nuevo.
        if (String(tsCol.type).toUpperCase().includes('TEXT')) return; // Ya migrada.

        console.log('[DB] Migrando pending_payments.timestamp de INTEGER a TEXT...');
        db.execSync('PRAGMA foreign_keys = OFF;');
        db.execSync('BEGIN TRANSACTION;');
        db.execSync(
            `CREATE TABLE pending_payments_new (
                timestamp TEXT PRIMARY KEY NOT NULL,
                creditId TEXT NOT NULL,
                paymentData TEXT NOT NULL,
                userId TEXT NOT NULL
            );`
        );
        db.execSync(
            `INSERT INTO pending_payments_new (timestamp, creditId, paymentData, userId)
             SELECT CAST(timestamp AS TEXT), creditId, paymentData, userId
             FROM pending_payments;`
        );
        db.execSync('DROP TABLE pending_payments;');
        db.execSync('ALTER TABLE pending_payments_new RENAME TO pending_payments;');
        db.execSync('COMMIT;');
        console.log('[DB] Migración completada. Pagos offline habilitados.');
    } catch (error) {
        try { db.execSync('ROLLBACK;'); } catch (_) { /* sin transacción activa */ }
        console.error('[DB] Error en la migración de esquema:', error);
    }
}

/**
 * Añade las columnas de plan/abonos a offline_credits.
 * Las bases ya instaladas no tienen estas columnas porque
 * `CREATE TABLE IF NOT EXISTS` no modifica tablas existentes.
 */
function addCreditColumnsIfNeeded() {
    try {
        const cols: any[] = db.getAllSync('PRAGMA table_info(offline_credits)');
        if (!cols.length) return; // La tabla aún no existe.

        const have = (n: string) => cols.some((c: any) => c.name === n);
        const missing: Array<[string, string]> = [
            ['totalCapital',   'REAL DEFAULT 0'],
            ['totalInteres',    'REAL DEFAULT 0'],
            ['promedioAtraso',  'REAL DEFAULT 0'],
            ['estado',          'INTEGER DEFAULT 1'],
            ['tipo_abono',      'INTEGER DEFAULT 1'],
            ['filas',           'TEXT'],
        ];

        for (const [name, type] of missing) {
            if (have(name)) continue;
            try {
                db.execSync(`ALTER TABLE offline_credits ADD COLUMN ${name} ${type};`);
                console.log(`[DB] Columna ${name} añadida a offline_credits`);
            } catch (e) {
                console.error(`[DB] No se pudo añadir la columna ${name}:`, e);
            }
        }
    } catch (error) {
        console.error('[DB] Error añadiendo columnas a offline_credits:', error);
    }
}

// Función para limpiar todas las tablas de la base de datos offline
export const clearOfflineDatabase = async () => {
    return new Promise<void>((resolve, reject) => {
        try {
            const tables = [
                'offline_clients',
                'offline_credits',
                'offline_abonos',
                'pending_payments',
                'pending_credits',
                'config'
            ];
            
            tables.forEach(table => {
                try {
                    db.execSync(`DELETE FROM ${table};`);
                    console.log(`[DB] Tabla ${table} limpiada`);
                } catch (error) {
                    console.error(`[DB] Error limpiando ${table}:`, error);
                }
            });
            
            console.log('Base de datos offline limpiada exitosamente.');
            resolve();
        } catch (error) {
            console.error('Error durante la limpieza de la base de datos offline:', error);
            reject(error);
        }
    });
};

// Llamar a la inicialización de la base de datos al cargar el módulo
initOfflineDatabase();

// Guardar clientes offline
export const saveClientsOffline = async (clients: any[]) => {
    try {
        db.execSync('DELETE FROM offline_clients;');
        
        const stmt = db.prepareSync(
            'INSERT INTO offline_clients (id, clientNumber, name, cedula, phone, address, neighborhood, municipality, department, isNew) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        
        for (const client of clients) {
            stmt.executeSync([
                client.id,
                client.clientNumber || '',
                client.name || '',
                client.cedula || '',
                client.phone || '',
                client.address || '',
                client.neighborhood || '',
                client.municipality || '',
                client.department || '',
                client.isNew ? 1 : 0
            ]);
        }
        
        console.log(`[DB] ${clients.length} clientes guardados offline`);
    } catch (error) {
        console.error('[DB] Error guardando clientes:', error);
        throw error;
    }
};

// Guardar créditos offline
export const saveCreditsOffline = async (credits: any[]) => {
    try {
        db.execSync('DELETE FROM offline_credits;');
        
        const stmt = db.prepareSync(
            'INSERT INTO offline_credits (id, creditNumber, clientName, clientId, amount, remainingBalance, dueTodayAmount, overdueAmount, lateDays, paymentFrequency, collectionsManager, details, paymentPlan, totalCapital, totalInteres, promedioAtraso, estado, tipo_abono, filas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        for (const credit of credits) {
            stmt.executeSync([
                credit.id,
                credit.creditNumber || '',
                credit.clientName || '',
                credit.clientId || '',
                credit.amount || 0,
                credit.remainingBalance || 0,
                credit.dueTodayAmount || 0,
                credit.overdueAmount || 0,
                credit.lateDays || 0,
                credit.paymentFrequency || '',
                credit.collectionsManager || '',
                JSON.stringify(credit.details || {}),
                JSON.stringify(credit.paymentPlan || []),
                credit.totalCapital || 0,
                credit.totalInteres || 0,
                credit.promedioAtraso || 0,
                credit.estado ?? 1,
                credit.tipo_abono ?? 1,
                JSON.stringify(credit.filas || []),
            ]);
        }

        console.log(`[DB] ${credits.length} créditos guardados offline`);
    } catch (error) {
        console.error('[DB] Error guardando créditos:', error);
        throw error;
    }
};

// Guardar abonos históricos para poder reimprimir recibos sin señal
export const saveAbonosOffline = async (abonos: any[]) => {
    try {
        db.execSync('DELETE FROM offline_abonos;');

        const stmt = db.prepareSync(
            'INSERT INTO offline_abonos (id, clientId, creditId, abonoId, receiptNumber, monto, fecha, detalle) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );

        for (const a of abonos) {
            stmt.executeSync([
                a.id,
                a.clientId || '',
                a.creditId || '',
                a.abonoId ?? null,
                a.receiptNumber || '',
                a.monto || 0,
                a.fecha || '',
                JSON.stringify(a.detalle || {}),
            ]);
        }

        console.log(`[DB] ${abonos.length} abonos guardados offline`);
    } catch (error) {
        console.error('[DB] Error guardando abonos:', error);
        throw error;
    }
};

// ─── Lecturas para modo offline ─────────────────────────────────────────────

const safeParse = (raw: any, fallback: any) => {
    try {
        return raw ? JSON.parse(raw) : fallback;
    } catch {
        return fallback;
    }
};

export const getOfflineClients = async (): Promise<any[]> => {
    try {
        return db.getAllSync('SELECT * FROM offline_clients');
    } catch (error) {
        console.error('[DB] Error leyendo clientes offline:', error);
        return [];
    }
};

export const getOfflineCredits = async (clientId?: string): Promise<any[]> => {
    try {
        const rows = clientId
            ? db.getAllSync('SELECT * FROM offline_credits WHERE clientId = ?', [String(clientId)])
            : db.getAllSync('SELECT * FROM offline_credits');

        return rows.map((r: any) => ({
            id: r.id,
            creditNumber: r.creditNumber,
            clientName: r.clientName,
            clientId: r.clientId,
            amount: r.amount,
            remainingBalance: r.remainingBalance,
            dueTodayAmount: r.dueTodayAmount,
            overdueAmount: r.overdueAmount,
            lateDays: r.lateDays,
            paymentFrequency: r.paymentFrequency,
            collectionsManager: r.collectionsManager,
            details: safeParse(r.details, {}),
            paymentPlan: safeParse(r.paymentPlan, []),
            filas: safeParse(r.filas, []),
            totalCapital: r.totalCapital,
            totalInteres: r.totalInteres,
            promedioAtraso: r.promedioAtraso,
            estado: r.estado,
            tipo_abono: r.tipo_abono,
        }));
    } catch (error) {
        console.error('[DB] Error leyendo créditos offline:', error);
        return [];
    }
};

export const getOfflineAbonos = async (clientId: string): Promise<any[]> => {
    try {
        const rows = db.getAllSync(
            'SELECT * FROM offline_abonos WHERE clientId = ? ORDER BY fecha DESC',
            [String(clientId)]
        );
        return rows.map((r: any) => ({
            id: r.id,
            clientId: r.clientId,
            creditId: r.creditId,
            abonoId: r.abonoId,
            receiptNumber: r.receiptNumber,
            monto: r.monto,
            fecha: r.fecha,
            detalle: safeParse(r.detalle, {}),
        }));
    } catch (error) {
        console.error('[DB] Error leyendo abonos offline:', error);
        return [];
    }
};

// Obtener pagos pendientes
export const getPendingPayments = async (): Promise<any[]> => {
    try {
        const result = db.getAllSync('SELECT * FROM pending_payments ORDER BY timestamp ASC');
        return result.map((row: any) => ({
            id: row.timestamp,
            creditId: row.creditId,
            ...JSON.parse(row.paymentData),
            managedBy: row.userId
        }));
    } catch (error) {
        console.error('[DB] Error obteniendo pagos pendientes:', error);
        return [];
    }
};

// Marcar pago como sincronizado
export const markPaymentAsSynced = async (timestamp: string | number) => {
    try {
        db.runSync('DELETE FROM pending_payments WHERE timestamp = ?', [timestamp]);
        console.log(`[DB] Pago ${timestamp} marcado como sincronizado`);
    } catch (error) {
        console.error('[DB] Error marcando pago como sincronizado:', error);
        throw error;
    }
};

// Obtener créditos pendientes
export const getPendingCredits = async (): Promise<any[]> => {
    try {
        const result = db.getAllSync('SELECT * FROM pending_credits ORDER BY timestamp ASC');
        return result.map((row: any) => ({
            id: row.timestamp,
            data: JSON.parse(row.creditData),
            userId: row.userId
        }));
    } catch (error) {
        console.error('[DB] Error obteniendo créditos pendientes:', error);
        return [];
    }
};

// Marcar crédito como sincronizado
export const markCreditAsSynced = async (timestamp: string | number) => {
    try {
        db.runSync('DELETE FROM pending_credits WHERE timestamp = ?', [timestamp]);
        console.log(`[DB] Crédito ${timestamp} marcado como sincronizado`);
    } catch (error) {
        console.error('[DB] Error marcando crédito como sincronizado:', error);
        throw error;
    }
};

// Guardar configuración
export const setConfig = async (key: string, value: string) => {
    try {
        db.runSync(
            'INSERT OR REPLACE INTO config (key, value) VALUES (?, ?)',
            [key, value]
        );
        console.log(`[DB] Config ${key} guardada`);
    } catch (error) {
        console.error('[DB] Error guardando config:', error);
        throw error;
    }
};

// Obtener configuración
export const getConfig = async (key: string): Promise<string | null> => {
    try {
        const result = db.getFirstSync('SELECT value FROM config WHERE key = ?', [key]);
        return result ? (result as any).value : null;
    } catch (error) {
        console.error('[DB] Error obteniendo config:', error);
        return null;
    }
};

// Guardar pago pendiente
export const savePendingPayment = async (creditId: string, paymentData: any, userId: string, id?: string | number) => {
    try {
        // Permite conservar el identificador OFFLINE mostrado en el recibo y
        // usado por las vistas locales. Antes, SQLite generaba otro timestamp
        // y el pago no podía relacionarse al limpiar o sincronizar.
        const timestamp = id ?? `OFFLINE-${Date.now()}`;
        db.runSync(
            'INSERT INTO pending_payments (timestamp, creditId, paymentData, userId) VALUES (?, ?, ?, ?)',
            [timestamp, creditId, JSON.stringify(paymentData), userId]
        );
        console.log(`[DB] Pago pendiente guardado: ${timestamp}`);
        return String(timestamp);
    } catch (error) {
        console.error('[DB] Error guardando pago pendiente:', error);
        throw error;
    }
};

// Guardar crédito pendiente
export const savePendingCredit = async (creditData: any, userId: string) => {
    try {
        const timestamp = Date.now();
        db.runSync(
            'INSERT INTO pending_credits (timestamp, creditData, userId) VALUES (?, ?, ?)',
            [timestamp, JSON.stringify(creditData), userId]
        );
        console.log(`[DB] Crédito pendiente guardado: ${timestamp}`);
        return timestamp;
    } catch (error) {
        console.error('[DB] Error guardando crédito pendiente:', error);
        throw error;
    }
};

// Obtener estadísticas offline
export const getOfflineStats = async () => {
    try {
        const pendingPaymentsResult = db.getFirstSync('SELECT COUNT(*) as count FROM pending_payments');
        const pendingCreditsResult = db.getFirstSync('SELECT COUNT(*) as count FROM pending_credits');
        const offlineClientsResult = db.getFirstSync('SELECT COUNT(*) as count FROM offline_clients');
        const offlineCreditsResult = db.getFirstSync('SELECT COUNT(*) as count FROM offline_credits');
        
        return {
            pendingPayments: (pendingPaymentsResult as any)?.count || 0,
            pendingCredits: (pendingCreditsResult as any)?.count || 0,
            offlineClients: (offlineClientsResult as any)?.count || 0,
            offlineCredits: (offlineCreditsResult as any)?.count || 0
        };
    } catch (error) {
        console.error('[DB] Error obteniendo estadísticas:', error);
        return {
            pendingPayments: 0,
            pendingCredits: 0,
            offlineClients: 0,
            offlineCredits: 0
        };
    }
};
