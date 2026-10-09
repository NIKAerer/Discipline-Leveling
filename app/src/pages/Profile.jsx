import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import NavBar from '../components/NavBar'
import { apiFetch, extractErrorMessage } from '../utils/api'
import DisciplineIcon from '../components/DisciplineIcon'

function Profile() {
    const [name, setName] = useState('')
    const [email, setEmail] = useState('')
    const [rank, setRank] = useState('')
    const [expTotal, setExpTotal] = useState(0)
    const [profileError, setProfileError] = useState('')
    const [profileSuccess, setProfileSuccess] = useState('')

    const [allDisciplines, setAllDisciplines] = useState([])
    const [trackedIds, setTrackedIds] = useState([])
    const [selectedNewDisciplines, setSelectedNewDisciplines] = useState([])
    const [disciplinesError, setDisciplinesError] = useState('')
    const [disciplinesSuccess, setDisciplinesSuccess] = useState('')

    const [confirmDelete, setConfirmDelete] = useState(false)
    const [deleteError, setDeleteError] = useState('')

    const [loading, setLoading] = useState(true)

    const navigate = useNavigate()

    useEffect(() => {
        Promise.all([
            apiFetch('/api/profile').then((r) => r.json()),
            apiFetch('/api/disciplines').then((r) => r.json()),
            apiFetch('/api/character').then((r) => r.json()),
        ]).then(([profile, disciplines, tracked]) => {
            setName(profile.name)
            setEmail(profile.email)
            setRank(profile.rank)
            setExpTotal(profile.expTotal)
            setAllDisciplines(disciplines)
            setTrackedIds(tracked.map((t) => t.disciplineId))
            setLoading(false)
        })
    }, [])

    function toggleNewDiscipline(id) {
        setSelectedNewDisciplines((prev) =>
            prev.includes(id) ? prev.filter((disciplineId) => disciplineId !== id) : [...prev, id]
        )
    }

    async function handleProfileSubmit(e) {
        e.preventDefault()
        setProfileError('')
        setProfileSuccess('')

        try {
            const response = await apiFetch('/api/profile', {
                method: 'PATCH',
                body: JSON.stringify({ name, email }),
            })

            if (!response.ok) {
                setProfileError(await extractErrorMessage(response, 'Impossible de mettre à jour ton profil'))
                return
            }

            const data = await response.json()

            if (data.emailChanged) {
                // The email is the JWT identifier — the current token no longer
                // resolves to this account, so force a fresh login instead of
                // letting the next API call fail with a confusing 401.
                localStorage.removeItem('token')
                navigate('/', { state: { message: 'Ton email a été modifié. Reconnecte-toi.' } })
                return
            }

            setProfileSuccess('Profil mis à jour !')
        } catch {
            setProfileError('Impossible de joindre le serveur. Réessaie plus tard.')
        }
    }

    async function handleAddDisciplines(e) {
        e.preventDefault()
        setDisciplinesError('')
        setDisciplinesSuccess('')

        if (selectedNewDisciplines.length === 0) {
            setDisciplinesError('Choisis au moins une discipline')
            return
        }

        const payload = selectedNewDisciplines.map((disciplineId) => ({ disciplineId, goal: '' }))

        try {
            const response = await apiFetch('/api/character', {
                method: 'POST',
                body: JSON.stringify({ disciplines: payload }),
            })

            if (!response.ok) {
                setDisciplinesError(await extractErrorMessage(response, 'Impossible d\'ajouter ces disciplines'))
                return
            }

            setTrackedIds((prev) => [...prev, ...selectedNewDisciplines])
            setSelectedNewDisciplines([])
            setDisciplinesSuccess('Disciplines ajoutées ! Retrouve-les sur ton tableau de bord.')
        } catch {
            setDisciplinesError('Impossible de joindre le serveur. Réessaie plus tard.')
        }
    }

    async function handleDeleteAccount() {
        setDeleteError('')

        try {
            const response = await apiFetch('/api/profile', {
                method: 'DELETE',
            })

            if (!response.ok) {
                setDeleteError(await extractErrorMessage(response, 'Impossible de supprimer ton compte'))
                return
            }

            localStorage.removeItem('token')
            navigate('/')
        } catch {
            setDeleteError('Impossible de joindre le serveur. Réessaie plus tard.')
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

    const availableDisciplines = allDisciplines.filter((discipline) => !trackedIds.includes(discipline.id))

    return (
        <div className="page">
            <NavBar />
            <div className="container" style={{ paddingTop: '40px', paddingBottom: '60px', maxWidth: '640px', display: 'flex', flexDirection: 'column', gap: '28px' }}>
                <div>
                    <h1 style={{ fontSize: '24px', marginBottom: '4px' }}>Profil</h1>
                    <p style={{ fontSize: '13px', color: 'var(--text-muted)', margin: 0 }}>
                        Rang {rank} &middot; {expTotal} XP
                    </p>
                </div>

                <div className="panel" style={{ padding: '32px' }}>
                    <h2 style={{ fontSize: '16px', marginBottom: '20px' }}>Informations du compte</h2>
                    <form onSubmit={handleProfileSubmit} className="form-stack">
                        <div>
                            <label>Pseudo</label>
                            <input className="field" type="text" value={name} onChange={(e) => setName(e.target.value)} />
                        </div>
                        <div>
                            <label>Email</label>
                            <input className="field" type="email" value={email} onChange={(e) => setEmail(e.target.value)} />
                        </div>
                        {profileError && <p className="msg-error">{profileError}</p>}
                        {profileSuccess && <p className="msg-success">{profileSuccess}</p>}
                        <button type="submit" className="btn-primary" style={{ marginTop: '4px' }}>Enregistrer</button>
                    </form>
                </div>

                <div className="panel" style={{ padding: '32px' }}>
                    <h2 style={{ fontSize: '16px', marginBottom: '6px' }}>Ajouter des disciplines</h2>
                    <p style={{ fontSize: '13px', color: 'var(--text-muted)', margin: '0 0 20px' }}>
                        Suis une nouvelle discipline en plus de celles que tu as déjà.
                    </p>

                    {availableDisciplines.length === 0 ? (
                        <p style={{ fontSize: '13px', color: 'var(--text-muted)' }}>Tu suis déjà toutes les disciplines.</p>
                    ) : (
                        <form onSubmit={handleAddDisciplines}>
                            <div className="discipline-grid" style={{ marginBottom: '20px' }}>
                                {availableDisciplines.map((discipline) => {
                                    const selected = selectedNewDisciplines.includes(discipline.id)
                                    return (
                                        <div
                                            key={discipline.id}
                                            className={`disc-card ${selected ? 'selected' : ''}`}
                                            onClick={() => toggleNewDiscipline(discipline.id)}
                                        >
                                            <DisciplineIcon icon={discipline.icon} />
                                            <span>{discipline.name}</span>
                                        </div>
                                    )
                                })}
                            </div>
                            {disciplinesError && <p className="msg-error" style={{ marginBottom: '16px' }}>{disciplinesError}</p>}
                            {disciplinesSuccess && <p className="msg-success" style={{ marginBottom: '16px' }}>{disciplinesSuccess}</p>}
                            <button type="submit" className="btn-primary">Ajouter la sélection</button>
                        </form>
                    )}
                </div>

                <div className="panel" style={{ padding: '32px', borderColor: 'var(--danger)' }}>
                    <h2 style={{ fontSize: '16px', marginBottom: '6px' }}>Zone de danger</h2>
                    <p style={{ fontSize: '13px', color: 'var(--text-muted)', margin: '0 0 20px' }}>
                        Supprimer ton compte efface définitivement ton personnage, tes disciplines et toute ta progression. Impossible de revenir en arrière.
                    </p>

                    {deleteError && <p className="msg-error" style={{ marginBottom: '16px' }}>{deleteError}</p>}

                    {confirmDelete ? (
                        <div style={{ display: 'flex', gap: '12px' }}>
                            <button type="button" className="btn-danger" onClick={handleDeleteAccount}>
                                Oui, supprimer définitivement mon compte
                            </button>
                            <button type="button" className="btn-ghost" onClick={() => setConfirmDelete(false)}>
                                Annuler
                            </button>
                        </div>
                    ) : (
                        <button type="button" className="btn-danger" onClick={() => setConfirmDelete(true)}>
                            Supprimer mon compte
                        </button>
                    )}
                </div>
            </div>
        </div>
    )
}

export default Profile
