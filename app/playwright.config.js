import { defineConfig, devices } from '@playwright/test'

// Tests de bout en bout : l'API Symfony et le front tournent pour de vrai.
// Prérequis : base de données prête avec le compte de démo (composer setup dans api/).
export default defineConfig({
    testDir: './e2e',
    testMatch: '**/*.e2e.js',
    fullyParallel: false,
    retries: process.env.CI ? 1 : 0,
    reporter: process.env.CI ? 'github' : 'list',
    use: {
        baseURL: 'http://127.0.0.1:4173',
        trace: 'retain-on-failure',
        viewport: { width: 1280, height: 800 },
    },
    projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'], viewport: { width: 1280, height: 800 } } }],
    webServer: [
        {
            command: 'php -S 127.0.0.1:8000 public/index.php',
            cwd: '../api',
            url: 'http://127.0.0.1:8000/api/disciplines',
            reuseExistingServer: !process.env.CI,
        },
        {
            command: 'npm run build && npm run preview -- --host 127.0.0.1 --port 4173 --strictPort',
            url: 'http://127.0.0.1:4173',
            env: { VITE_API_URL: 'http://127.0.0.1:8000' },
            reuseExistingServer: !process.env.CI,
        },
    ],
})
