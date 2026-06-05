import { defineConfig, loadEnv } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');
  return {
  plugins: [vue()],
  base: env.VITE_BASE_PATH || '/',
  build: {
    outDir: 'dist',
    manifest: true,
  },
  server: {
    port: 3000,
    proxy: {
      '/api': {
        target: 'https://weather-map.test',
        changeOrigin: true,
        secure: false,
      },
    },
  },
  publicDir: 'public',
  };
});
