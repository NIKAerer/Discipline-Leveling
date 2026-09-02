import { useState, useEffect } from 'react'
import { useParams, useNavigate, Link } from 'react-router-dom'
import NavBar from '../components/NavBar'
import { extractErrorMessage } from '../utils/api'

function QuestSection({ title, danger, items, onToggle, adding, onOpenAdd, onCloseAdd, templates, onAddFromTemplate, newLabel, setNewLabel, newXp, setNewXp, onSubmitCustom, formError }) {
    return (
        <div>
            <h2 className={`section-title ${danger ? 'malus-title' : ''}`}>{title}</h2>
            <div className="panel quest-list">
                {items.length === 0 && (
                    <p className="activity-empty">Nothing here yet.</p>
                )}
                {items.map((item) => (
                    <button
                        type="button"
                        key={item.id}
                        className={`quest-row ${danger ? 'malus' : ''} ${item.validatedToday ? 'done' : ''}`}
                        onClick={() => onToggle(item)}
                    >
                        <span className="quest-checkbox">
                            {item.validatedToday && (
                                <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke={danger ? 'var(--bg)' : 'var(--accent-dark)'} strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
                                    <path d="M5 13l4 4L19 7" />
                                </svg>
                            )}
                        </span>
                        <span className="quest-label">{item.label}</span>
                        <span className="quest-xp" style={{ color: danger ? 'var(--danger)' : 'var(--accent)' }}>
                            {item.expValue > 0 ? '+' : ''}{item.expValue} XP
                        </span>
                    </button>
                ))}
            </div>

            {adding ? (
                <div className="quest-add-panel">
                    {templates.length > 0 && (
                        <div>
                            <label style={{ marginBottom: '8px' }}>Suggestions</label>
                            <div className="template-pills">
                                {templates.map((template) => (
                                    <button
                                        type="button"
                                        key={template.id}
                                        className="template-pill"
                                        onClick={() => onAddFromTemplate(template)}
                                    >
                                        {template.label} ({template.expValue > 0 ? '+' : ''}{template.expValue} XP)
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    <form className="custom-quest-form" onSubmit={onSubmitCustom}>
                        <div>
                            <label>Custom {danger ? 'malus' : 'quest'}</label>
                            <input
                                className="field"
                                type="text"
                                placeholder="Label"
                                value={newLabel}
                                onChange={(e) => setNewLabel(e.target.value)}
                            />
                        </div>
                        <div>
                            <label>XP</label>
                            <input
                                className="field xp-input"
                                type="number"
                                min="1"
                                value={newXp}
                                onChange={(e) => setNewXp(e.target.value)}
                            />
                        </div>
                        <button type="submit" className="btn-primary">Add</button>
                        <button type="button" className="btn-ghost" onClick={onCloseAdd}>Cancel</button>
                    </form>

                    {formError && <p className="msg-error">{formError}</p>}
                </div>
            ) : (
                <button type="button" className="add-quest-btn" onClick={onOpenAdd}>
                    + Add a {danger ? 'malus' : 'quest'}
                </button>
            )}
        </div>
    )
}

function DisciplineDetail() {
    const { id } = useParams()
    const navigate = useNavigate()
    const [discipline, setDiscipline] = useState(null)
    const [goal, setGoal] = useState('')
    const [saved, setSaved] = useState(false)
    const [error, setError] = useState('')

    const [quests, setQuests] = useState([])
    const [templates, setTemplates] = useState([])

    const [addingType, setAddingType] = useState(null) // null | 'quest' | 'malus'
    const [newLabel, setNewLabel] = useState('')
    const [newXp, setNewXp] = useState('10')
    const [formError, setFormError] = useState('')

    const token = localStorage.getItem('token')

    useEffect(() => {
        fetch(`http://localhost:8000/api/character/${id}`, {
            headers: { 'Authorization': `Bearer ${token}` },
        })
            .then((response) => response.json())
            .then((data) => {
                setDiscipline(data)
                setGoal(data.goal || '')
            })

        fetch(`http://localhost:8000/api/character/${id}/quests`, {
            headers: { 'Authorization': `Bearer ${token}` },
        })
            .then((response) => response.json())
            .then((data) => setQuests(data))

        fetch(`http://localhost:8000/api/disciplines/${id}/quest-templates`)
            .then((response) => response.json())
            .then((data) => setTemplates(data))
    }, [id, token])

    async function handleSave(e) {
        e.preventDefault()
        setSaved(false)
        setError('')

        const response = await fetch(`http://localhost:8000/api/character/${id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({ goal }),
        })

        if (!response.ok) {
            setError('Could not save your goal')
            return
        }

        setSaved(true)
    }

    async function toggleQuest(quest) {
        const method = quest.validatedToday ? 'DELETE' : 'POST'

        try {
            const response = await fetch(`http://localhost:8000/api/quests/${quest.id}/validate`, {
                method,
                headers: { 'Authorization': `Bearer ${token}` },
            })

            if (!response.ok) {
                return
            }

            const data = await response.json()

            setQuests((prev) => prev.map((item) => (
                item.id === quest.id ? { ...item, validatedToday: data.validatedToday } : item
            )))
            setDiscipline((prev) => ({
                ...prev,
                exp: data.disciplineExp,
                rank: data.disciplineRank,
            }))
        } catch {
            // Silent — the checkbox just won't move, which is enough signal here.
        }
    }

    function openAdd(type) {
        setAddingType(type)
        setNewLabel('')
        setNewXp('10')
        setFormError('')
    }

    function closeAdd() {
        setAddingType(null)
        setFormError('')
    }

    async function addFromTemplate(template) {
        setFormError('')

        try {
            const response = await fetch(`http://localhost:8000/api/character/${id}/quests/from-template/${template.id}`, {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}` },
            })

            if (!response.ok) {
                setFormError(await extractErrorMessage(response, 'Could not add this quest'))
                return
            }

            const created = await response.json()
            setQuests((prev) => [...prev, created])
            closeAdd()
        } catch {
            setFormError('Server error — is the API running on localhost:8000?')
        }
    }

    async function submitCustom(e) {
        e.preventDefault()
        setFormError('')

        const label = newLabel.trim()
        const magnitude = Math.abs(Number(newXp))

        if (label === '' || !magnitude) {
            setFormError('A label and an XP value are required')
            return
        }

        const expValue = addingType === 'malus' ? -magnitude : magnitude

        try {
            const response = await fetch(`http://localhost:8000/api/character/${id}/quests`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
                body: JSON.stringify({ label, expValue }),
            })

            if (!response.ok) {
                setFormError(await extractErrorMessage(response, 'Could not add this quest'))
                return
            }

            const created = await response.json()
            setQuests((prev) => [...prev, created])
            closeAdd()
        } catch {
            setFormError('Server error — is the API running on localhost:8000?')
        }
    }

    if (!discipline) {
        return (
            <div className="page">
                <NavBar />
                <p style={{ padding: '48px' }}>Loading...</p>
            </div>
        )
    }

    const positiveQuests = quests.filter((q) => q.expValue > 0)
    const malusItems = quests.filter((q) => q.expValue < 0)
    const positiveTemplates = templates.filter((t) => t.expValue > 0)
    const malusTemplates = templates.filter((t) => t.expValue < 0)

    return (
        <div className="page">
            <NavBar />
            <div className="container" style={{ paddingTop: '40px', paddingBottom: '40px', maxWidth: '640px' }}>
                <button type="button" className="btn-link" style={{ marginBottom: '20px' }} onClick={() => navigate('/dashboard')}>
                    &larr; Back to dashboard
                </button>

                <div className="panel" style={{ padding: '32px', marginBottom: '32px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: '8px' }}>
                        <div className="discipline-card-head" style={{ marginBottom: 0 }}>
                            <span className="disc-dot" />
                            <h1 style={{ fontSize: '22px' }}>{discipline.name}</h1>
                        </div>
                        <span style={{ fontSize: '12px', color: 'var(--text-muted)' }}>
                            Rank {discipline.rank} &middot; {discipline.progressPercent}%
                        </span>
                    </div>
                    <div className="progress-track" style={{ marginBottom: '24px' }}>
                        <div className="progress-fill" style={{ width: `${discipline.progressPercent}%` }} />
                    </div>

                    {discipline.name === 'LoL' && (
                        <Link to="/lol" className="btn-ghost" style={{ marginBottom: '24px', display: 'inline-block' }}>
                            Open LoL Tracker &rarr;
                        </Link>
                    )}

                    <form onSubmit={handleSave}>
                        <div style={{ marginBottom: '20px' }}>
                            <label>Goal</label>
                            <input
                                className="field"
                                type="text"
                                value={goal}
                                onChange={(e) => setGoal(e.target.value)}
                            />
                        </div>
                        <button type="submit" className="btn-primary">Save</button>
                    </form>

                    {error && <p className="msg-error" style={{ marginTop: '16px' }}>{error}</p>}
                    {saved && <p className="msg-success" style={{ marginTop: '16px' }}>Saved!</p>}
                </div>

                <div style={{ marginBottom: '32px' }}>
                    <QuestSection
                        title="Quests"
                        danger={false}
                        items={positiveQuests}
                        onToggle={toggleQuest}
                        adding={addingType === 'quest'}
                        onOpenAdd={() => openAdd('quest')}
                        onCloseAdd={closeAdd}
                        templates={positiveTemplates}
                        onAddFromTemplate={addFromTemplate}
                        newLabel={newLabel}
                        setNewLabel={setNewLabel}
                        newXp={newXp}
                        setNewXp={setNewXp}
                        onSubmitCustom={submitCustom}
                        formError={addingType === 'quest' ? formError : ''}
                    />
                </div>

                <div>
                    <QuestSection
                        title="Malus"
                        danger={true}
                        items={malusItems}
                        onToggle={toggleQuest}
                        adding={addingType === 'malus'}
                        onOpenAdd={() => openAdd('malus')}
                        onCloseAdd={closeAdd}
                        templates={malusTemplates}
                        onAddFromTemplate={addFromTemplate}
                        newLabel={newLabel}
                        setNewLabel={setNewLabel}
                        newXp={newXp}
                        setNewXp={setNewXp}
                        onSubmitCustom={submitCustom}
                        formError={addingType === 'malus' ? formError : ''}
                    />
                </div>
            </div>
        </div>
    )
}

export default DisciplineDetail
