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
