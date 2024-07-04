import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [vue()],
  base: '/weather-map/', // 替換為您的 repository 名稱
  build: {
    manifest: true,
    outDir: 'dist', // 替換為您的 repository 名稱
  },
});
