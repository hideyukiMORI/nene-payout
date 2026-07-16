import { defineConfig } from 'vite'
import { fileURLToPath } from 'node:url'

/**
 * Builds the standalone embeddable loader `widget.js` (IIFE, no dependencies)
 * into the served assets dir alongside the admin bundle. Kept separate from the
 * main app build so the host page loads a tiny, framework-free script.
 *
 *   npm run build:widget
 */
export default defineConfig({
  build: {
    outDir: '../public_html/assets',
    emptyOutDir: false,
    lib: {
      entry: fileURLToPath(new URL('./widget-loader/widget.ts', import.meta.url)),
      name: 'NenePayoutWidget',
      formats: ['iife'],
      fileName: () => 'widget.js',
    },
  },
})
