import { Routes, Route } from 'react-router-dom'
import Home from './pages/Home'
import CreateCharacter from './pages/CreateCharacter'
import Dashboard from './pages/Dashboard'
import DisciplineDetail from './pages/DisciplineDetail'
import Profile from './pages/Profile'
import LolTracker from './pages/LolTracker'
import ProtectedRoute from './components/ProtectedRoute'

function App() {
  return (
    <Routes>
      <Route path="/" element={<Home />} />
      <Route
        path="/create-character"
        element={
          <ProtectedRoute>
            <CreateCharacter />
          </ProtectedRoute>
        }
      />
      <Route
        path="/dashboard"
        element={
          <ProtectedRoute>
            <Dashboard />
          </ProtectedRoute>
        }
      />
      <Route
        path="/discipline/:id"
        element={
          <ProtectedRoute>
            <DisciplineDetail />
          </ProtectedRoute>
        }
      />
      <Route
        path="/profile"
        element={
          <ProtectedRoute>
            <Profile />
          </ProtectedRoute>
        }
      />
      <Route
        path="/lol"
        element={
          <ProtectedRoute>
            <LolTracker />
          </ProtectedRoute>
        }
      />
    </Routes>
  )
}

export default App
