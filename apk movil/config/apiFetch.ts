import AsyncStorage from '@react-native-async-storage/async-storage';

const SESSION_KEY = '@credinic_user_session';

/**
 * Wrapper de fetch que agrega automáticamente el token Bearer
 * y los headers de JSON en todas las peticiones a la API.
 * 
 * Si el servidor responde con HTML en lugar de JSON (ej. página de error
 * o redirect de Laravel), convierte la respuesta en un JSON de error
 * estándar para evitar que el llamador falle al parsear.
 */
export async function apiFetch(url: string, options: RequestInit = {}): Promise<Response> {
    let token = '';

    try {
        const session = await AsyncStorage.getItem(SESSION_KEY);
        if (session) {
            const parsed = JSON.parse(session);
            token = parsed.token ?? '';
        }
    } catch (e) {
        console.warn('[apiFetch] No se pudo obtener el token de sesión');
    }

    const headers: Record<string, string> = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        ...(options.headers as Record<string, string> ?? {}),
    };

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await fetch(url, { ...options, headers });

    // Detectar si el servidor devolvió HTML en lugar de JSON
    // (ocurre cuando el token expiró y Laravel redirige al login,
    //  o cuando hay un error 500 que genera una página HTML)
    const contentType = response.headers.get('content-type') ?? '';
    if (!contentType.includes('application/json')) {
        console.warn(
            `[apiFetch] Respuesta no-JSON (${response.status}) desde ${url}. ` +
            `Content-Type: ${contentType}. El servidor puede haber devuelto HTML.`
        );

        // Construir una respuesta JSON sintética con el código HTTP real
        const errorBody = JSON.stringify({
            success: false,
            message: response.status === 401
                ? 'Sesión expirada. Por favor inicia sesión nuevamente.'
                : `Error del servidor (${response.status})`,
            html_response: true,
        });

        return new Response(errorBody, {
            status: response.status,
            headers: { 'Content-Type': 'application/json' },
        });
    }

    return response;
}
