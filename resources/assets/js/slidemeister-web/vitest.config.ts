import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname),
    },
  },
  test: {
    globals: true,
    environment: 'happy-dom',
    setupFiles: ['./__tests__/setup.ts'],
    include: ['./__tests__/**/*.test.ts'],
    coverage: {
      provider: 'v8',
      include: ['./**/*.ts'],
      exclude: ['__tests__/**', '*.d.ts', '*.config.ts', 'node_modules/**'],
    },
  },
})
