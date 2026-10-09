import { heatLevel } from '../utils/heatLevel'

function formatDay(date) {
    return new Date(`${date}T00:00:00`).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })
}

// Une case par jour : plus la case est lumineuse, plus le joueur a gagné d'XP.
function ActivityHeatmap({ days }) {
    return (
        <div className="heatmap" role="list" aria-label="XP earned over the last 30 days">
            {days.map((day) => {
                const label = `${formatDay(day.date)}: ${day.xp} XP (${day.count} quest${day.count === 1 ? '' : 's'})`
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
