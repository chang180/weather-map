# 開發環境

本專案開發環境需要 Node.js 22+、npm、Laravel Herd 與 Vite。CWA 授權碼請設定為環境變數 `CWA_API_KEY`，或在本機根目錄建立不提交版控的 `.env`：

```env
CWA_API_KEY=your-cwa-api-key-here
```

可參考 `.env.example` 建立本機設定檔。

## TypeScript

前端原始碼使用 TypeScript。`src/` 內入口與 API 模組使用 `.ts`，Vue SFC 使用 `<script setup lang="ts">`。

開發或 Review 前請執行：

```bash
npm run typecheck
npm run build
```

<!-- TODO: Phase X 補齊 -->
