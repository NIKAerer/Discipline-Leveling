import { useState, useEffect } from 'react'

function App() {
  const [message, setMessage] = useState('Chargement...')

  useEffect(() => {
    fetch('http://localhost:8000/api/test')
      .then((response) => response.json())
      .then((data) => setMessage(data.message))
      .catch(() => setMessage('Erreur : impossible de contacter Symfony'))
  }, [])

  return (
    <div style={{ padding: '40px', fontFamily: 'sans-serif' }}>
      <h1>Discipline Leveling</h1>
      <p>Message reçu de l'API Symfony :</p>
      <p><strong>{message}</strong></p>
    </div>
  )
}

export default App