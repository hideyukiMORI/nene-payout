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
        target: process.env.NENE_PAYOUT_API_URL || 'http://localhost:9000',
        changeOrigin: true,
      },
    },
  },
  build: {
    outDir: '../public_html/assets',
    emptyOutDir: true,
  },
})
