import { useState } from 'react'
import { useLocation } from 'react-router-dom'
import AuthModal from '../components/AuthModal'

function Home() {
    const location = useLocation()
    const infoMessage = location.state?.message

    // If we were redirected here after an email change (see Profile.jsx),
    // open straight to the login tab instead of making the user click twice.
    const [modalOpen, setModalOpen] = useState(() => Boolean(infoMessage))
    const [modalTab, setModalTab] = useState(() => infoMessage ? 'login' : 'register')

    const [demoMode, setDemoMode] = useState(false)

    function openModal(tab, demo = false) {
        setModalTab(tab)
        setDemoMode(demo)
        setModalOpen(true)
    }

    return (
        <div className="page">
            <div className="navbar">
                <div className="brand">
                    <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="oklch(0.75 0.15 230)" strokeWidth="1.6" strokeLinejoin="round">
                        <path d="M12 2l9 5v10l-9 5-9-5V7l9-5z" />
                        <path d="M12 8l4 2.3v4.4L12 17l-4-2.3v-4.4L12 8z" />
                    </svg>
                    <span>DISCIPLINE LEVELING</span>
                </div>
                <div className="navbar-actions">
                    <button type="button" className="btn-ghost" onClick={() => openModal('login')}>
                        Connexion
                    </button>
                    <button type="button" className="btn-primary" onClick={() => openModal('register')}>
                        Inscription
                    </button>
                </div>
            </div>

            {infoMessage && (
                <div className="container" style={{ paddingTop: '20px' }}>
                    <p className="msg-info" style={{ textAlign: 'center' }}>{infoMessage}</p>
                </div>
            )}

            <div className="container hero">
                <div className="hero-content">
                    <div className="badge-tag">Système de progression personnelle</div>
                    <h1 className="hero-title">Deviens le héros de ta propre progression.</h1>
                    <p className="hero-text">
                        Chaque effort réel, en ranked, en code, au sport ou au quotidien, devient de l'XP.
                        Grimpe les rangs de E à S et transforme ta vie en fiche de personnage.
                    </p>
                    <div className="hero-actions">
                        <button type="button" className="btn-primary" onClick={() => openModal('register')}>
                            Commencer ma progression &rarr;
                        </button>
                        <button type="button" className="btn-ghost" onClick={() => openModal('login', true)}>
                            Essayer la démo
                        </button>
                    </div>
                    <p className="hero-demo-hint">
                        Pas envie de créer un compte ? La démo contient 30 jours d&apos;historique et 20 games LoL.
                    </p>
                </div>

                {/* Static preview for visitors — not real user data */}
                <div className="panel preview-card pulse-glow">
                    <div className="preview-label">Fiche de personnage</div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '16px', marginBottom: '20px' }}>
                        <div className="rank-diamond">
                            <span>B</span>
                        </div>
                        <div>
                            <div className="display" style={{ fontSize: '17px' }}>Nika</div>
                            <div style={{ fontSize: '12px', color: 'var(--text-muted)' }}>Rang B &middot; 1 240 XP</div>
                        </div>
                    </div>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                        <div>
                            <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '12px', marginBottom: '4px' }}>
                                <span>LoL</span>
                                <span style={{ color: 'var(--text-muted)' }}>72%</span>
                            </div>
                            <div className="progress-track"><div className="progress-fill" style={{ width: '72%' }} /></div>
                        </div>
                        <div>
                            <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '12px', marginBottom: '4px' }}>
                                <span>Code</span>
                                <span style={{ color: 'var(--text-muted)' }}>45%</span>
                            </div>
                            <div className="progress-track"><div className="progress-fill" style={{ width: '45%' }} /></div>
                        </div>
                    </div>
                </div>
            </div>

            <div className="container feature-grid" style={{ paddingBottom: '80px' }}>
                <div className="panel feature-card">
                    <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="oklch(0.75 0.15 230)" strokeWidth="1.6">
                        <circle cx="12" cy="12" r="8" /><circle cx="12" cy="12" r="4.5" /><circle cx="12" cy="12" r="1" />
                    </svg>
                    <h3>Disciplines</h3>
                    <p>Choisis les domaines de ta vie à faire progresser : LoL, code, sport, lecture…</p>
                </div>
                <div className="panel feature-card">
                    <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="oklch(0.75 0.15 230)" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M5 13l4 4L19 7" />
                    </svg>
                    <h3>Quêtes &amp; XP</h3>
                    <p>Valide tes quêtes du jour pour gagner de l'XP, et prends des malus si tu te relâches.</p>
                </div>
                <div className="panel feature-card">
                    <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="oklch(0.75 0.15 230)" strokeWidth="1.6" strokeLinejoin="round">
                        <path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3z" />
                    </svg>
                    <h3>Rangs et séries</h3>
                    <p>Passe du rang E au rang S comme dans Solo Leveling, et entretiens ta série de jours.</p>
                </div>
            </div>

            {modalOpen && (
                <AuthModal
                    key={`${modalTab}-${demoMode}`}
                    initialTab={modalTab}
                    demo={demoMode}
                    onClose={() => setModalOpen(false)}
                />
            )}
        </div>
    )
}

export default Home
