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

- `public/`：前端靜態檔（`index.html`、`assets/`、`images/` 等）
- `api/`：PHP API 原始碼（包含 `weather.php`、`bootstrap.php`、各 service）

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

## 驗收（正式站台）

在正式站台（需 HTTPS 才能使用瀏覽器定位）確認 API 可用：

```bash
curl -ks "https://<你的網域>/api/weather.php?lat=25.04&lon=121.52" | jq .
```
