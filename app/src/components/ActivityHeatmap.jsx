import { heatLevel } from '../utils/heatLevel'

function formatDay(date) {
    return new Date(`${date}T00:00:00`).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })
}

// Une case par jour : plus la case est lumineuse, plus le joueur a gagné d'XP.
function ActivityHeatmap({ days }) {
    return (
        <div className="heatmap" role="list" aria-label="XP gagnée sur les 30 derniers jours">
            {days.map((day) => {
                const label = `${formatDay(day.date)} : ${day.xp} XP (${day.count} quête${day.count > 1 ? 's' : ''})`
                return (
                    <span
                        key={day.date}
                        role="listitem"
                        className={`heatmap-cell level-${heatLevel(day.xp)}`}
                        title={label}
                        aria-label={label}
                    />
                )
            })}
        </div>
    )
}

export default ActivityHeatmap
