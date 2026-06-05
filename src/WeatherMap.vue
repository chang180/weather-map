<template>
  <div id="map-container">
    <div v-if="locationNotice" class="location-notice">
      {{ locationNotice }}
      <button type="button" class="locate-link" @click="requestLocation">重新定位</button>
    </div>
    <button type="button" class="locate-btn" @click="requestLocation" title="定位到我的位置">
      📍
    </button>
    <div id="map"></div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { getWeatherData } from "./api";
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.fullscreen';
import 'leaflet.fullscreen/dist/Control.FullScreen.css';

const baseUrl = import.meta.env.BASE_URL;

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: `${baseUrl}images/marker-icon-2x.png`,
  iconUrl: `${baseUrl}images/marker-icon.png`,
  shadowUrl: `${baseUrl}images/marker-shadow.png`,
});

const DEFAULT_LAT = 23.6978;
const DEFAULT_LON = 120.9605;

const weatherData = ref([]);
const map = ref(null);
const locationNotice = ref("");
const markersLayer = ref(null);

const geoOptions = {
  enableHighAccuracy: true,
  timeout: 10000,
  maximumAge: 0,
};

const initMap = (lat, lon) => {
  if (map.value) {
    map.value.setView([lat, lon], 12);
    return;
  }

  map.value = L.map("map", {
    fullscreenControl: true,
    fullscreenControlOptions: {
      position: "topleft",
    },
    zoomControl: false, // 移除左側的縮放控制條
  }).setView([lat, lon], 12); // 將地圖中心設置為當前位置，並設置適當的縮放級別

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: false, // 移除底部的標題
  }).addTo(map.value);

  markersLayer.value = L.layerGroup().addTo(map.value);

  weatherData.value.forEach((station) => {
    const stationLat = station.GeoInfo.Coordinates[1].StationLatitude;
    const stationLon = station.GeoInfo.Coordinates[1].StationLongitude;
    const marker = L.marker([stationLat, stationLon]).addTo(markersLayer.value);
    marker.bindPopup(`
        <h3>${station.StationName}</h3>
        <p>Temperature: ${station.WeatherElement.AirTemperature} °C</p>
        <p>Humidity: ${station.WeatherElement.RelativeHumidity} %</p>
        <p>Wind Speed: ${station.WeatherElement.WindSpeed} m/s</p>
        <p>Weather: ${station.WeatherElement.Weather}</p>
      `);
  });

  // 確保地圖在容器大小改變後刷新
  window.addEventListener("resize", () => {
    map.value.invalidateSize();
  });

  // 事件監聽
  map.value.on("enterFullscreen", function () {
    console.log("entered fullscreen");
  });

  map.value.on("exitFullscreen", function () {
    console.log("exited fullscreen");
  });
};

const fetchWeatherData = async (lat, lon) => {
  try {
    const data = await getWeatherData();
    weatherData.value = data;
    initMap(lat, lon);
  } catch (err) {
    console.error("Failed to fetch weather data", err);
    locationNotice.value =
      "無法載入氣象資料。此網站依賴外部 API（chang180backend.com），請確認 API 可連線後重新整理。";
  }
};

const getLocationErrorMessage = (error) => {
  if (!error) {
    return "無法取得您的位置，已改以台灣中心顯示。";
  }

  switch (error.code) {
    case error.PERMISSION_DENIED:
      return "定位權限被拒絕，已改以台灣中心顯示。請在瀏覽器設定中允許此網站使用位置資訊。";
    case error.POSITION_UNAVAILABLE:
      return "目前無法取得位置，已改以台灣中心顯示。";
    case error.TIMEOUT:
      return "定位逾時，已改以台灣中心顯示。";
    default:
      return "無法取得您的位置，已改以台灣中心顯示。";
  }
};

const useFallbackLocation = (error) => {
  locationNotice.value = getLocationErrorMessage(error);
  fetchWeatherData(DEFAULT_LAT, DEFAULT_LON);
};

const requestLocation = () => {
  locationNotice.value = "";

  if (!window.isSecureContext) {
    locationNotice.value =
      "此頁面未使用 HTTPS，瀏覽器無法詢問定位權限。請改用 HTTPS 網址開啟。";
    fetchWeatherData(DEFAULT_LAT, DEFAULT_LON);
    return;
  }

  if (!navigator.geolocation) {
    useFallbackLocation();
    return;
  }

  navigator.geolocation.getCurrentPosition(
    (position) => {
      locationNotice.value = "";
      fetchWeatherData(position.coords.latitude, position.coords.longitude);
    },
    (error) => {
      console.error("Geolocation failed", error);
      useFallbackLocation(error);
    },
    geoOptions
  );
};

onMounted(requestLocation);
</script>

<style scoped>
#map-container {
  width: 80vw;
  height: 75vh;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  overflow: hidden;
  border: 1px solid #ccc; /* 可選：添加邊框 */
}

#map {
  width: 100%;
  height: 100%;
}

.location-notice {
  position: absolute;
  top: 12px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1000;
  max-width: 90%;
  padding: 8px 12px;
  background: rgba(255, 243, 205, 0.95);
  border: 1px solid #f0c36d;
  border-radius: 6px;
  color: #664d03;
  font-size: 14px;
}

.locate-link,
.locate-btn {
  cursor: pointer;
}

.locate-link {
  margin-left: 8px;
  padding: 0;
  border: none;
  background: none;
  color: #0d6efd;
  text-decoration: underline;
}

.locate-btn {
  position: absolute;
  top: 12px;
  right: 12px;
  z-index: 1000;
  width: 36px;
  height: 36px;
  border: 1px solid #ccc;
  border-radius: 6px;
  background: #fff;
  font-size: 18px;
  line-height: 1;
}
</style>
