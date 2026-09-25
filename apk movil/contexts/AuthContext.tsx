import React, { createContext, useContext, useState, useEffect, useRef } from 'react';
import { AppState, AppStateStatus } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { sessionService, UserSession } from '../services/session';
import { router } from 'expo-router';
import { fullSync, checkConnection } from '../services/sync-service';
import { clearOfflineDatabase } from '../services/offline-db';

// Keys de AsyncStorage relacionadas con datos del día del agente
// Se limpian al hacer logout para que no contamine la siguiente sesión
const COBRADOS_HOY_PREFIX   = '@credinic_cobrados_hoy_';
const ABONOS_HOY_PREFIX     = '@credinic_abonos_individuales_';

const clearDailyAsyncStorageCache = async () => {
    try {
        const allKeys = await AsyncStorage.getAllKeys();
        const keysToRemove = allKeys.filter(
            k => k.startsWith(COBRADOS_HOY_PREFIX) || k.startsWith(ABONOS_HOY_PREFIX)
        );
        if (keysToRemove.length > 0) {
            await AsyncStorage.multiRemove(keysToRemove);
            console.log('[AUTH] Cache diario limpiado:', keysToRemove.length, 'keys eliminadas');
        }
    } catch (error) {
        console.error('[AUTH] Error limpiando cache diario:', error);
    }
};

interface AuthContextType {
    user: UserSession | null;
    isLoading: boolean;
    isLoggingOut: boolean;
    login: (user: UserSession) => Promise<void>;
    logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: React.ReactNode }) {
    const [user, setUser] = useState<UserSession | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const [isLoggingOut, setIsLoggingOut] = useState(false);

    // Control de auto-sync por AppState
    const appState      = useRef<AppStateStatus>(AppState.currentState);
    const wasSyncing    = useRef(false);

    useEffect(() => {
        loadSession();
        setupAutoLogout();
    }, []);

    // Auto-sync: se dispara cuando la app vuelve al primer plano Y hay conexión
    useEffect(() => {
        if (!user) return; // Solo si hay sesión activa

        const subscription = AppState.addEventListener('change', async (nextState: AppStateStatus) => {
            const comingToForeground =
                appState.current.match(/inactive|background/) && nextState === 'active';

            appState.current = nextState;

            if (comingToForeground && !wasSyncing.current) {
                wasSyncing.current = true;
                try {
                    const online = await checkConnection();
                    if (online) {
                        console.log('[AUTH] App al frente con conexión — sincronizando...');
                        await fullSync();
                    }
                } catch (e) {
                    console.error('[AUTH] Error en auto-sync al volver al frente:', e);
                } finally {
                    wasSyncing.current = false;
                }
            }
        });

        return () => subscription.remove();
    }, [user]);

    const loadSession = async () => {
        try {
            const session = await sessionService.getSession();
            setUser(session);
        } catch (error) {
            console.error('[AUTH] Error loading session:', error);
        } finally {
            setIsLoading(false);
        }
    };

    // Configurar cierre de sesión automático a las 00:00:00
    const setupAutoLogout = () => {
        const checkAndLogout = async () => {
            const now = new Date();
            const midnight = new Date();
            midnight.setHours(24, 0, 0, 0); // Próxima medianoche
            
            const timeUntilMidnight = midnight.getTime() - now.getTime();
            
            console.log('[AUTH] Auto-logout configurado para:', midnight.toLocaleString('es-NI'));
            
            setTimeout(async () => {
                console.log('[AUTH] Ejecutando cierre de sesión automático a las 00:00:00');
                const session = await sessionService.getSession();
                if (session) {
                    await logout();
                }
                // Reconfigurar para el siguiente día
                setupAutoLogout();
            }, timeUntilMidnight);
        };
        
        checkAndLogout();
    };

    const login = async (userData: UserSession) => {
        await sessionService.saveSession(userData);
        setUser(userData);
        // Disparar una sincronización completa después de un inicio de sesión exitoso
        // para asegurar que los datos offline estén frescos para el nuevo usuario.
        try {
            console.log('[AUTH] Disparando sincronización completa después del login...');
            await fullSync();
        } catch (error) {
            console.error('[AUTH] Error durante la sincronización inicial después del login:', error);
        }
    };

    const logout = async () => {
        console.log('[AUTH] Cerrando sesión (Modo Atómico)...');
        
        setIsLoggingOut(true);
        try {
            // 1. Limpiar sesión, DB offline y cache diario del AsyncStorage
            await sessionService.clearSession();
            await clearOfflineDatabase();
            await clearDailyAsyncStorageCache();
            
            // 2. Limpiar el estado de usuario
            setUser(null);

            // 3. Forzar navegación inmediata al login
            // Usamos un delay de 1.5s para que el usuario vea el mensaje de confirmación de seguridad
            setTimeout(() => {
                setIsLoggingOut(false);
                router.replace('/');
            }, 1500);
            
        } catch (error) {
            console.error('[AUTH] Fatal Logout Error:', error);
            setIsLoggingOut(false);
            setUser(null);
            router.replace('/');
        }
    };

    return (
        <AuthContext.Provider value={{ user, isLoading, isLoggingOut, login, logout }}>
            {children}
        </AuthContext.Provider>
    );
}

export function useAuth() {
    const context = useContext(AuthContext);
    if (context === undefined) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
}
