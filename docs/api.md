# API 合約

Weather Map 後端入口為 `api/weather.php`，正式環境採 **同站部署**，前端以同源路徑呼叫：

```text
/api/weather.php
```

## Endpoint

```http
GET /api/weather.php?lat={lat}&lon={lon}
```

| 參數 | 必填 | 說明 |
|------|------|------|
| `lat` | 是 | 使用者緯度，目前限制在台灣周邊範圍 |
| `lon` | 是 | 使用者經度，目前限制在台灣周邊範圍 |

## 成功回應

```json
{
  "meta": {
    "fetchedAt": "2026-06-05T06:24:23+00:00",
    "locationMethod": "nearest_station",
    "datasets": {
      "observation": "O-A0003-001",
      "forecast": "F-D0047-061"
    },
    "stationDistanceKm": 0.58
  },
  "location": {
    "lat": 25.04,
    "lon": 121.52,
    "county": "臺北市",
    "town": "中正區"
  },
  "current": {
    "stationName": "臺北",
    "stationId": "466920",
    "observedAt": "2026-06-05T14:00:00+08:00",
    "temperature": 26.7,
    "humidity": 95,
    "pressure": 1000.5,
    "windSpeed": 1.4,
    "windDirection": 180,
    "weatherText": "陰有雨",
    "rainfall10min": 40.5,
    "uvIndex": 2
  },
  "forecast": {
    "locationName": "中正區",
    "days": [
      {
        "date": "2026-06-05",
        "wx": "短暫陣雨或雷雨",
        "maxTemp": 28,
        "minTemp": 28,
        "pop": 80,
        "periods": []
      }
    ]
  },
  "advice": {
    "level": "urgent",
    "reason": "目前或未來短時間有降雨風險，建議立即帶傘。",
    "icon": "umbrella"
  }
}
```

`forecast.days[].periods` 會包含各 3 小時區間的 `startTime`、`endTime`、`wx`、`temperature`、`pop`。

## 錯誤回應

所有錯誤使用同一格式：

```json
{
  "error": {
    "code": "MISSING_LOCATION",
    "message": "lat and lon query parameters are required"
  }
}
```

常見錯誤碼：

| code | HTTP | 說明 |
|------|------|------|
| `MISSING_API_KEY` | 500 | 伺服器未設定 `CWA_API_KEY` |
| `MISSING_LOCATION` | 400 | 缺少 `lat` 或 `lon` |
| `INVALID_QUERY` | 400 | `lat` 或 `lon` 不是數字 |
| `LOCATION_OUT_OF_RANGE` | 400 | 座標不在台灣周邊範圍 |
| `WEATHER_SERVICE_ERROR` | 502 | CWA 連線、解析或位置匹配失敗 |
