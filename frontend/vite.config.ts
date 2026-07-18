import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'
import { fileURLToPath } from 'node:url'

export default defineConfig({
  plugins: [react(), tailwindcss()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    port: Number(process.env.NENE_PAYOUT_FRONTEND_PORT) || 5190,
    proxy: {
      '/api': {
        // Single source of truth is NENE_PAYOUT_PORT; the proxy target follows it.
        // NENE_PAYOUT_API_URL stays as an explicit override (escape hatch). See #170.
        target:
          process.env.NENE_PAYOUT_API_URL ||
          `http://localhost:${process.env.NENE_PAYOUT_PORT || 9000}`,
        changeOrigin: true,
      },
    },
  },
  build: {
    outDir: '../public_html/assets',
    emptyOutDir: true,
  },
})
