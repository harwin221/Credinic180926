import { Stack } from 'expo-router';
import { AuthProvider, useAuth } from '../contexts/AuthContext';
import { AlertProvider } from '../components/AlertProvider';
import { useEffect } from 'react';
import { ActivityIndicator, View, Text, StyleSheet } from 'react-native';

// Descarga offline — flag de módulo para que no se repita al re-montar
let _offlineDownloaded = false;

function RootLayoutContent() {
  const { user, isLoading, isLoggingOut } = useAuth();

  // Descarga offline en background una sola vez al iniciar sesión
  useEffect(() => {
    if (user && !isLoading && !_offlineDownloaded) {
      _offlineDownloaded = true;
      import('../services/sync-service').then(({ downloadOfflineData }) => {
        downloadOfflineData()
          .then(r => console.log('[LAYOUT] Descarga offline:', r.message))
          .catch(e => console.warn('[LAYOUT] Error descarga offline:', e));
      });
    }
    if (!user) {
      _offlineDownloaded = false;
    }
  }, [user, isLoading]);

  if (isLoading || isLoggingOut) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={isLoggingOut ? '#e11d48' : '#0ea5e9'} />
        <Text style={[styles.loadingText, isLoggingOut && { color: '#e11d48', fontWeight: 'bold' }]}>
          {isLoggingOut ? 'Cerrando sesión de forma segura...' : 'Cargando sesión...'}
        </Text>
      </View>
    );
  }

  return (
    <Stack screenOptions={{ headerShown: false }} initialRouteName="index">
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
