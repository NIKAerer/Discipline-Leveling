import { render, screen } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import { describe, expect, it } from 'vitest'
import AuthModal from './AuthModal'
import { DEMO_ACCOUNT } from '../utils/demo'

function renderModal(props) {
    render(
        <MemoryRouter>
            <AuthModal onClose={() => {}} {...props} />
        </MemoryRouter>,
    )
}

describe('AuthModal', () => {
    it('pré-remplit le compte de démo', () => {
        renderModal({ initialTab: 'login', demo: true })

        expect(screen.getByPlaceholderText('toi@exemple.fr')).toHaveValue(DEMO_ACCOUNT.email)
        expect(screen.getByText(/Compte de démo pré-rempli/)).toBeInTheDocument()
    })

    it("affiche le formulaire d'inscription vide", () => {
        renderModal({ initialTab: 'register' })

        expect(screen.getByRole('heading', { name: 'Crée ton compte' })).toBeInTheDocument()
        expect(screen.getByPlaceholderText('toi@exemple.fr')).toHaveValue('')
    })
})
