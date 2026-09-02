import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { extractErrorMessage } from '../utils/api'

function AuthModal({ initialTab, onClose }) {
    const [activeTab, setActiveTab] = useState(initialTab)
    const [name, setName] = useState('')
    const [email, setEmail] = useState('')
    const [password, setPassword] = useState('')
    const [confirmPassword, setConfirmPassword] = useState('')
    const [error, setError] = useState('')
    const [info, setInfo] = useState('')
    const navigate = useNavigate()

    useEffect(() => {
        function handleKeyDown(e) {
            if (e.key === 'Escape') {
                onClose()
            }
        }
        window.addEventListener('keydown', handleKeyDown)
        return () => window.removeEventListener('keydown', handleKeyDown)
    }, [onClose])

    function switchTab(tab) {
        setActiveTab(tab)
        setPassword('')
        setConfirmPassword('')
        setError('')
        setInfo('')
    }

    async function handleLogin(e) {
        e.preventDefault()
        setError('')

        try {
            const response = await fetch('http://localhost:8000/api/login_check', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password }),
            })

            if (!response.ok) {
                setError(await extractErrorMessage(response, 'An error occurred during login'))
                return
            }

            const data = await response.json()
            localStorage.setItem('token', data.token)

            // Check if this user already has a character (tracked disciplines),
            // so a returning user goes straight to the dashboard instead of
            // being sent back through character creation every time.
            const characterResponse = await fetch('http://localhost:8000/api/character', {
                headers: { 'Authorization': `Bearer ${data.token}` },
            })

            if (!characterResponse.ok) {
                // Login itself succeeded — don't block the user with an error
                // just because this secondary read failed. Send them through
                // character creation, the safe default for any account state.
                onClose()
                navigate('/create-character')
                return
            }

            const disciplines = await characterResponse.json()

            onClose()
            navigate(disciplines.length > 0 ? '/dashboard' : '/create-character')
        } catch {
            setError('Server error — is the API running on localhost:8000?')
        }
    }

    async function handleRegister(e) {
        e.preventDefault()
        setError('')

        if (password !== confirmPassword) {
            setError("Passwords don't match")
            return
        }

        try {
            const response = await fetch('http://localhost:8000/api/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, email, password }),
            })

            if (!response.ok) {
                setError(await extractErrorMessage(response, 'An error occurred during registration'))
                return
            }

            setPassword('')
            setConfirmPassword('')
            setInfo('Account created! Log in to continue.')
            setActiveTab('login')
        } catch {
            setError('Server error — is the API running on localhost:8000?')
        }
    }

    return (
        <div className="modal-backdrop" onClick={onClose}>
            <div className="panel modal-panel" onClick={(e) => e.stopPropagation()}>
                <button type="button" className="modal-close" onClick={onClose} aria-label="Close">
                    &times;
                </button>

                <div className="brand" style={{ justifyContent: 'center', marginBottom: '28px', fontSize: '15px' }}>
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="oklch(0.75 0.15 230)" strokeWidth="1.6" strokeLinejoin="round">
                        <path d="M12 2l9 5v10l-9 5-9-5V7l9-5z" />
                        <path d="M12 8l4 2.3v4.4L12 17l-4-2.3v-4.4L12 8z" />
                    </svg>
                    <span>DISCIPLINE LEVELING</span>
                </div>

                <div className="tabs">
                    <button
                        type="button"
                        className={`tab ${activeTab === 'login' ? 'active' : ''}`}
                        onClick={() => switchTab('login')}
                    >
                        Login
                    </button>
                    <button
                        type="button"
                        className={`tab ${activeTab === 'register' ? 'active' : ''}`}
                        onClick={() => switchTab('register')}
                    >
                        Register
                    </button>
                </div>

                {activeTab === 'login' ? (
                    <>
                        <h2 style={{ fontSize: '19px', marginBottom: '6px' }}>Welcome back</h2>
                        <p style={{ fontSize: '13px', color: 'var(--text-muted)', margin: '0 0 24px' }}>
                            Log in to continue your progression.
                        </p>

                        {info && <p className="msg-success" style={{ marginBottom: '16px' }}>{info}</p>}
                        {error && <p className="msg-error" style={{ marginBottom: '16px' }}>{error}</p>}

                        <form className="form-stack" onSubmit={handleLogin}>
                            <div>
                                <label>Email</label>
                                <input
                                    className="field"
                                    type="email"
                                    placeholder="you@example.com"
                                    value={email}
                                    onChange={(e) => setEmail(e.target.value)}
                                />
                            </div>
                            <div>
                                <label>Password</label>
                                <input
                                    className="field"
                                    type="password"
                                    placeholder="********"
                                    value={password}
                                    onChange={(e) => setPassword(e.target.value)}
                                />
                            </div>
                            <button type="submit" className="btn-primary" style={{ marginTop: '8px' }}>
                                Login
                            </button>
                        </form>
                    </>
                ) : (
                    <>
                        <h2 style={{ fontSize: '19px', marginBottom: '6px' }}>Create your account</h2>
                        <p style={{ fontSize: '13px', color: 'var(--text-muted)', margin: '0 0 24px' }}>
                            The first step before creating your character.
                        </p>

                        {error && <p className="msg-error" style={{ marginBottom: '16px' }}>{error}</p>}

                        <form className="form-stack" onSubmit={handleRegister}>
                            <div>
                                <label>Username</label>
                                <input
                                    className="field"
                                    type="text"
                                    placeholder="Ex: Nika"
                                    value={name}
                                    onChange={(e) => setName(e.target.value)}
                                />
                            </div>
                            <div>
                                <label>Email</label>
                                <input
                                    className="field"
                                    type="email"
                                    placeholder="you@example.com"
                                    value={email}
                                    onChange={(e) => setEmail(e.target.value)}
                                />
                            </div>
                            <div>
                                <label>Password</label>
                                <input
                                    className="field"
                                    type="password"
                                    placeholder="********"
                                    value={password}
                                    onChange={(e) => setPassword(e.target.value)}
                                />
                            </div>
                            <div>
                                <label>Confirm Password</label>
                                <input
                                    className="field"
                                    type="password"
                                    placeholder="********"
                                    value={confirmPassword}
                                    onChange={(e) => setConfirmPassword(e.target.value)}
                                />
                            </div>
                            <button type="submit" className="btn-primary" style={{ marginTop: '8px' }}>
                                Create my account
                            </button>
                        </form>
                    </>
                )}
            </div>
        </div>
    )
}

export default AuthModal
