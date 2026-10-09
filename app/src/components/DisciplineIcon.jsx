// Emoji de la discipline (🎮, 💻…), ou le point lumineux par défaut.
function DisciplineIcon({ icon }) {
    if (!icon) {
        return <span className="disc-dot" />
    }

    return <span className="disc-icon" aria-hidden="true">{icon}</span>
}

export default DisciplineIcon
