# Weather Map

以 Vue 3、Leaflet 與原生 PHP API 建立的個人化氣象 Dashboard，提供最近測站觀測、三天預報與出門帶傘建議。

- 部署方式：共享空間同站部署（前端 + 原生 PHP API）
- 完整文件：[docs/README.md](docs/README.md)

## 環境需求

- Node.js 22+
- npm
- 本機 PHP 站台：macOS / Windows 用 [Laravel Herd](https://herd.laravel.com/)；Linux 用 [Lerd](https://geodro.github.io/lerd/)

## 快速開始（本機開發）

```bash
npm install
npm run build
npm run sync-api
npm run dev
```

`npm run build` 與 `npm run sync-api` 會把前端與 API 產物放到 `public/`，供 Herd / Lerd 本機站台使用；Linux 首次設定請見 [開發環境文件](docs/development.md#linuxlerd)。正式部署流程見 [部署文件](docs/deployment.md)。

開啟終端機顯示的 Vite 網址，通常為：

```text
http://localhost:3000
```

本機開發與部署細節請參考 [開發環境文件](docs/development.md) 與 [部署文件](docs/deployment.md)。

## 驗證

```bash
npm run typecheck
GOMAXPROCS=1 RAYON_NUM_THREADS=1 npm run build
```

> 共享空間執行緒限制：Vite 8（Rolldown）需加 `GOMAXPROCS=1 RAYON_NUM_THREADS=1` 才能建置成功。詳見 [部署文件](docs/deployment.md)。

本機 PHP API 修改後，執行 `npm run sync-api` 同步到 `public/api/`。API 合約請見 [docs/api.md](docs/api.md)，部署步驟請見 [docs/deployment.md](docs/deployment.md)。
