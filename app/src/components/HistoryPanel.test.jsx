import { render, screen } from '@testing-library/react'
import { describe, expect, it } from 'vitest'
import HistoryPanel from './HistoryPanel'

const history = {
    currentStreak: 4,
    bestStreak: 9,
    days: [
        { date: '2026-10-08', xp: 0, count: 0 },
        { date: '2026-10-09', xp: 35, count: 2 },
    ],
    recent: [],
}

describe('HistoryPanel', () => {
    it('affiche la série en cours et le record', () => {
        render(<HistoryPanel history={history} />)

        expect(screen.getByText('4')).toBeInTheDocument()
        expect(screen.getByText('9')).toBeInTheDocument()
    })

    it('affiche une case par jour avec son XP', () => {
        render(<HistoryPanel history={history} />)

        expect(screen.getAllByRole('listitem')).toHaveLength(2)
        expect(screen.getByLabelText('9 oct. : 35 XP (2 quêtes)')).toHaveClass('level-2')
    })
})
