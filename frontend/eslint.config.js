import js from '@eslint/js'
import eslintConfigPrettier from 'eslint-config-prettier'
import importPlugin from 'eslint-plugin-import'
import jsxA11y from 'eslint-plugin-jsx-a11y'
import reactHooks from 'eslint-plugin-react-hooks'
import reactRefresh from 'eslint-plugin-react-refresh'
import globals from 'globals'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
import tseslint from 'typescript-eslint'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

// FSD private entity files — only their own `index.ts` may be imported by upper layers.
const entityInternalFiles = [
  './src/entities/*/api-types.ts',
  './src/entities/*/mapper.ts',
  './src/entities/*/queries.ts',
  './src/entities/*/mutations.ts',
  './src/entities/*/query-keys.ts',
  './src/entities/*/ids.ts',
  './src/entities/*/model.ts',
  './src/entities/*/enum.ts',
]

const importZones = [
  { target: './src/features', from: entityInternalFiles },
  { target: './src/features', from: './src/shared/api' },
  { target: './src/pages', from: entityInternalFiles },
  { target: './src/pages', from: './src/shared/api' },
  { target: './src/shared/ui', from: './src/entities' },
  { target: './src/shared/ui', from: './src/features' },
  { target: './src/shared/ui', from: './src/shared/api' },
]

export default tseslint.config(
  {
    ignores: ['dist', 'node_modules', 'coverage', '../public_html/assets'],
  },
  {
    extends: [js.configs.recommended, ...tseslint.configs.strictTypeChecked],
    files: ['src/**/*.{ts,tsx}', 'tests/**/*.{ts,tsx}'],
    languageOptions: {
      ecmaVersion: 2023,
      globals: globals.browser,
      parserOptions: {
        project: ['./tsconfig.json'],
        tsconfigRootDir: __dirname,
      },
    },
    plugins: {
      'react-hooks': reactHooks,
      'react-refresh': reactRefresh,
      'jsx-a11y': jsxA11y,
      import: importPlugin,
    },
    settings: {
      'import/resolver': {
        typescript: { project: './tsconfig.json' },
      },
    },
    rules: {
      ...reactHooks.configs.recommended.rules,
      'react-refresh/only-export-components': ['warn', { allowConstantExport: true }],
      ...jsxA11y.configs.recommended.rules,
      // Numbers in template literals are safe and needed for RHF typed
      // field-array paths (e.g. `taxBreakdown.${index}.taxAmount`).
      '@typescript-eslint/restrict-template-expressions': ['error', { allowNumber: true }],
      'import/no-restricted-paths': ['error', { zones: importZones }],
      'no-restricted-syntax': [
        'error',
        {
          selector: 'JSXAttribute[name.name="className"] Literal[value=/\\[.*\\]/]',
          message: 'Tailwind arbitrary values are forbidden outside shared/ui/theme.',
        },
      ],
    },
  },
  {
    // Tests may use jsdom/node globals and looser type-aware rules.
    files: ['tests/**/*.{ts,tsx}', 'src/**/*.test.{ts,tsx}'],
    languageOptions: {
      globals: { ...globals.browser, ...globals.node },
    },
    rules: {
      '@typescript-eslint/no-non-null-assertion': 'off',
      // Test render helpers legitimately export both components and utilities.
      'react-refresh/only-export-components': 'off',
    },
  },
  eslintConfigPrettier,
)
