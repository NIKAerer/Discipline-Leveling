import { render, screen } from '@testing-library/react'
import { MemoryRouter, Route, Routes } from 'react-router-dom'
import { describe, expect, it } from 'vitest'
import ProtectedRoute from './ProtectedRoute'

function renderAt(path) {
    render(
        <MemoryRouter initialEntries={[path]}>
            <Routes>
                <Route path="/" element={<p>Accueil</p>} />
                <Route
                    path="/dashboard"
                    element={
                        <ProtectedRoute>
                            <p>Tableau de bord</p>
                        </ProtectedRoute>
                    }
                />
            </Routes>
        </MemoryRouter>,
    )
}

describe('ProtectedRoute', () => {
    it("renvoie vers l'accueil sans token", () => {
        renderAt('/dashboard')

        expect(screen.getByText('Accueil')).toBeInTheDocument()
        expect(screen.queryByText('Tableau de bord')).not.toBeInTheDocument()
    })

    it('affiche la page avec un token', () => {
        localStorage.setItem('token', 'faux-token')

        renderAt('/dashboard')

        expect(screen.getByText('Tableau de bord')).toBeInTheDocument()
    })
})
