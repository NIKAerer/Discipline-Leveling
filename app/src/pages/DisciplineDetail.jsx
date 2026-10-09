import { useState, useEffect, useCallback } from 'react'
import { useParams, useNavigate, Link } from 'react-router-dom'
import NavBar from '../components/NavBar'
import HistoryPanel from '../components/HistoryPanel'
import { apiFetch, extractErrorMessage } from '../utils/api'
import DisciplineIcon from '../components/DisciplineIcon'

function QuestSection({ title, danger, items, onToggle, onDelete, adding, onOpenAdd, onCloseAdd, templates, onAddFromTemplate, newLabel, setNewLabel, newXp, setNewXp, onSubmitCustom, formError }) {
    return (
        <div>
            <h2 className={`section-title ${danger ? 'malus-title' : ''}`}>{title}</h2>
            <div className="panel quest-list">
                {items.length === 0 && (
                    <p className="activity-empty">Rien pour l'instant.</p>
                )}
                {items.map((item) => (
                    <div className="quest-item" key={item.id}>
                        <button
                            type="button"
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
                        <button
                            type="button"
                            className="quest-delete"
                            aria-label={`Supprimer « ${item.label} »`}
                            title="Supprimer"
                            onClick={() => onDelete(item)}
                        >
                            &times;
                        </button>
                    </div>
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
                            <label>{danger ? 'Malus personnalisé' : 'Quête personnalisée'}</label>
                            <input
                                className="field"
                                type="text"
                                placeholder="Intitulé"
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
                        <button type="submit" className="btn-primary">Ajouter</button>
                        <button type="button" className="btn-ghost" onClick={onCloseAdd}>Annuler</button>
                    </form>

                    {formError && <p className="msg-error">{formError}</p>}
                </div>
            ) : (
                <button type="button" className="add-quest-btn" onClick={onOpenAdd}>
                    + Ajouter {danger ? 'un malus' : 'une quête'}
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
    const [history, setHistory] = useState(null)

    const loadHistory = useCallback(() => {
        apiFetch(`/api/history?disciplineId=${id}`)
            .then((response) => (response.ok ? response.json() : null))
            .then((data) => setHistory(data))
    }, [id])

    useEffect(() => {
        apiFetch(`/api/character/${id}`)
            .then((response) => response.json())
            .then((data) => {
                setDiscipline(data)
                setGoal(data.goal || '')
            })

        apiFetch(`/api/character/${id}/quests`)
            .then((response) => response.json())
            .then((data) => setQuests(data))

        apiFetch(`/api/disciplines/${id}/quest-templates`)
            .then((response) => response.json())
            .then((data) => setTemplates(data))

        loadHistory()
    }, [id, loadHistory])

    async function handleSave(e) {
        e.preventDefault()
        setSaved(false)
        setError('')

        const response = await apiFetch(`/api/character/${id}`, {
            method: 'PATCH',
            body: JSON.stringify({ goal }),
        })

        if (!response.ok) {
            setError('Impossible d\'enregistrer ton objectif')
            return
        }

        setSaved(true)
    }

    async function toggleQuest(quest) {
        const method = quest.validatedToday ? 'DELETE' : 'POST'

        try {
            const response = await apiFetch(`/api/quests/${quest.id}/validate`, {
                method,
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
                progressPercent: data.disciplineProgressPercent,
            }))
            loadHistory()
        } catch {
            // Silent — the checkbox just won't move, which is enough signal here.
        }
    }

    async function deleteQuest(quest) {
        if (!window.confirm(`Supprimer la quête « ${quest.label} » ? L'XP déjà gagnée est conservée.`)) {
            return
        }

        const response = await apiFetch(`/api/quests/${quest.id}`, { method: 'DELETE' })

        if (response.ok) {
            setQuests((prev) => prev.filter((item) => item.id !== quest.id))
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
            const response = await apiFetch(`/api/character/${id}/quests/from-template/${template.id}`, {
                method: 'POST',
            })

            if (!response.ok) {
                setFormError(await extractErrorMessage(response, 'Impossible d\'ajouter cette quête'))
                return
            }

            const created = await response.json()
            setQuests((prev) => [...prev, created])
            closeAdd()
        } catch {
            setFormError('Impossible de joindre le serveur. Réessaie plus tard.')
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
            const response = await apiFetch(`/api/character/${id}/quests`, {
                method: 'POST',
                body: JSON.stringify({ label, expValue }),
            })

            if (!response.ok) {
                setFormError(await extractErrorMessage(response, 'Impossible d\'ajouter cette quête'))
                return
            }

            const created = await response.json()
            setQuests((prev) => [...prev, created])
            closeAdd()
        } catch {
            setFormError('Impossible de joindre le serveur. Réessaie plus tard.')
        }
    }

    if (!discipline) {
        return (
            <div className="page">
                <NavBar />
                <p style={{ padding: '48px' }}>Chargement…</p>
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
                    &larr; Retour au tableau de bord
                </button>

                <div className="panel" style={{ padding: '32px', marginBottom: '32px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: '8px' }}>
                        <div className="discipline-card-head" style={{ marginBottom: 0 }}>
                            <DisciplineIcon icon={discipline.icon} />
                            <h1 style={{ fontSize: '22px' }}>{discipline.name}</h1>
                        </div>
                        <span style={{ fontSize: '12px', color: 'var(--text-muted)' }}>
                            Rang {discipline.rank} &middot; {discipline.progressPercent}%
                        </span>
                    </div>
                    <div className="progress-track" style={{ marginBottom: '24px' }}>
                        <div className="progress-fill" style={{ width: `${discipline.progressPercent}%` }} />
                    </div>

                    {discipline.name === 'LoL' && (
                        <Link to="/lol" className="btn-ghost" style={{ marginBottom: '24px', display: 'inline-block' }}>
                            Ouvrir le tracker LoL &rarr;
                        </Link>
                    )}

                    <form onSubmit={handleSave}>
                        <div style={{ marginBottom: '20px' }}>
                            <label>Objectif</label>
                            <input
                                className="field"
                                type="text"
                                value={goal}
                                onChange={(e) => setGoal(e.target.value)}
                            />
                        </div>
                        <button type="submit" className="btn-primary">Enregistrer</button>
                    </form>

                    {error && <p className="msg-error" style={{ marginTop: '16px' }}>{error}</p>}
                    {saved && <p className="msg-success" style={{ marginTop: '16px' }}>Enregistré !</p>}
                </div>

                {history && <HistoryPanel history={history} />}

                <div style={{ marginBottom: '32px' }}>
                    <QuestSection
                        title="Quêtes"
                        danger={false}
                        items={positiveQuests}
                        onToggle={toggleQuest}
                        onDelete={deleteQuest}
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
                        onDelete={deleteQuest}
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
