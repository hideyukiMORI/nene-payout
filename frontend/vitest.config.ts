import { defineConfig } from 'vitest/config'
import react from '@vitejs/plugin-react'
import { fileURLToPath } from 'node:url'

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
      '@tests': fileURLToPath(new URL('./tests', import.meta.url)),
    },
  },
  test: {
    environment: 'jsdom',
    globals: true,
    setupFiles: ['./tests/setup/vitest.setup.ts'],
    include: ['src/**/*.test.{ts,tsx}', 'tests/**/*.test.{ts,tsx}'],
    coverage: {
      provider: 'v8',
      // json-summary feeds the shrink-only ratchet (tools/coverage-ratchet.mjs);
      // text is for humans, json for drill-down. Never write html into the repo.
      reporter: ['text-summary', 'json-summary', 'json'],
      reportsDirectory: './coverage',
      // Measure the application source only. Test/story/mock/setup files and
      // type-only barrels are not units under test.
      include: ['src/**/*.{ts,tsx}'],
      exclude: [
        'src/**/*.test.{ts,tsx}',
        'src/**/*.stories.{ts,tsx}',
        'src/**/*.d.ts',
        'src/**/index.ts',
        'src/main.tsx',
        'src/**/__mocks__/**',
        'src/test/**',
        'src/shared/i18n/messages/**',
      ],
    },
  },
})
