import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { extractErrorMessage } from '../utils/api'
import avatar1 from '../assets/avatars/avatar-1.svg'
import avatar2 from '../assets/avatars/avatar-2.svg'
import avatar3 from '../assets/avatars/avatar-3.svg'
import avatar4 from '../assets/avatars/avatar-4.svg'

const AVATARS = [
    { id: 'avatar-1', src: avatar1, label: 'Hunter' },
    { id: 'avatar-2', src: avatar2, label: 'Mage' },
    { id: 'avatar-3', src: avatar3, label: 'Warrior' },
    { id: 'avatar-4', src: avatar4, label: 'Shadow' },
]

function CreateCharacter() {
    const [disciplines, setDisciplines] = useState([])
    const [selectedDisciplines, setSelectedDisciplines] = useState([])
    const [selectedAvatar, setSelectedAvatar] = useState(null)
    const [error, setError] = useState('')
    const navigate = useNavigate()

    useEffect(() => {
        fetch('http://localhost:8000/api/disciplines')
            .then((response) => response.json())
            .then((data) => setDisciplines(data))
    }, [])

    useEffect(() => {
        const token = localStorage.getItem('token')
        fetch('http://localhost:8000/api/character', { headers: { 'Authorization': `Bearer ${token}` } })
            .then((response) => response.json())
            .then((data) => {
                const alreadyTrackedIds = data.map((tracking) => tracking.disciplineId)
                setSelectedDisciplines(alreadyTrackedIds)
            })
    }, [])

    function toggleDiscipline(id) {
        setSelectedDisciplines((prev) => prev.includes(id) ? prev.filter((disciplineId) => disciplineId !== id) : [...prev, id])
    }

    async function handleSubmit(e) {
        e.preventDefault()
        if (!selectedAvatar) { setError('Choose an avatar'); return }
        const token = localStorage.getItem('token')
        const payload = selectedDisciplines.map((disciplineId) => ({ disciplineId, goal: '' }))
        try {
            const response = await fetch('http://localhost:8000/api/character', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
                body: JSON.stringify({ avatar: selectedAvatar, disciplines: payload }),
            })
            if (!response.ok) {
                setError(await extractErrorMessage(response, 'An error occurred while creating your character'))
                return
            }
            navigate('/dashboard')
        } catch { setError('Server error — is the API running on localhost:8000?') }
    }

    return (
        <div className="center-page">
            <div className="panel character-panel">
                <div className="badge-tag" style={{ marginBottom: '18px' }}>Step 1 / 1</div>
                <h1 style={{ fontSize: '24px', marginBottom: '8px' }}>Create your character</h1>
                <p style={{ fontSize: '14px', color: 'var(--text-muted)', margin: '0 0 28px' }}>
                    You start at rank E. Choose the disciplines you want to grow — you can add more later.
                </p>

                <form onSubmit={handleSubmit}>
                    <div style={{ marginBottom: '28px' }}>
                        <label>Choose your avatar</label>
                        <div style={{ display: 'flex', gap: '12px' }}>
                            {AVATARS.map((avatar) => (
                                <img
                                    key={avatar.id}
                                    src={avatar.src}
                                    alt={avatar.label}
                                    width="72"
                                    height="72"
                                    className={`avatar-option ${selectedAvatar === avatar.id ? 'selected' : ''}`}
                                    onClick={() => setSelectedAvatar(avatar.id)}
                                />
                            ))}
                        </div>
                    </div>

                    <label style={{ marginBottom: '12px' }}>Starting disciplines</label>
                    <div className="discipline-grid" style={{ marginBottom: '32px' }}>
                        {disciplines.map((discipline) => {
                            const selected = selectedDisciplines.includes(discipline.id)
                            return (
                                <div
                                    key={discipline.id}
                                    className={`disc-card ${selected ? 'selected' : ''}`}
                                    onClick={() => toggleDiscipline(discipline.id)}
                                >
                                    <span className="disc-dot" />
                                    <span>{discipline.name}</span>
                                </div>
                            )
                        })}
                    </div>

                    {error && <p className="msg-error" style={{ marginBottom: '16px' }}>{error}</p>}

                    <button type="submit" className="btn-primary" style={{ width: '100%' }}>
                        Enter the system &rarr;
                    </button>
                </form>
            </div>
        </div>
    )
}

export default CreateCharacter
