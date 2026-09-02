import { Link, useLocation, useNavigate } from 'react-router-dom'

function NavBar() {
    const location = useLocation()
    const navigate = useNavigate()

    function handleLogout() {
        localStorage.removeItem('token')
        navigate('/')
    }

    return (
        <div className="navbar">
            <div style={{ display: 'flex', alignItems: 'center' }}>
                <div className="brand">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="oklch(0.75 0.15 230)" strokeWidth="1.6" strokeLinejoin="round">
                        <path d="M12 2l9 5v10l-9 5-9-5V7l9-5z" />
                        <path d="M12 8l4 2.3v4.4L12 17l-4-2.3v-4.4L12 8z" />
                    </svg>
                    <span>DISCIPLINE LEVELING</span>
                </div>
                <div className="navlinks">
                    <Link to="/dashboard" className={`navlink ${location.pathname === '/dashboard' ? 'active' : ''}`}>
                        Dashboard
                    </Link>
                    <span className="navlink disabled" title="Coming soon">
                        Disciplines
                    </span>
                    <Link to="/profile" className={`navlink ${location.pathname === '/profile' ? 'active' : ''}`}>
                        Profile
                    </Link>
                </div>
            </div>
            <button type="button" className="btn-ghost" onClick={handleLogout}>
                Logout
            </button>
        </div>
    )
}

export default NavBar
