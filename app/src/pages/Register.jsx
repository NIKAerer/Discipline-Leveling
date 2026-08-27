import { useState } from 'react'
import { useNavigate } from 'react-router-dom'

function Register() {
    const [name, setName] = useState('')
    const [email, setEmail] = useState('')
    const [password, setPassword] = useState('')
    const [confirmPassword, setConfirmPassword] = useState('')
    const [error, setError] = useState('')
    const navigate = useNavigate()
    


    async function handleSubmit(e) {
        e.preventDefault()

        if (password !== confirmPassword) {
            setError("Passwords don't match")
            return
        }

        try {
            const response = await fetch("http://localhost:8000/api/register",{
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, email, password }),
            })

            if (!response.ok) {
                setError('An error occurred during registration')
                return
            }

            navigate('/login')
        }   catch(err) {
            setError('Server error')
        }
    }

    return (
        <div>
            <h1>Register</h1>
            <form onSubmit={handleSubmit}>
                <input
                    type="text"
                    placeholder="Username"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                />
            
                <input
                    type="email"
                    placeholder="Email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                />

                <input
                    type="password"
                    placeholder="Password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                />

                <input
                    type='password'
                    placeholder='Confirm Password'
                    value={confirmPassword}
                    onChange={(e) => setConfirmPassword(e.target.value)}
                />

                {error && <p style={{color : 'red'}}>{error}</p>}

                <button type='submit'>Register</button>
            </form>
        </div>
    )
}

export default Register