<template>
  <div id="map-container">
    <div id="map"></div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { getWeatherData } from "./api";
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.fullscreen';
import 'leaflet.fullscreen/Control.FullScreen.css';

const weatherData = ref([]);
const map = ref(null);

const initMap = (lat, lon) => {
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

  weatherData.value.forEach((station) => {
    const lat = station.GeoInfo.Coordinates[1].StationLatitude;
    const lon = station.GeoInfo.Coordinates[1].StationLongitude;
    const marker = L.marker([lat, lon]).addTo(map.value);
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
  }
};

const getCurrentLocation = () => {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;
        fetchWeatherData(lat, lon);
      },
      () => {
        console.error("Geolocation permission denied or unavailable");
        // 使用默認位置
        fetchWeatherData(23.6978, 120.9605); // 台灣的地理中心
      }
    );
  } else {
    console.error("Geolocation is not supported by this browser");
    // 使用默認位置
    fetchWeatherData(23.6978, 120.9605); // 台灣的地理中心
  }
};

onMounted(getCurrentLocation);
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
</style>
