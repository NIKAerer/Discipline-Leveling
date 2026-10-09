import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import NavBar from '../components/NavBar'
import { apiFetch, extractErrorMessage } from '../utils/api'

function LpChart({ matches, lpGoal }) {
    if (matches.length < 2) {
        return (
            <p className="activity-empty" style={{ padding: '32px 22px' }}>
                Enregistre au moins 2 games pour voir ta courbe de LP.
            </p>
        )
    }

    const width = 600
    const height = 160
    const padding = 12

    const values = matches.map((m) => m.cumulativeLp)
    if (lpGoal !== null && lpGoal !== undefined) {
        values.push(lpGoal)
    }

    const min = Math.min(...values)
    const max = Math.max(...values)
    const range = max - min || 1

    function xFor(index) {
        return padding + (index / (matches.length - 1)) * (width - padding * 2)
    }

    function yFor(value) {
        return height - padding - ((value - min) / range) * (height - padding * 2)
    }

    const linePoints = matches.map((m, i) => `${xFor(i)},${yFor(m.cumulativeLp)}`).join(' ')

    return (
        <svg viewBox={`0 0 ${width} ${height}`} width="100%" height="160" preserveAspectRatio="none" style={{ display: 'block' }}>
            {(lpGoal !== null && lpGoal !== undefined) && (
                <line
                    x1={padding}
                    x2={width - padding}
                    y1={yFor(lpGoal)}
                    y2={yFor(lpGoal)}
                    stroke="var(--success)"
                    strokeWidth="1"
                    strokeDasharray="4 4"
                />
            )}
            <polyline points={linePoints} fill="none" stroke="var(--accent)" strokeWidth="2" />
            {matches.map((m, i) => (
                <circle key={m.id} cx={xFor(i)} cy={yFor(m.cumulativeLp)} r="3" fill="var(--accent)" />
            ))}
        </svg>
    )
}

const emptyForm = {
    champion: '',
    role: '',
    matchup: '',
    result: 'win',
    kills: '',
    deaths: '',
    assists: '',
    gameDurationMinutes: '',
    cs: '',
    lpChange: '',
}

