import globals from 'globals'
import tseslint from 'typescript-eslint'

// Browser E2E specs run under the Playwright (node) runner, not the typed app
// project, so they use the untyped recommended rules (#263).
//
// The specs live at the repo root (`tests/e2e/`, 判例4) and ESLint cannot lint
// files above its own config file. `npm run lint` therefore runs eslint twice:
// once here in frontend/ for the app, and once from the repo root, where
// ../eslint.config.js re-exports THIS array alone. Exporting it separately
// matters — re-exporting the whole config would let the typed app blocks match
// `tests/e2e/**` and fail with "file was not found in any of the provided
// project(s)". (Shape adopted from invoice #733/#735.)
//
// Note: payout carries no `@eslint/js` devDependency — it was dropped in #247
// when the shared nene2-standards form made it redundant — so this config uses
// typescript-eslint's untyped recommended set on its own rather than
// re-introducing the dependency for one file.
export const e2eConfig = tseslint.config({
  files: ['tests/e2e/**/*.ts'],
  extends: [...tseslint.configs.recommended],
  languageOptions: {
    ecmaVersion: 2023,
    globals: { ...globals.node },
  },
})
