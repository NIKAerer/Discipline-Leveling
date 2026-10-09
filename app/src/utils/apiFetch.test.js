import { afterEach, describe, expect, it, vi } from 'vitest'
import { API_URL, apiFetch, setUnauthorizedHandler } from './api'

function mockFetch(status) {
    const fetchMock = vi.fn().mockResolvedValue(new Response('{}', { status }))
    vi.stubGlobal('fetch', fetchMock)
    return fetchMock
}

describe('apiFetch', () => {
    afterEach(() => {
        vi.unstubAllGlobals()
        setUnauthorizedHandler(null)
    })

    it("préfixe l'URL et ajoute le token JWT", async () => {
        localStorage.setItem('token', 'abc')
        const fetchMock = mockFetch(200)

        await apiFetch('/api/dashboard')

        const [url, options] = fetchMock.mock.calls[0]
        expect(url).toBe(`${API_URL}/api/dashboard`)
        expect(options.headers.Authorization).toBe('Bearer abc')
    })

    it('ajoute le Content-Type JSON quand il y a un body', async () => {
        const fetchMock = mockFetch(201)

        await apiFetch('/api/register', { method: 'POST', body: JSON.stringify({ name: 'Nika' }) })

        const [, options] = fetchMock.mock.calls[0]
        expect(options.headers['Content-Type']).toBe('application/json')
        expect(options.headers.Authorization).toBeUndefined()
    })

    it('supprime le token et prévient App.jsx sur une réponse 401', async () => {
        localStorage.setItem('token', 'expire')
        const onUnauthorized = vi.fn()
        setUnauthorizedHandler(onUnauthorized)
        mockFetch(401)

        await apiFetch('/api/dashboard')

        expect(localStorage.getItem('token')).toBeNull()
        expect(onUnauthorized).toHaveBeenCalledOnce()
    })

    it("ne déclenche rien sur un 401 sans token (mauvais mot de passe au login)", async () => {
        const onUnauthorized = vi.fn()
        setUnauthorizedHandler(onUnauthorized)
        mockFetch(401)

        await apiFetch('/api/login_check', { method: 'POST', body: '{}' })

        expect(onUnauthorized).not.toHaveBeenCalled()
    })
})
