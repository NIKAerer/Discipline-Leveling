import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import NavBar from '../components/NavBar'
import { extractErrorMessage } from '../utils/api'

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
    const token = localStorage.getItem('token')

    useEffect(() => {
        Promise.all([
            fetch('http://localhost:8000/api/profile', { headers: { 'Authorization': `Bearer ${token}` } }).then((r) => r.json()),
            fetch('http://localhost:8000/api/disciplines').then((r) => r.json()),
            fetch('http://localhost:8000/api/character', { headers: { 'Authorization': `Bearer ${token}` } }).then((r) => r.json()),
        ]).then(([profile, disciplines, tracked]) => {
            setName(profile.name)
            setEmail(profile.email)
            setRank(profile.rank)
            setExpTotal(profile.expTotal)
            setAllDisciplines(disciplines)
            setTrackedIds(tracked.map((t) => t.disciplineId))
            setLoading(false)
        })
    }, [token])

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
            const response = await fetch('http://localhost:8000/api/profile', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
                body: JSON.stringify({ name, email }),
            })

            if (!response.ok) {
                setProfileError(await extractErrorMessage(response, 'An error occurred while updating your profile'))
                return
            }

            const data = await response.json()

            if (data.emailChanged) {
                // The email is the JWT identifier — the current token no longer
                // resolves to this account, so force a fresh login instead of
                // letting the next API call fail with a confusing 401.
                localStorage.removeItem('token')
                navigate('/', { state: { message: 'Your email was updated. Please log in again.' } })
                return
            }

            setProfileSuccess('Profile updated!')
        } catch {
            setProfileError('Server error — is the API running on localhost:8000?')
        }
    }

    async function handleAddDisciplines(e) {
        e.preventDefault()
        setDisciplinesError('')
        setDisciplinesSuccess('')

        if (selectedNewDisciplines.length === 0) {
            setDisciplinesError('Choose at least one discipline')
            return
        }

        const payload = selectedNewDisciplines.map((disciplineId) => ({ disciplineId, goal: '' }))

        try {
            const response = await fetch('http://localhost:8000/api/character', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
                body: JSON.stringify({ disciplines: payload }),
            })

            if (!response.ok) {
                setDisciplinesError(await extractErrorMessage(response, 'An error occurred while adding disciplines'))
                return
            }

            setTrackedIds((prev) => [...prev, ...selectedNewDisciplines])
            setSelectedNewDisciplines([])
            setDisciplinesSuccess('Disciplines added! Check your dashboard.')
        } catch {
            setDisciplinesError('Server error — is the API running on localhost:8000?')
        }
    }

    async function handleDeleteAccount() {
        setDeleteError('')

        try {
            const response = await fetch('http://localhost:8000/api/profile', {
                method: 'DELETE',
                headers: { 'Authorization': `Bearer ${token}` },
            })

            if (!response.ok) {
                setDeleteError(await extractErrorMessage(response, 'An error occurred while deleting your account'))
                return
            }

            localStorage.removeItem('token')
            navigate('/')
        } catch {
            setDeleteError('Server error — is the API running on localhost:8000?')
        }
    }

    if (loading) {
        return (
            <div className="page">
                <NavBar />
                <p style={{ padding: '48px' }}>Loading...</p>
            </div>
        )
    }

    const availableDisciplines = allDisciplines.filter((discipline) => !trackedIds.includes(discipline.id))

    return (
        <div className="page">
            <NavBar />
            <div className="container" style={{ paddingTop: '40px', paddingBottom: '60px', maxWidth: '640px', display: 'flex', flexDirection: 'column', gap: '28px' }}>
                <div>
                    <h1 style={{ fontSize: '24px', marginBottom: '4px' }}>Profile</h1>
                    <p style={{ fontSize: '13px', color: 'var(--text-muted)', margin: 0 }}>
                        Rank {rank} &middot; {expTotal} XP
                    </p>
                </div>

                <div className="panel" style={{ padding: '32px' }}>
                    <h2 style={{ fontSize: '16px', marginBottom: '20px' }}>Account details</h2>
                    <form onSubmit={handleProfileSubmit} className="form-stack">
                        <div>
                            <label>Username</label>
                            <input className="field" type="text" value={name} onChange={(e) => setName(e.target.value)} />
                        </div>
                        <div>
                            <label>Email</label>
                            <input className="field" type="email" value={email} onChange={(e) => setEmail(e.target.value)} />
                        </div>
                        {profileError && <p className="msg-error">{profileError}</p>}
                        {profileSuccess && <p className="msg-success">{profileSuccess}</p>}
                        <button type="submit" className="btn-primary" style={{ marginTop: '4px' }}>Save changes</button>
                    </form>
                </div>

                <div className="panel" style={{ padding: '32px' }}>
                    <h2 style={{ fontSize: '16px', marginBottom: '6px' }}>Add disciplines</h2>
                    <p style={{ fontSize: '13px', color: 'var(--text-muted)', margin: '0 0 20px' }}>
                        Track a new discipline in addition to the ones you already have.
                    </p>

                    {availableDisciplines.length === 0 ? (
                        <p style={{ fontSize: '13px', color: 'var(--text-muted)' }}>You&apos;re already tracking every discipline.</p>
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
                                            <span className="disc-dot" />
                                            <span>{discipline.name}</span>
                                        </div>
                                    )
                                })}
                            </div>
                            {disciplinesError && <p className="msg-error" style={{ marginBottom: '16px' }}>{disciplinesError}</p>}
                            {disciplinesSuccess && <p className="msg-success" style={{ marginBottom: '16px' }}>{disciplinesSuccess}</p>}
                            <button type="submit" className="btn-primary">Add selected</button>
                        </form>
                    )}
                </div>

                <div className="panel" style={{ padding: '32px', borderColor: 'var(--danger)' }}>
                    <h2 style={{ fontSize: '16px', marginBottom: '6px' }}>Danger zone</h2>
                    <p style={{ fontSize: '13px', color: 'var(--text-muted)', margin: '0 0 20px' }}>
                        Deleting your account permanently removes your character, your disciplines and all your progress. This cannot be undone.
                    </p>

                    {deleteError && <p className="msg-error" style={{ marginBottom: '16px' }}>{deleteError}</p>}

                    {confirmDelete ? (
                        <div style={{ display: 'flex', gap: '12px' }}>
                            <button type="button" className="btn-danger" onClick={handleDeleteAccount}>
                                Yes, delete my account permanently
                            </button>
                            <button type="button" className="btn-ghost" onClick={() => setConfirmDelete(false)}>
                                Cancel
                            </button>
                        </div>
                    ) : (
                        <button type="button" className="btn-danger" onClick={() => setConfirmDelete(true)}>
                            Delete my account
                        </button>
                    )}
                </div>
            </div>
        </div>
    )
}

export default Profile
