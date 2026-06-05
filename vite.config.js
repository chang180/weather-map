import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [vue()],
  base: process.env.VITE_BASE_PATH || '/',
  build: {
    outDir: 'dist',
    manifest: true,
  },
  server: {
    port: 3000,
  },
  publicDir: 'public',
});
