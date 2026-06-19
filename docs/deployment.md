# 部署流程

Weather Map 採「前端靜態檔 + 原生 PHP API」同站部署到共享空間。

## 本機與正式環境的目錄差異

| 用途 | 本機 Herd / Lerd | 正式共享空間 |
| --- | --- | --- |
| 站台根目錄 | 專案內的 `public/` | 主機實際站台根目錄（如 `public_html/`） |
| PHP API 來源 | `api/` 原始碼，經 `npm run sync-api` **複製**到 `public/api/` | 直接上傳根目錄 `api/` 到站台根目錄下的 `api/` |
| 前端靜態檔 | `npm run build` 產出後放在 `public/` | 上傳 `public/` 內容到站台根目錄 |
| `public/api/` | 僅本機同步產物，已列入 `.gitignore` | **不使用**；正式環境 API 路徑為站台根目錄的 `/api/`，不是 `public/api/` |

> **給 AI / 自動化部署的提醒**：`npm run sync-api` 與 `public/api/` 只服務本機開發（Herd / Lerd）。正式環境請上傳根目錄 `api/`，不要依賴 `public/api/` 或在本機執行 `sync-api` 後再部署。

## API 部署（正式環境）

API 原始碼位於根目錄 `api/`。正式環境需將整個 `api/` 目錄上傳到共享空間站台根目錄，並讓站台可由同源路徑存取：

```text
/api/weather.php
```

共享主機必須設定環境變數：

```env
CWA_API_KEY=your-cwa-api-key
REDIS_HOST=your-redis-host
REDIS_PORT=6379
REDIS_PASSWORD=your-redis-password
```

Redis 用於快取 CWA 與 NLSC 回應（3 小時）。若 Redis 無法連線，API 仍可直接打上游服務；但建議正式環境配置外部 Redis 以降低 CWA 延遲。

PHP 需啟用 `redis` 擴充才能使用快取。若共享主機無法設定環境變數，可在部署目標的專案根目錄放置 `.env`，但不得提交到 git。

## 前端部署（正式環境）

前端靜態檔由 Vite build 產出，並同步到 `public/`。正式部署時，需再將 `public/` **內的內容**複製到站台根目錄。

> **共享空間執行緒限制**：Vite 8 使用 Rolldown（Rust/Rayon）建置，共享主機的 OS 執行緒數量有限，`npm run build` 直接執行會報錯：`ThreadPoolBuildError { kind: IOError ... "Resource temporarily unavailable" }`。必須加上環境變數限制執行緒數：

```bash
npm run typecheck
GOMAXPROCS=1 RAYON_NUM_THREADS=1 npm run build
```

build 完成後，將 `public/` 內容覆蓋到站台根目錄（本專案站台根目錄即專案根目錄本身）：

```bash
cp public/index.html ./index.html
rm -rf assets && cp -r public/assets ./assets
```

> **注意**：`npm run sync-api` 僅供本機 Herd / Lerd 開發使用，正式環境部署不需執行。

## 同站部署清單（正式環境）

正式站台根目錄需同時具備：

- 前端靜態檔：`index.html`、`assets/`、`images/` 等（來自 build 後的 `public/` 內容）
- API 目錄：站台根目錄下的 `api/`（來自專案根目錄 `api/` 原始碼），需可由 `/api/weather.php` 存取
- 外部 Redis：可由 PHP `redis` 擴充連線，用於 CWA / NLSC 快取

## 本機開發（Herd / Lerd）

Herd（macOS / Windows）或 Lerd（Linux）會從專案內的 `public/` 提供本機站台 `weather-map.test`。Linux 首次設定請見 [開發環境文件](development.md#linuxlerd)。首次 clone 後請先執行：

```bash
npm run build
npm run sync-api
```

之後若只修改 API，執行 `npm run sync-api` 即可。修改 `api/` 後，執行：

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
- Redis 連線參數已設定，或已接受無 Redis 時直接打上游 API。
- `.env` 不應公開成可瀏覽清單。
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
