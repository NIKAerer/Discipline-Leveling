import { describe, expect, it } from 'vitest'
import { heatLevel } from './heatLevel'

describe('heatLevel', () => {
    it.each([
        [-10, 'negative'],
        [0, '0'],
        [15, '1'],
        [20, '2'],
        [89, '3'],
        [150, '4'],
    ])('%i XP donne le niveau %s', (xp, level) => {
        expect(heatLevel(xp)).toBe(level)
    })
})
