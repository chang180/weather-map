# 系統架構

Weather Map 由 Vue/Vite 前端、專案內原生 PHP API（`api/`）與 CWA Open Data 組成。正式環境採共享空間同站部署（前端靜態檔 + PHP API 同源），本機開發可透過 Herd 呼叫 `public/api/weather.php`（由 `npm run sync-api` 同步產生）。

## 前端元件樹

```text
App.vue
├─ Header（站名、定位按鈕、更新時間）
├─ UmbrellaBanner.vue
├─ CurrentWeatherCard.vue
├─ MetricGrid.vue
├─ ForecastPanel.vue
└─ WeatherMap.vue
```

Dashboard 以氣象資訊為主，地圖是輔助區塊。手機版採單欄排列，桌機版在即時觀測與指標區使用雙欄配置。

## 資料流

```text
useWeather()
  ├─ navigator.geolocation / fallback 台灣中心
  ├─ src/api.ts:getWeather(lat, lon)
  └─ WeatherResponse
      ├─ advice → UmbrellaBanner
      ├─ current + location + meta → CurrentWeatherCard
      ├─ current → MetricGrid
      ├─ forecast.days → ForecastPanel
      └─ current + mapCenter → WeatherMap
```

前端只消費 `WeatherResponse`。帶傘建議的 `urgent` / `suggest` / `none` 由 PHP API 計算，前端 `src/utils/umbrellaAdvice.ts` 僅負責顯示文字、圖示與 CSS class。

## API 與部署

前端預設呼叫同源 API：

```text
/api/weather.php?lat={lat}&lon={lon}
```

Vite dev server 使用 `/api` proxy 到 Herd：

```text
http://localhost:3000/api/* → https://weather-map.test/api/*
```

正式部署時需先部署 `api/`，再部署前端靜態檔，避免前端與 API 合約不同步。
