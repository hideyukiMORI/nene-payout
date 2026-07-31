// Repo-root ESLint config — E2E specs only (#263).
//
// ESLint cannot lint files that sit above its own config file, and the Playwright
// specs live at the repo root (`tests/e2e/`, 判例4) while the app and its node
// deps live in `frontend/`. So `npm run lint` runs eslint twice: once inside
// frontend/ for the app, and once from here via `npm run lint:e2e`.
//
// This re-exports ONLY `e2eConfig` — never frontend/eslint.config.js as a whole.
// The app blocks there are typed (projectService) and would match `tests/e2e/**`
// from this root, failing with "file was not found in any of the provided
// project(s)". The reason lives in frontend/eslint.e2e.config.js too.
import { e2eConfig } from './frontend/eslint.e2e.config.js'

export default e2eConfig
