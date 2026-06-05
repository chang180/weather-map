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

## 本機 API 同步

`api/` 是 PHP API 原始碼；Herd 本機站台從 `public/` 服務檔案。修改 API 後請執行：

```bash
npm run sync-api
```

再以 Herd HTTPS 驗證：

```bash
curl -ks "https://weather-map.test/api/weather.php?lat=25.04&lon=121.52" | jq .
```

## 前端 API URL

前端預設以同源 API 讀取資料：

```ts
const API_URL = import.meta.env.VITE_API_URL ?? '/api/weather.php'
```

一般開發不需要設定 `VITE_API_URL`。若要覆寫，只能設定 API 路徑或 API URL，**不可**把 `CWA_API_KEY` 放進任何 `VITE_*` 變數。

Vite dev server 已設定 `/api` proxy 到 Herd：

```text
http://localhost:3000/api/weather.php?lat=25.04&lon=121.52
→ https://weather-map.test/api/weather.php?lat=25.04&lon=121.52
```

因此使用 `npm run dev` 前，請先確認已執行 `npm run sync-api`，且 Herd 的 `https://weather-map.test` 可正常回應 PHP API。

## 正式部署方式

正式環境改為部署到共享空間（同站部署），前端以同源 `/api/weather.php` 取得資料，詳見 [部署文件](deployment.md)。

<!-- TODO: Phase X 補齊 -->
