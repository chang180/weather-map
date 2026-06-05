<template>
  <div id="map-container">
    <div v-if="locationNotice" class="location-notice">
      {{ locationNotice }}
      <button type="button" class="locate-link" @click="requestLocation">重新定位</button>
    </div>
    <div v-if="error" class="location-notice error-notice">
      {{ error }}
      <button type="button" class="locate-link" @click="requestLocation">重試</button>
    </div>
    <div v-if="loading" class="weather-status">氣象資料載入中...</div>
    <div v-else-if="weather" class="weather-status" :class="getAdviceClass(weather.advice.level)">
      {{ getAdviceIcon(weather.advice.level) }} {{ getAdviceLabel(weather.advice.level) }}：{{ weather.advice.reason }}
    </div>
    <button type="button" class="locate-btn" @click="requestLocation" title="定位到我的位置">
      📍
    </button>
    <div id="map"></div>
  </div>
</template>

<script setup lang="ts">
import { shallowRef, onMounted, watch } from "vue";
import L from 'leaflet';
import type { Map, LayerGroup } from 'leaflet';
import type { WeatherResponse } from './types/weather';
import { useWeather } from './composables/useWeather';
import { getAdviceClass, getAdviceIcon, getAdviceLabel } from './utils/umbrellaAdvice';
import 'leaflet/dist/leaflet.css';
import 'leaflet.fullscreen';
import 'leaflet.fullscreen/dist/Control.FullScreen.css';

const baseUrl = import.meta.env.BASE_URL;

delete (L.Icon.Default.prototype as L.Icon.Default & { _getIconUrl?: unknown })._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: `${baseUrl}images/marker-icon-2x.png`,
  iconUrl: `${baseUrl}images/marker-icon.png`,
  shadowUrl: `${baseUrl}images/marker-shadow.png`,
});

const { weather, loading, error, locationNotice, mapCenter, requestLocation } = useWeather();
const map = shallowRef<Map | null>(null);
const markersLayer = shallowRef<LayerGroup | null>(null);

const initMap = (lat: number, lon: number): void => {
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
    attribution: "", // 移除底部的標題
  }).addTo(map.value);

  markersLayer.value = L.layerGroup().addTo(map.value);

  // 確保地圖在容器大小改變後刷新
  window.addEventListener("resize", () => {
    map.value?.invalidateSize();
  });

  // 事件監聽
  map.value.on("enterFullscreen", function () {
    console.log("entered fullscreen");
  });

  map.value.on("exitFullscreen", function () {
    console.log("exited fullscreen");
  });
};

const formatValue = (value: number | string | null, unit = ''): string => {
  if (value === null || value === '') {
    return '無資料';
  }

  return `${value}${unit}`;
};

const renderWeatherMarker = (payload: WeatherResponse): void => {
  initMap(mapCenter.value.lat, mapCenter.value.lon);

  if (!markersLayer.value) {
    return;
  }

  markersLayer.value.clearLayers();
  const marker = L.marker([mapCenter.value.lat, mapCenter.value.lon]).addTo(markersLayer.value);
  marker.bindPopup(`
    <h3>${payload.current.stationName ?? '最近測站'}</h3>
    <p>${payload.location.county}${payload.location.town}</p>
    <p>氣溫：${formatValue(payload.current.temperature, ' °C')}</p>
    <p>濕度：${formatValue(payload.current.humidity, ' %')}</p>
    <p>風速：${formatValue(payload.current.windSpeed, ' m/s')}</p>
    <p>天氣：${payload.current.weatherText ?? '無資料'}</p>
  `);
};

watch(weather, (payload) => {
  if (payload) {
    renderWeatherMarker(payload);
  }
});

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

.error-notice {
  top: 56px;
  background: rgba(248, 215, 218, 0.95);
  border-color: #f1aeb5;
  color: #58151c;
}

.weather-status {
  position: absolute;
  bottom: 12px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1000;
  max-width: 90%;
  padding: 8px 12px;
  border: 1px solid #b6d4fe;
  border-radius: 6px;
  background: rgba(207, 226, 255, 0.95);
  color: #052c65;
  font-size: 14px;
}

.advice-urgent {
  border-color: #f1aeb5;
  background: rgba(248, 215, 218, 0.95);
  color: #58151c;
}

.advice-suggest {
  border-color: #ffe69c;
  background: rgba(255, 243, 205, 0.95);
  color: #664d03;
}

.advice-none {
  border-color: #a3cfbb;
  background: rgba(209, 231, 221, 0.95);
  color: #0a3622;
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
