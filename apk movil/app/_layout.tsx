import { Stack, router, useSegments } from 'expo-router';
import { AuthProvider, useAuth } from '../contexts/AuthContext';
import { AlertProvider } from '../components/AlertProvider';
import { useEffect, useRef } from 'react';
import { ActivityIndicator, View, Text, StyleSheet } from 'react-native';

function RootLayoutContent() {
  const { user, isLoading, isLoggingOut } = useAuth();
  const segments = useSegments() as string[];
  const hasDownloadedRef = useRef(false);

  const inAuthGroup = segments.length === 0 || segments[0] === 'index';

  useEffect(() => {
    if (!isLoading && !isLoggingOut) {
      if (!user && !inAuthGroup) {
        hasDownloadedRef.current = false; // reset al hacer logout
        router.replace('/');
      } else if (user && inAuthGroup) {
        const roleUpper = user.role.toUpperCase();
        const isManager = ['GERENTE', 'ADMINISTRADOR', 'FINANZAS', 'ADMINISTRATIVO'].includes(roleUpper);
        
        console.log('[LAYOUT] Usuario logueado, redirigiendo...', { role: user.role, isManager });
        
        if (isManager) {
          router.replace('/(manager-tabs)/index' as any);
        } else {
          router.replace('/(tabs)/index' as any);
        }
      }
    }
  }, [user, isLoading, isLoggingOut, inAuthGroup]);

  // Descarga offline en background al iniciar sesión (una sola vez por sesión)
  useEffect(() => {
    if (user && !isLoading && !hasDownloadedRef.current) {
      hasDownloadedRef.current = true;
      // No bloqueamos la UI — fire and forget
      import('../services/sync-service').then(({ downloadOfflineData }) => {
        downloadOfflineData()
          .then(r => console.log('[LAYOUT] Descarga offline inicial:', r.message))
          .catch(e => console.warn('[LAYOUT] Error descarga offline inicial:', e));
      });
    }
  }, [user, isLoading]);

  if (isLoading || isLoggingOut) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={isLoggingOut ? "#e11d48" : "#0ea5e9"} />
        <Text style={[styles.loadingText, isLoggingOut && { color: '#e11d48', fontWeight: 'bold' }]}>
          {isLoggingOut ? "Cerrando sesión de forma segura..." : "Cargando sesión..."}
        </Text>
      </View>
    );
  }

  return (
    <Stack screenOptions={{ headerShown: false }}>
      <Stack.Screen name="index" options={{ headerShown: false }} />
      <Stack.Screen name="(tabs)" options={{ headerShown: false }} />
      <Stack.Screen name="(manager-tabs)" options={{ headerShown: false }} />
    </Stack>
  );
}

export default function RootLayout() {
  return (
    <AuthProvider>
      <AlertProvider>
        <RootLayoutContent />
      </AlertProvider>
    </AuthProvider>
  );
}

const styles = StyleSheet.create({
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#ffffff',
  },
  loadingText: {
    marginTop: 10,
    fontSize: 16,
    color: '#64748b',
  },
});