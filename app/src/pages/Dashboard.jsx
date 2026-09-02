import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import NavBar from '../components/NavBar'

function Dashboard() {
    const [dashboard, setDashboard] = useState(null)
    const navigate = useNavigate()

    useEffect(() => {
        const token = localStorage.getItem('token')
        fetch('http://localhost:8000/api/dashboard', { headers: { 'Authorization': `Bearer ${token}` } })
            .then((response) => response.json())
            .then((data) => setDashboard(data))
    }, [])

    if (!dashboard) {
        return (
            <div className="page">
                <NavBar />
                <p style={{ padding: '48px' }}>Loading...</p>
            </div>
        )
    }

    return (
        <div className="page">
            <NavBar />
            <div className="container" style={{ paddingTop: '40px', paddingBottom: '40px' }}>
                <div className="panel dashboard-header pulse-glow">
                    <div className="rank-diamond">
                        <span>{dashboard.rank}</span>
                    </div>
                    <div className="dashboard-header-info">
                        <div style={{ display: 'flex', alignItems: 'baseline', gap: '12px', marginBottom: '6px' }}>
                            <h1 style={{ fontSize: '26px' }}>{dashboard.name}</h1>
                            <span style={{ fontSize: '13px', color: 'var(--text-muted)' }}>Rank {dashboard.rank}</span>
                        </div>
                        <div style={{ fontSize: '12px', color: 'var(--text-muted)', marginBottom: '10px' }}>
                            {dashboard.expTotal} XP
                        </div>
                        <div className="progress-track" style={{ maxWidth: '320px' }}>
                            <div className="progress-fill" style={{ width: `${dashboard.progressPercent}%` }} />
                        </div>
                    </div>
                </div>

                <div>
                    <h2 className="section-title">Your disciplines</h2>
                    <div className="disciplines-grid">
                        {dashboard.disciplines.map((discipline) => (
                            <button
                                type="button"
                                key={discipline.disciplineId}
                                className="discipline-card"
                                onClick={() => navigate(`/discipline/${discipline.disciplineId}`)}
                            >
                                <div className="discipline-card-head">
                                    <span className="disc-dot" />
                                    <span>{discipline.name}</span>
                                </div>
                                <p className="discipline-goal">{discipline.goal || 'No goal set yet'}</p>
                                <div className="discipline-meta" style={{ marginBottom: '10px' }}>Rank {discipline.rank} &middot; {discipline.exp} XP</div>
                                <div className="progress-track">
                                    <div className="progress-fill" style={{ width: `${discipline.progressPercent}%` }} />
                                </div>
                            </button>
                        ))}
                    </div>
                </div>

                <div>
                    <h2 className="section-title">Recent activity</h2>
                    <div className="panel activity-list">
                        <p className="activity-empty">No activity yet</p>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default Dashboard
