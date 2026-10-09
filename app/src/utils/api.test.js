import { describe, expect, it } from 'vitest'
import { extractErrorMessage } from './api'

describe('extractErrorMessage', () => {
    it("renvoie le message d'erreur de l'API", async () => {
        const response = new Response(JSON.stringify({ error: 'Email déjà utilisé' }))

        expect(await extractErrorMessage(response, 'Erreur')).toBe('Email déjà utilisé')
    })

    it("renvoie le message par défaut si la réponse n'est pas du JSON", async () => {
        const response = new Response('<html>500</html>')

        expect(await extractErrorMessage(response, 'Erreur serveur')).toBe('Erreur serveur')
    })
})
