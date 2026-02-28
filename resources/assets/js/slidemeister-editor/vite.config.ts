import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

// Resolve up to the Laravel root (partymeister-template)
const laravelRoot = path.resolve(__dirname, '../../../../../..')

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname),
    },
  },
  build: {
    outDir: path.resolve(laravelRoot, 'public/slidemeister-editor'),
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: path.resolve(__dirname, 'main.ts'),
    },
  },
  server: {
    port: 5174,
    origin: 'http://localhost:5174',
  },
})
