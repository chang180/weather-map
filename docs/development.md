# 開發環境

本專案開發環境需要 Node.js 22+、npm、Vite，以及本機 PHP 站台（macOS / Windows 用 Laravel Herd，Linux 用 [Lerd](https://geodro.github.io/lerd/)）。CWA 授權碼請設定為環境變數 `CWA_API_KEY`，或在本機根目錄建立不提交版控的 `.env`：

```env
CWA_API_KEY=your-cwa-api-key-here
```

可參考 `.env.example` 建立本機設定檔。

## Redis（外部伺服器）

Redis 由外部伺服器提供，用於快取 CWA 資料集與 NLSC 行政區反查結果（TTL 3 小時）。請在 `.env` 設定連線參數（可參考 `.env.example`）：

```env
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=your-redis-password
```

本機 PHP 需啟用 `redis` 擴充（Herd / Lerd 預設可用；Lerd 若缺少可執行 `lerd php:ext add redis`）。若 Redis 無法連線，API 會自動 fallback 直接打 CWA / NLSC，不會中斷服務。

行政區顯示使用 NLSC 反查；即時觀測仍取最近測站。永和座標驗證範例：

```bash
curl -ks "https://weather-map.test/api/weather.php?lat=25.008&lon=121.515" | jq '.location, .meta'
```

## TypeScript

前端原始碼使用 TypeScript。`src/` 內入口與 API 模組使用 `.ts`，Vue SFC 使用 `<script setup lang="ts">`。

開發或 Review 前請執行：

```bash
npm run typecheck
npm run build
```

## 本機 PHP 站台

| 平台 | 工具 | 站台網址 |
| --- | --- | --- |
| macOS / Windows | Laravel Herd | `https://weather-map.test` |
| Linux | [Lerd](https://geodro.github.io/lerd/) | `https://weather-map.test` |

兩者皆從專案內的 `public/` 服務站台。Vite dev server 預設將 `/api` proxy 到 `https://weather-map.test`，一般不需要設定 `DEV_API_PROXY_TARGET`。

### Linux（Lerd）

專案根目錄已附 `.lerd.yaml`（`public_dir: public`、`domains: [weather-map]`、`secured: true`）。首次在本機設定：

```bash
# 一次性設定（腳本內有 2 步需要 sudo：安裝 podman、開放 80/443 給 rootless nginx）
bash scripts/setup-lerd-linux.sh
```

腳本會安裝 Lerd CLI（`~/.local/bin`，不需 sudo）、初始化 rootless Podman、執行 `lerd link` 並驗證 API。若偏好手動，亦可分開執行：

```bash
sudo apt-get install -y podman libnss3-tools
sudo sysctl -w net.ipv4.ip_unprivileged_port_start=80
curl -fsSL https://raw.githubusercontent.com/geodro/lerd/main/install.sh | bash
lerd install && npm run sync-api && lerd link
```

驗證 API：

```bash
curl -ks "https://weather-map.test/api/weather.php?lat=25.04&lon=121.52" | jq .
```

日常啟動：

```bash
lerd start          # 若 Lerd 尚未在背景運行
npm run sync-api    # 僅在修改 api/ 後需要
npm run dev         # 前端熱更新：http://localhost:3000
```

瀏覽器網址：

- **改前端（HMR）**：`http://localhost:3000`（Vite 將 `/api` proxy 到 `weather-map.test`）
- **靜態預覽（同源）**：`https://weather-map.test`（需先 `npm run build`）

Lerd 使用外部 Redis（`.env` 的 `REDIS_*`），不需在本機啟動 Redis 服務。`php -S` 或 Docker 等備援方案仍可用，屆時在 `.env` 設定 `DEV_API_PROXY_TARGET` 覆寫 Vite proxy 即可。

### macOS / Windows（Herd）

Herd 從專案內的 `public/` 服務 `weather-map.test`。clone 後若 Herd 顯示 404，通常是 `public/` 尚未有建置產物。請執行：

```bash
npm run build
npm run sync-api
```

`public/index.html`、`public/assets/`、`public/api/` 皆為本機產物，已列入 `.gitignore`，不會隨 git 下載。Lerd 使用者同樣需要先執行上述指令。

## 本機 API 同步

`api/` 是 PHP API 原始碼。正式環境直接部署根目錄 `api/`；本機開發則需把 `api/` 複製到 `public/api/`。修改 API 後請執行：

```bash
npm run sync-api
```

再以 HTTPS 驗證：

```bash
curl -ks "https://weather-map.test/api/weather.php?lat=25.04&lon=121.52" | jq .
```

## 前端 API URL

前端預設以同源 API 讀取資料：

```ts
const API_URL = import.meta.env.VITE_API_URL ?? '/api/weather.php'
```

一般開發不需要設定 `VITE_API_URL`。若要覆寫，只能設定 API 路徑或 API URL，**不可**把 `CWA_API_KEY` 放進任何 `VITE_*` 變數。

Vite dev server 已設定 `/api` proxy 到本機 PHP 站台（Herd 或 Lerd）：

```text
http://localhost:3000/api/weather.php?lat=25.04&lon=121.52
→ https://weather-map.test/api/weather.php?lat=25.04&lon=121.52
```

因此使用 `npm run dev` 前，請先確認已執行 `npm run sync-api`，且 `https://weather-map.test` 可正常回應 PHP API。

地圖輔助區塊會使用 API 回傳的 `stations` 渲染全部測站點位；若調整 PHP API，請確認回應仍包含 `stations` 並至少有可用座標。

## 正式部署方式

正式環境改為部署到共享空間（同站部署），前端以同源 `/api/weather.php` 取得資料，詳見 [部署文件](deployment.md)。
