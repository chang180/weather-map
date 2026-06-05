# Weather Map

以 Vue 3、Leaflet 顯示台灣氣象觀測站資料的地圖應用。

- 線上版本：https://chang180.github.io/weather-map/
- 資料來源：`https://chang180backend.com/api/weather.php`

## 環境需求

- Node.js 18+
- npm

本機以 [Laravel Herd](https://herd.laravel.com/) 預覽時，需先安裝並將專案放在 `~/Herd/` 目錄下。

## 安裝

```bash
npm install
```

## 開發

啟動 Vite 開發伺服器（支援熱更新）：

```bash
npm run dev
```

開啟 http://localhost:3000

## 本機預覽（Herd）

Herd 會從 `public/` 目錄提供靜態檔案，因此需先建置並同步產物：

```bash
npm run build
```

接著以 **HTTPS** 開啟：

```
https://weather-map.test
```

> **定位功能需 HTTPS。** 若尚未啟用憑證，可執行：
>
> ```bash
> herd secure weather-map
> ```

### 指令說明

| 指令 | 用途 | `base` 路徑 |
|------|------|-------------|
| `npm run build` | 本機 Herd 建置，並同步至 `public/` | `/` |
| `npm run build:pages` | GitHub Pages 建置，產出至 `dist/` | `/weather-map/` |
| `npm run sync-public` | 將 `dist/` 產物複製到 `public/`（由 `build` 自動呼叫） | — |

## 外部 API 依賴

此專案為**純前端靜態網站**，氣象資料在瀏覽器端即時向外部 API 請求：

```
https://chang180backend.com/api/weather.php
```

### 部署 vs 執行

| 階段 | 是否呼叫 API | 說明 |
|------|-------------|------|
| `npm run build:pages` | 否 | 只打包前端靜態檔，建置本身不會因 API 失敗 |
| `npm run deploy` | 否 | 只推送 `dist/` 至 `gh-pages` 分支 |
| 使用者開啟網頁 | **是** | 瀏覽器向 API 取資料，API 無回應時地圖無法顯示 |

目前此 API 已回傳 `Access-Control-Allow-Origin: *`，從 `https://chang180.github.io` 可跨域請求。若日後 API 變更 CORS 政策、下線或回應逾時，GitHub Pages 上的網站仍會載入，但**地圖資料會無法顯示**。

## 部署至 GitHub Pages

此專案使用 [`gh-pages`](https://www.npmjs.com/package/gh-pages) 將 `dist/` 推送至 `gh-pages` 分支。

```bash
npm run deploy
```

部署完成後，網址為：

```
https://chang180.github.io/weather-map/
```

### 部署前本地驗證

可先模擬 GitHub Pages 環境確認路徑是否正確：

```bash
npm run preview:pages
```

開啟終端機顯示的預覽網址（通常為 http://localhost:4173/weather-map/）。

### GitHub Pages 設定

Repository → **Settings** → **Pages**：

- **Source**：Deploy from a branch
- **Branch**：`gh-pages` / `/ (root)`

`npm run deploy` 會自動更新 `gh-pages` 分支，無需手動上傳 `dist/`。

## 推送至 GitHub 注意事項

以下為建置產物，**不需提交**至 `main` 分支（已列入 `.gitignore`）：

- `dist/`
- `public/index.html`
- `public/assets/`

推送原始碼後，在本地執行 `npm run deploy` 即可更新線上版本。

若 `dist/` 先前已被 git 追蹤，可執行以下指令移除追蹤（不刪除本地檔案）：

```bash
git rm -r --cached dist public/index.html public/assets
```

## 專案結構

```
├── public/          # 靜態資源（images、vite.svg）；建置後會產生 index.html、assets/
├── src/
│   ├── api.js       # 氣象 API
│   ├── App.vue
│   └── WeatherMap.vue
├── index.html       # Vite 開發入口（勿與 public/index.html 混淆）
├── vite.config.js   # base 路徑由 VITE_BASE_PATH 控制
└── dist/            # 建置輸出（GitHub Pages 部署來源）
```

## 常見問題

**本機開啟後沒有詢問定位權限？**

瀏覽器定位 API 僅在 HTTPS（或 `localhost`）下可用。請確認使用 `https://weather-map.test`，或點擊地圖右上角的 📍 按鈕手動定位。

**GitHub Pages 上圖示或資源 404？**

確認使用 `npm run build:pages` 或 `npm run deploy` 建置，而非 `npm run build`。GitHub Pages 需要 `/weather-map/` 前綴，與 repository 名稱一致。

**GitHub Pages 有畫面但地圖是空的？**

1. 確認已執行 `npm run deploy` 更新 `gh-pages`（線上可能仍是舊版建置）
2. 開啟瀏覽器開發者工具 → Network，檢查 `weather.php` 是否回傳 200
3. 允許網站使用定位，或點擊右上角 📍 按鈕
4. 若 API 請求失敗，畫面會顯示錯誤提示；需確認 `chang180backend.com` 是否正常運作
