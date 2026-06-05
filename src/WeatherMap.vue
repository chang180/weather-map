<template>
  <div class="map-frame">
    <div id="map"></div>
  </div>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, shallowRef, watch } from 'vue';
import L from 'leaflet';
import type { LayerGroup, Map } from 'leaflet';
import type { WeatherResponse } from './types/weather';
import 'leaflet/dist/leaflet.css';
import 'leaflet.fullscreen';
import 'leaflet.fullscreen/dist/Control.FullScreen.css';

const props = defineProps<{
  weather: WeatherResponse
  mapCenter: {
    lat: number
    lon: number
  }
}>();

const baseUrl = import.meta.env.BASE_URL;

delete (L.Icon.Default.prototype as L.Icon.Default & { _getIconUrl?: unknown })._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: `${baseUrl}images/marker-icon-2x.png`,
  iconUrl: `${baseUrl}images/marker-icon.png`,
  shadowUrl: `${baseUrl}images/marker-shadow.png`,
});

const map = shallowRef<Map | null>(null);
const markersLayer = shallowRef<LayerGroup | null>(null);

const formatValue = (value: number | string | null, unit = ''): string => {
  if (value === null || value === '') {
    return '無資料';
  }

  return `${value}${unit}`;
};

const initMap = (): void => {
  if (map.value) {
    return;
  }

  map.value = L.map('map', {
    fullscreenControl: true,
    fullscreenControlOptions: {
      position: 'topleft',
    },
    zoomControl: false,
  }).setView([props.mapCenter.lat, props.mapCenter.lon], 12);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '',
  }).addTo(map.value);

  markersLayer.value = L.layerGroup().addTo(map.value);
};

const renderMarker = (): void => {
  initMap();

  if (!map.value || !markersLayer.value) {
    return;
  }

  map.value.setView([props.mapCenter.lat, props.mapCenter.lon], 12);
  markersLayer.value.clearLayers();

  const marker = L.marker([props.mapCenter.lat, props.mapCenter.lon]).addTo(markersLayer.value);
  marker.bindPopup(`
    <h3>${props.weather.current.stationName ?? '最近測站'}</h3>
    <p>${props.weather.location.county}${props.weather.location.town}</p>
    <p>氣溫：${formatValue(props.weather.current.temperature, ' °C')}</p>
    <p>濕度：${formatValue(props.weather.current.humidity, ' %')}</p>
    <p>風速：${formatValue(props.weather.current.windSpeed, ' m/s')}</p>
    <p>天氣：${props.weather.current.weatherText ?? '無資料'}</p>
  `);
};

const invalidateMap = (): void => {
  map.value?.invalidateSize();
};

onMounted(() => {
  renderMarker();
  window.addEventListener('resize', invalidateMap);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', invalidateMap);
  map.value?.remove();
});

watch(
  () => [props.weather, props.mapCenter.lat, props.mapCenter.lon],
  () => {
    renderMarker();
  },
);
</script>

<style scoped>
.map-frame {
  width: 100%;
  min-height: var(--size-map-height);
  overflow: hidden;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  background: var(--color-surface-muted);
}

#map {
  width: 100%;
  height: var(--size-map-height);
}

@media (min-width: 900px) {
  .map-frame {
    min-height: var(--size-map-height-lg);
  }

  #map {
    height: var(--size-map-height-lg);
  }
}
</style>
