import { expect, test } from '@playwright/test'

// Parcours d'un recruteur : il arrive sur l'accueil, essaie la démo,
// visite le tableau de bord, une discipline et le tracker LoL.
// Avec SCREENSHOTS=1, des captures sont enregistrées pour le README.
const screenshots = process.env.SCREENSHOTS === '1'

async function capture(page, name) {
    if (screenshots) {
        await page.screenshot({ path: `../docs/screenshots/${name}.png` })
    }
}

test('un visiteur peut explorer le compte de démo', async ({ page }) => {
    await page.goto('/')
    await expect(page.getByRole('heading', { name: 'Deviens le héros de ta propre progression.' })).toBeVisible()
    await capture(page, '1-accueil')

    await page.getByRole('button', { name: 'Essayer la démo' }).click()
    await page.getByRole('button', { name: 'Se connecter' }).click()

    await expect(page).toHaveURL(/\/dashboard$/)
    await expect(page.getByRole('heading', { name: 'Joueur Démo' })).toBeVisible()
    await expect(page.getByText("jours d'affilée")).toBeVisible()
    await expect(page.locator('.heatmap-cell')).toHaveCount(30)
    await capture(page, '2-tableau-de-bord')

    await page.locator('.discipline-card', { hasText: 'Code' }).click()
    await expect(page.getByRole('heading', { name: 'Code' })).toBeVisible()
    await expect(page.getByRole('heading', { name: 'Quêtes' })).toBeVisible()
    await capture(page, '3-discipline')

    await page.goto('/lol')
    await expect(page.getByRole('heading', { name: 'Tracker LoL' })).toBeVisible()
    await expect(page.getByText('Ahri').first()).toBeVisible()
    await capture(page, '4-tracker-lol')
})

test('valider une quête fait gagner de l\'XP', async ({ page }) => {
    await page.goto('/')
    await page.getByRole('button', { name: 'Essayer la démo' }).click()
    await page.getByRole('button', { name: 'Se connecter' }).click()
    await expect(page).toHaveURL(/\/dashboard$/)

    await page.locator('.discipline-card', { hasText: 'Sport' }).click()
    const quest = page.locator('.quest-row', { hasText: 'Séance de musculation' })
    const wasDone = await quest.evaluate((element) => element.classList.contains('done'))

    await quest.click()

    if (wasDone) {
        await expect(quest).not.toHaveClass(/\bdone\b/)
    } else {
        await expect(quest).toHaveClass(/\bdone\b/)
    }
})
