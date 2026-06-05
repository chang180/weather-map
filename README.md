# Weather Map

以 Vue 3、Leaflet 顯示台灣氣象觀測資料的地圖應用，後續將升級為以使用者位置為中心的個人化氣象網站。

- 部署方式：共享空間同站部署（前端 + 原生 PHP API）
- 完整文件：[docs/README.md](docs/README.md)

## 環境需求

- Node.js 22+
- npm
- Laravel Herd（本機 HTTPS 預覽與 PHP API 開發）

## 快速開始

```bash
npm install
npm run sync-api
npm run dev
```

開啟終端機顯示的 Vite 網址，通常為：

```text
http://localhost:3000
```

本機 Herd 預覽與部署細節請參考 [開發環境文件](docs/development.md) 與 [部署文件](docs/deployment.md)。

## 驗證

```bash
npm run typecheck
npm run build
```

本機 PHP API 修改後，執行 `npm run sync-api` 同步到 Herd 用的 `public/api/`。API 合約請見 [docs/api.md](docs/api.md)。
