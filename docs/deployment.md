# 部署流程

Weather Map 採「前端靜態檔 + 原生 PHP API」同站部署到共享空間。

## API 部署

API 原始碼位於根目錄 `api/`。正式環境需將整個 `api/` 目錄上傳到共享空間，並讓站台可由同源路徑存取：

```text
/api/weather.php
```

共享主機必須設定環境變數：

```env
CWA_API_KEY=your-cwa-api-key
```

若共享主機無法設定環境變數，可在部署目標的專案根目錄放置 `.env`，但不得提交到 git。

## 前端部署（共享空間）

前端靜態檔由 Vite build 產出，並同步到 `public/` 供 Herd / 共享空間直接服務。

```bash
npm run typecheck
npm run build
```

部署時請將 `public/` 內的內容上傳到共享空間站台目錄（依你的主機設定可能是 `public_html/` 或子目錄）。若你的共享空間不是以 `public/` 當站台根目錄，請確保靜態檔可被直接存取。

## 同站部署清單

正式站台需同時具備：

- 前端靜態檔：`public/index.html`、`public/assets/`、`public/images/` 等
- API 目錄：根目錄 `api/` 內容，需可由 `/api/weather.php` 存取
- API cache：`api/cache/` 需可由 PHP 寫入，且不得對外列目錄

## 本機 Herd API

Herd 會從 `public/` 提供本機站台。修改 `api/` 後，執行：

```bash
npm run sync-api
```

驗收：

```bash
curl -ks "https://weather-map.test/api/weather.php?lat=25.04&lon=121.52" | jq .
```

`public/api/` 是本機同步產物，已列入 `.gitignore`。

## 共享空間部署檢查清單

- HTTPS 憑證已啟用；瀏覽器定位需要 HTTPS。
- 伺服器已安全設定 `CWA_API_KEY`，或在不進版控的位置放置 `.env`。
- 站台根目錄已對應到前端靜態檔；若主機不是直接服務 `public/`，需把 `public/` 內容搬到實際站台根目錄。
- `/api/weather.php` 可直接存取，且同源回應 JSON。
- `api/cache/` 目錄存在並可由 PHP 寫入。
- `api/cache/`、`.env` 不應公開成可瀏覽清單。
- 部署順序為先 API、後前端，以避免合約不同步。

## 驗收（正式站台）

在正式站台（需 HTTPS 才能使用瀏覽器定位）確認 API 可用：

```bash
curl -ks "https://<你的網域>/api/weather.php?lat=25.04&lon=121.52" | jq .
```

前端驗收：

1. 開啟正式站台首頁。
2. 允許定位或確認 fallback 可載入。
3. 確認 Dashboard 顯示帶傘建議、即時觀測、三天預報與地圖。