function LolTracker() {
    const [overview, setOverview] = useState(null)
    const [notTracked, setNotTracked] = useState(false)
    const [loading, setLoading] = useState(true)

    const [form, setForm] = useState(emptyForm)
    const [formError, setFormError] = useState('')

    const [goalInput, setGoalInput] = useState('')
    const [startingInput, setStartingInput] = useState('')
    const [settingsSaved, setSettingsSaved] = useState(false)

    async function refreshOverview() {
        const response = await apiFetch(`/api/lol/overview`)

        if (response.status === 404) {
            setNotTracked(true)
            setLoading(false)
            return
        }

        const data = await response.json()
        setOverview(data)
        setGoalInput(data.lpGoal ?? '')
        setStartingInput(data.lpStarting ?? '')
        setLoading(false)
    }

    useEffect(() => {
        apiFetch(`/api/lol/overview`)
            .then(async (response) => {
                if (response.status === 404) {
                    setNotTracked(true)
                    setLoading(false)
                    return
                }

                const data = await response.json()
                setOverview(data)
                setGoalInput(data.lpGoal ?? '')
                setStartingInput(data.lpStarting ?? '')
                setLoading(false)
            })
            .catch(() => setLoading(false))
    }, [])

    function updateField(field, value) {
        setForm((prev) => ({ ...prev, [field]: value }))
    }

    async function submitMatch(e) {
        e.preventDefault()
        setFormError('')

        const kills = Number(form.kills)
        const deaths = Number(form.deaths)
        const assists = Number(form.assists)
        const lpChange = Number(form.lpChange)

        if (
            form.champion.trim() === ''
            || form.kills === '' || Number.isNaN(kills)
            || form.deaths === '' || Number.isNaN(deaths)
            || form.assists === '' || Number.isNaN(assists)
            || form.lpChange === '' || Number.isNaN(lpChange)
        ) {
            setFormError('Champion, KDA et variation de LP obligatoires')
            return
        }

        try {
            const response = await apiFetch(`/api/lol/matches`, {
                method: 'POST',
                body: JSON.stringify({
                    champion: form.champion.trim(),
                    role: form.role.trim() || null,
                    matchup: form.matchup.trim() || null,
                    win: form.result === 'win',
                    kills,
                    deaths,
                    assists,
                    gameDurationMinutes: form.gameDurationMinutes === '' ? null : Number(form.gameDurationMinutes),
                    cs: form.cs === '' ? null : Number(form.cs),
                    lpChange,
                }),
            })

            if (!response.ok) {
                setFormError(await extractErrorMessage(response, 'Impossible d\'enregistrer cette game'))
                return
            }

            setForm(emptyForm)
            await refreshOverview()
        } catch {
            setFormError('Impossible de joindre le serveur. Réessaie plus tard.')
        }
    }

    async function deleteMatch(matchId) {
        try {
            const response = await apiFetch(`/api/lol/matches/${matchId}`, {
                method: 'DELETE',
            })

            if (response.ok) {
                await refreshOverview()
            }
        } catch {
            // Silent — the row just stays, which is enough signal here.
        }
    }

    async function saveSettings(e) {
        e.preventDefault()
        setSettingsSaved(false)

        const response = await apiFetch(`/api/lol/settings`, {
            method: 'PATCH',
            body: JSON.stringify({
                lpGoal: goalInput === '' ? null : Number(goalInput),
                lpStarting: startingInput === '' ? null : Number(startingInput),
            }),
        })

        if (response.ok) {
            setSettingsSaved(true)
            await refreshOverview()
        }
    }

    if (loading) {
        return (
            <div className="page">
                <NavBar />
                <p style={{ padding: '48px' }}>Chargement…</p>
            </div>
        )
    }

    if (notTracked) {
        return (
            <div className="page">
                <NavBar />
                <div className="container" style={{ paddingTop: '40px' }}>
                    <p className="activity-empty">
                        Tu ne suis pas encore la discipline LoL. Ajoute-la depuis ton{' '}
                        <Link to="/profile">profil</Link>.
                    </p>
                </div>
            </div>
        )
    }

    const goalProgress = overview.lpGoal && overview.currentLp !== null
        ? Math.min(100, Math.max(0, Math.round((overview.currentLp / overview.lpGoal) * 100)))
        : 0

    const sortedMatches = [...overview.matches].reverse()

    return (
        <div className="page">
            <NavBar />
            <div className="container" style={{ paddingTop: '40px', paddingBottom: '40px', maxWidth: '760px' }}>
                <Link to="/dashboard" className="btn-link" style={{ marginBottom: '20px', display: 'inline-block' }}>
                    &larr; Retour au tableau de bord
                </Link>

                <div className="panel" style={{ padding: '32px', marginBottom: '32px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: '8px' }}>
                        <h1 style={{ fontSize: '22px' }}>Tracker LoL</h1>
                        <span style={{ fontSize: '12px', color: 'var(--text-muted)' }}>
                            {overview.currentLp !== null ? `${overview.currentLp} LP` : 'Pas encore de données'}
                            {overview.lpGoal ? ` / objectif ${overview.lpGoal} LP` : ''}
                        </span>
                    </div>

                    {overview.lpGoal && (
                        <div className="progress-track" style={{ marginBottom: '24px' }}>
                            <div className="progress-fill" style={{ width: `${goalProgress}%` }} />
                        </div>
                    )}

                    <form onSubmit={saveSettings} style={{ display: 'flex', gap: '10px', alignItems: 'flex-end', marginBottom: '20px', flexWrap: 'wrap' }}>
                        <div style={{ flex: 1, minWidth: '140px' }}>
                            <label>LP de départ</label>
                            <input
                                className="field"
                                type="number"
                                placeholder="Tes LP actuels"
                                value={startingInput}
                                onChange={(e) => setStartingInput(e.target.value)}
                            />
                        </div>
                        <div style={{ flex: 1, minWidth: '140px' }}>
                            <label>Objectif de LP</label>
                            <input
                                className="field"
                                type="number"
                                placeholder="ex : 1000"
                                value={goalInput}
                                onChange={(e) => setGoalInput(e.target.value)}
                            />
                        </div>
                        <button type="submit" className="btn-ghost">Enregistrer</button>
                    </form>
                    {settingsSaved && <p className="msg-success">Réglages enregistrés !</p>}

                    <h2 className="section-title" style={{ marginTop: '12px' }}>Progression des LP</h2>
                    <div className="panel" style={{ padding: '12px' }}>
                        <LpChart matches={overview.matches} lpGoal={overview.lpGoal} />
                    </div>
                </div>

                <div style={{ marginBottom: '32px' }}>
                    <h2 className="section-title">Winrate par champion</h2>
                    <div className="panel">
                        {overview.winrateByChampion.length === 0 && (
                            <p className="activity-empty">Aucune game enregistrée.</p>
                        )}
                        {overview.winrateByChampion.map((c) => (
                            <div key={c.champion} className="activity-row" style={{ flexDirection: 'column', alignItems: 'stretch', gap: '6px' }}>
                                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                                    <span>{c.champion}</span>
                                    <span style={{ color: 'var(--text-muted)' }}>
                                        {c.wins}V {c.losses}D &middot; {c.winratePercent}%
                                    </span>
                                </div>
                                <div className="progress-track">
                                    <div className="progress-fill" style={{ width: `${c.winratePercent}%` }} />
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                <div style={{ marginBottom: '32px' }}>
                    <h2 className="section-title">Ajouter une game</h2>
                    <div className="quest-add-panel">
                        <form className="custom-quest-form" onSubmit={submitMatch} style={{ flexWrap: 'wrap' }}>
                            <div>
                                <label>Champion</label>
                                <input className="field" type="text" value={form.champion} onChange={(e) => updateField('champion', e.target.value)} />
                            </div>
                            <div>
                                <label>Rôle</label>
                                <input className="field" type="text" value={form.role} onChange={(e) => updateField('role', e.target.value)} />
                            </div>
                            <div>
                                <label>Matchup</label>
                                <input className="field" type="text" value={form.matchup} onChange={(e) => updateField('matchup', e.target.value)} />
                            </div>
                            <div>
                                <label>Résultat</label>
                                <select className="field" value={form.result} onChange={(e) => updateField('result', e.target.value)}>
                                    <option value="win">Victoire</option>
                                    <option value="loss">Défaite</option>
                                </select>
                            </div>
                            <div>
                                <label>Kills</label>
                                <input className="field xp-input" type="number" value={form.kills} onChange={(e) => updateField('kills', e.target.value)} />
                            </div>
                            <div>
                                <label>Morts</label>
                                <input className="field xp-input" type="number" value={form.deaths} onChange={(e) => updateField('deaths', e.target.value)} />
                            </div>
                            <div>
                                <label>Assists</label>
                                <input className="field xp-input" type="number" value={form.assists} onChange={(e) => updateField('assists', e.target.value)} />
                            </div>
                            <div>
                                <label>Durée (min)</label>
                                <input className="field xp-input" type="number" value={form.gameDurationMinutes} onChange={(e) => updateField('gameDurationMinutes', e.target.value)} />
                            </div>
                            <div>
                                <label>CS</label>
                                <input className="field xp-input" type="number" value={form.cs} onChange={(e) => updateField('cs', e.target.value)} />
                            </div>
                            <div>
                                <label>Variation de LP</label>
                                <input className="field xp-input" type="number" placeholder="+18 ou -15" value={form.lpChange} onChange={(e) => updateField('lpChange', e.target.value)} />
                            </div>
                            <button type="submit" className="btn-primary">Ajouter</button>
                        </form>
                        {formError && <p className="msg-error">{formError}</p>}
                    </div>
                </div>

                <div>
                    <h2 className="section-title">Historique des games</h2>
                    <div className="panel quest-list">
                        {sortedMatches.length === 0 && (
                            <p className="activity-empty">Aucune game enregistrée.</p>
                        )}
                        {sortedMatches.map((m) => (
                            <div key={m.id} className="activity-row" style={{ flexDirection: 'column', alignItems: 'stretch', gap: '4px' }}>
                                <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                                    <span>
                                        <strong style={{ color: m.win ? 'var(--accent)' : 'var(--danger)' }}>
                                            {m.win ? 'Victoire' : 'Défaite'}
                                        </strong>{' '}
                                        &middot; {m.champion}{m.role ? ` (${m.role})` : ''}{m.matchup ? ` vs ${m.matchup}` : ''}
                                    </span>
                                    <span style={{ display: 'flex', alignItems: 'center', gap: '14px' }}>
                                        <span className={m.lpChange >= 0 ? 'activity-xp-positive' : 'activity-xp-negative'}>
                                            {m.lpChange >= 0 ? '+' : ''}{m.lpChange} LP
                                        </span>
                                        <button type="button" className="btn-link" onClick={() => deleteMatch(m.id)}>
                                            Supprimer
                                        </button>
                                    </span>
                                </div>
                                <span style={{ fontSize: '12px', color: 'var(--text-muted)' }}>
                                    {m.kills}/{m.deaths}/{m.assists} KDA
                                    {m.cs !== null ? ` · ${m.cs} CS` : ''}
                                    {m.gameDurationMinutes !== null ? ` · ${m.gameDurationMinutes} min` : ''}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    )
}

export default LolTracker
