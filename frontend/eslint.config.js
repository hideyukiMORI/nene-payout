import nene2 from '@hideyukimori/nene2-standards'
import eslintConfigPrettier from 'eslint-config-prettier'
import reactHooks from 'eslint-plugin-react-hooks'
import reactRefresh from 'eslint-plugin-react-refresh'
import globals from 'globals'
import tseslint from 'typescript-eslint'

export default tseslint.config(
  {
    ignores: [
      'dist',
      'node_modules',
      'coverage',
      '../public_html/assets',
      'storybook-static',
      // Build/config files live outside tsconfig; base enables the typed
      // projectService, which errors on files it can't find in a project.
      '*.config.{ts,js,mjs}',
      'tools/**',
      'widget-loader/**',
      '.storybook/**',
      '**/*.mjs',
    ],
  },
  // base enables the typed projectService (auto-discovers tsconfig), so we only
  // supply browser globals here — no explicit parserOptions.project.
  {
    files: ['src/**/*.{ts,tsx}', 'tests/**/*.{ts,tsx}'],
    languageOptions: {
      ecmaVersion: 2023,
      globals: globals.browser,
    },
  },
  // Shared synthesized form (README canonical order). fsd/api/i18n/testing carry
  // the FSD boundaries, transport bans, a11y, and testing-library rules that were
  // previously hand-rolled or missing. styling uses the no-arg FSD-canonical entry.
  ...nene2.base,
  ...nene2.fsd,
  ...nene2.api,
  ...nene2.stylingWith(),
  ...nene2.i18n,
  ...nene2.testing,
  // React hygiene is not part of the fleet form; keep it as a repo-local addition.
  {
    files: ['src/**/*.{ts,tsx}'],
    plugins: { 'react-hooks': reactHooks, 'react-refresh': reactRefresh },
    rules: {
      ...reactHooks.configs.recommended.rules,
      'react-refresh/only-export-components': ['warn', { allowConstantExport: true }],
    },
  },
  // Registered payout exception: numbers in template literals are needed for RHF
  // typed field-array paths (e.g. `taxBreakdown.${index}.taxAmount`).
  {
    files: ['src/**/*.{ts,tsx}'],
    rules: {
      '@typescript-eslint/restrict-template-expressions': ['error', { allowNumber: true }],
    },
  },
  {
    // Tests and stories: jsdom/node globals and looser type-aware rules.
    files: ['tests/**/*.{ts,tsx}', 'src/**/*.test.{ts,tsx}', 'src/**/*.stories.{ts,tsx}'],
    languageOptions: {
      globals: { ...globals.browser, ...globals.node },
    },
    rules: {
      '@typescript-eslint/no-non-null-assertion': 'off',
      // Test render helpers and stories legitimately export non-components.
      'react-refresh/only-export-components': 'off',
    },
  },
  // ── Registered exceptions (hub 裁定 07-21・playbook §7 判例15–19) ──────────────
  // Each is a files×rule override with a reason; scope-off, never inline disable.
  {
    // I18N-13: this is payout's own Intl wrapper. Removal condition: delete this
    // override when B-2 lands nene2-i18n/format and format.ts migrates to it
    // (Phase B ledger B-2). format.ts has no other restricted-syntax usage.
    files: ['src/shared/lib/format.ts'],
    rules: { 'no-restricted-syntax': 'off' },
  },
  {
    // AM-18 false positive: the I18nProvider is the one place AM-18 *permits*
    // setting `lang`. Root fix is in the shared config (exclude the provider);
    // tracked as nene2-fleet-tooling#118. Local override until that lands.
    files: ['src/shared/i18n/i18n-context.tsx'],
    rules: { 'no-restricted-syntax': 'off' },
  },
  {
    // R1⑦ exemption (判例18): '普通'/'当座' are backend wire values (the
    // VendorInputMapper contract / AccountType union), not user-perceived
    // strings — display is separated via ACCOUNT_TYPE_LABEL_KEY + t() in
    // VendorForm. English-ising them would change the backend contract.
    files: ['src/entities/vendor/model.ts', 'src/features/manage-vendors/model/vendor-form.ts'],
    rules: { 'no-restricted-syntax': 'off' },
  },
  {
    // R1⑦ exemption (判例19): language endonyms ('日本語'/'English') are the
    // self-name of each locale and are not translated.
    files: ['src/shared/i18n/locales.ts'],
    rules: { 'no-restricted-syntax': 'off' },
  },
  {
    // Widget surface (判例14・provisional). The embeddable widget is injected
    // into third-party pages and cannot depend on the app's apiClient/i18n, so
    // it uses raw fetch (A-1) and Intl (I18N-13). Permanence is decided in the
    // W2a widget lane; do not treat as permanent.
    files: ['src/app/widget/**/*.{ts,tsx}'],
    rules: { 'no-restricted-globals': 'off', 'no-restricted-syntax': 'off' },
  },
  eslintConfigPrettier,
)
