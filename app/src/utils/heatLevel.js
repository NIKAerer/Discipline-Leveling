// Intensité d'une case de la heatmap selon l'XP gagnée dans la journée.
// Un jour au bilan négatif (malus) a son propre niveau, affiché en rouge.
export function heatLevel(xp) {
    if (xp < 0) return 'negative'
    if (xp === 0) return '0'
    if (xp < 20) return '1'
    if (xp < 50) return '2'
    if (xp < 90) return '3'
    return '4'
}
