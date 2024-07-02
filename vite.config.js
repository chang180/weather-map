import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    watch: {
      usePolling: true, // 針對文件變更的輪詢設置
      interval: 1000, // 每秒檢查文件變更一次
    },
  },
})
