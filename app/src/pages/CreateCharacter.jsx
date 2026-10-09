import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { apiFetch, extractErrorMessage } from '../utils/api'
import avatar1 from '../assets/avatars/avatar-1.svg'
import avatar2 from '../assets/avatars/avatar-2.svg'
import avatar3 from '../assets/avatars/avatar-3.svg'
import avatar4 from '../assets/avatars/avatar-4.svg'
import DisciplineIcon from '../components/DisciplineIcon'

const AVATARS = [
    { id: 'avatar-1', src: avatar1, label: 'Chasseur' },
    { id: 'avatar-2', src: avatar2, label: 'Mage' },
    { id: 'avatar-3', src: avatar3, label: 'Guerrier' },
    { id: 'avatar-4', src: avatar4, label: 'Ombre' },
]

function CreateCharacter() {
    const [disciplines, setDisciplines] = useState([])
    const [selectedDisciplines, setSelectedDisciplines] = useState([])
    const [selectedAvatar, setSelectedAvatar] = useState(null)
    const [error, setError] = useState('')
    const navigate = useNavigate()

    useEffect(() => {
        apiFetch('/api/disciplines')
            .then((response) => response.json())
            .then((data) => setDisciplines(data))
    }, [])

    useEffect(() => {
        apiFetch('/api/character')
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
        if (!selectedAvatar) { setError('Choisis un avatar'); return }
        const payload = selectedDisciplines.map((disciplineId) => ({ disciplineId, goal: '' }))
        try {
            const response = await apiFetch('/api/character', {
                method: 'POST',
                body: JSON.stringify({ avatar: selectedAvatar, disciplines: payload }),
            })
            if (!response.ok) {
                setError(await extractErrorMessage(response, 'Impossible de créer ton personnage'))
                return
            }
            navigate('/dashboard')
        } catch { setError('Impossible de joindre le serveur. Réessaie plus tard.') }
    }

    return (
        <div className="center-page">
            <div className="panel character-panel">
                <div className="badge-tag" style={{ marginBottom: '18px' }}>Dernière étape</div>
                <h1 style={{ fontSize: '24px', marginBottom: '8px' }}>Crée ton personnage</h1>
                <p style={{ fontSize: '14px', color: 'var(--text-muted)', margin: '0 0 28px' }}>
                    Tu commences au rang E. Choisis les disciplines à faire progresser, tu pourras en ajouter plus tard.
                </p>

                <form onSubmit={handleSubmit}>
                    <div style={{ marginBottom: '28px' }}>
                        <label>Choisis ton avatar</label>
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

                    <label style={{ marginBottom: '12px' }}>Disciplines de départ</label>
                    <div className="discipline-grid" style={{ marginBottom: '32px' }}>
                        {disciplines.map((discipline) => {
                            const selected = selectedDisciplines.includes(discipline.id)
                            return (
                                <div
                                    key={discipline.id}
                                    className={`disc-card ${selected ? 'selected' : ''}`}
                                    onClick={() => toggleDiscipline(discipline.id)}
                                >
                                    <DisciplineIcon icon={discipline.icon} />
                                    <span>{discipline.name}</span>
                                </div>
                            )
                        })}
                    </div>

                    {error && <p className="msg-error" style={{ marginBottom: '16px' }}>{error}</p>}

                    <button type="submit" className="btn-primary" style={{ width: '100%' }}>
                        Entrer dans le système &rarr;
                    </button>
                </form>
            </div>
        </div>
    )
}

export default CreateCharacter
