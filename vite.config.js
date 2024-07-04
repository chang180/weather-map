import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [vue()],
  base: '/weather-map/',
  build: {
    outDir: 'dist',
    manifest: true,
    rollupOptions: {
      input: {
        main: 'index.html',
      },
    },
  },
  server: {
    port: 3000,
  },
});
