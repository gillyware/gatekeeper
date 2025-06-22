import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

export default defineConfig({
  plugins: [react(), tailwindcss()],
  build: {
    outDir: 'dist',
    assetsDir: '',
    manifest: true,
    rollupOptions: {
      input: ['resources/css/app.css', 'resources/js/app.tsx'],
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: '[name].js',
        assetFileNames: '[name].[ext]',
      }
    }
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
      '@components': path.resolve(__dirname, 'resources/js/components'),
    },
  },
})
