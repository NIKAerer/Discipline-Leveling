// Adresse de l'API : définie par VITE_API_URL (voir app/.env.example),
// avec localhost:8000 par défaut pour le développement.
export const API_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8000'

let unauthorizedHandler = null

// App.jsx enregistre ici ce qu'il faut faire quand le token est refusé
// (expiré ou invalide) : renvoyer l'utilisateur vers la page de connexion.
export function setUnauthorizedHandler(handler) {
    unauthorizedHandler = handler
}

/**
 * Point d'entrée unique pour appeler l'API.
 * - préfixe le chemin avec API_URL
 * - ajoute le token JWT s'il existe
 * - ajoute Content-Type: application/json quand il y a un body
 * - sur une réponse 401, supprime le token et prévient App.jsx
 */
export async function apiFetch(path, { headers = {}, ...options } = {}) {
    const token = localStorage.getItem('token')

    const response = await fetch(`${API_URL}${path}`, {
        ...options,
        headers: {
            ...(options.body ? { 'Content-Type': 'application/json' } : {}),
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
            ...headers,
        },
    })

    if (response.status === 401 && token) {
        localStorage.removeItem('token')
        unauthorizedHandler?.()
    }

    return response
}

export async function extractErrorMessage(response, fallback) {
    try {
        const data = await response.json()
        if (data && (data.error || data.message)) {
            return data.error || data.message
        }
    } catch {
        // Response body wasn't JSON (or was empty) — fall back to the generic message.
    }
    return fallback
}
