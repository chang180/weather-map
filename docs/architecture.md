# 系統架構

Weather Map 由 Vue/Vite 前端、專案內原生 PHP API（`api/`）與 CWA Open Data 組成。正式環境採共享空間同站部署（前端靜態檔 + PHP API 同源），本機開發可透過 Herd 呼叫 `public/api/weather.php`（由 `npm run sync-api` 同步產生）。

<!-- TODO: Phase X 補齊 -->
