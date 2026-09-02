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

    function openModal(tab) {
        setModalTab(tab)
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
                <div style={{ display: 'flex', gap: '14px' }}>
                    <button type="button" className="btn-ghost" onClick={() => openModal('login')}>
                        Login
                    </button>
                    <button type="button" className="btn-primary" onClick={() => openModal('register')}>
                        Sign up
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
                    <div className="badge-tag">Personal progression system</div>
                    <h1 className="hero-title">Become the protagonist of your own progression.</h1>
                    <p className="hero-text">
                        Every real effort — LoL, code, sport, everyday discipline — becomes XP.
                        Climb the ranks, from E to S, and watch your life turn into a character sheet.
                    </p>
                    <div className="hero-actions">
                        <button type="button" className="btn-primary" onClick={() => openModal('register')}>
                            Start my progression &rarr;
                        </button>
                        <button type="button" className="btn-link" onClick={() => openModal('login')}>
                            Already have an account? Log in
                        </button>
                    </div>
                </div>

                {/* Static preview for visitors — not real user data */}
                <div className="panel preview-card pulse-glow">
                    <div className="preview-label">Character sheet</div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '16px', marginBottom: '20px' }}>
                        <div className="rank-diamond">
                            <span>B</span>
                        </div>
                        <div>
                            <div className="display" style={{ fontSize: '17px' }}>Nika</div>
                            <div style={{ fontSize: '12px', color: 'var(--text-muted)' }}>Rank B &middot; 4,280 XP</div>
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
                    <p>Choose the areas of your life to grow: LoL, code, sport, and more.</p>
                </div>
                <div className="panel feature-card">
                    <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="oklch(0.75 0.15 230)" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M5 13l4 4L19 7" />
                    </svg>
                    <h3>Quests &amp; XP</h3>
                    <p>Complete quests to earn XP, and take penalties if you slack off.</p>
                </div>
                <div className="panel feature-card">
                    <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="oklch(0.75 0.15 230)" strokeWidth="1.6" strokeLinejoin="round">
                        <path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3z" />
                    </svg>
                    <h3>Rank system</h3>
                    <p>Progress from rank E to rank S, just like in Solo Leveling.</p>
                </div>
            </div>

            {modalOpen && (
                <AuthModal
                    initialTab={modalTab}
                    onClose={() => setModalOpen(false)}
                />
            )}
        </div>
    )
}

export default Home
