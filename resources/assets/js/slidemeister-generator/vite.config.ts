import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'
import fs from 'fs'

// Resolve up to the Laravel root (partymeister-template)
const laravelRoot = path.resolve(__dirname, '../../../../../..')
const hotFile = path.resolve(laravelRoot, 'public/hot-slidemeister-generator')

export default defineConfig({
  plugins: [
    vue(),
    {
      name: 'hot-file',
      configureServer(server) {
        fs.writeFileSync(hotFile, 'http://localhost:5175')
        server.httpServer?.on('close', () => {
          fs.rmSync(hotFile, { force: true })
        })
      },
    },
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname),
      '@common': path.resolve(__dirname, '../slidemeister-common'),
    },
  },
  build: {
    outDir: path.resolve(laravelRoot, 'public/build/slidemeister-generator'),
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: path.resolve(__dirname, 'main.ts'),
    },
  },
  server: {
    port: 5175,
    origin: 'http://localhost:5175',
  },
})
