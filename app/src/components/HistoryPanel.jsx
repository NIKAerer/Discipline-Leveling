import ActivityHeatmap from './ActivityHeatmap'

// Série en cours, record et carte des 30 derniers jours.
// Les données viennent de GET /api/history, chargées par la page parente.
function HistoryPanel({ history }) {
    return (
        <div className="panel history-panel">
            <div className="streak-stats">
                <div>
                    <span className="streak-value">{history.currentStreak}</span>
                    <span className="streak-label">day streak</span>
                </div>
                <div>
                    <span className="streak-value muted">{history.bestStreak}</span>
                    <span className="streak-label">best streak</span>
                </div>
            </div>
            <div className="history-heatmap">
                <span className="streak-label">Last 30 days</span>
                <ActivityHeatmap days={history.days} />
            </div>
        </div>
    )
}

export default HistoryPanel
