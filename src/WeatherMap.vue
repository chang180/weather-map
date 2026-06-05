<template>
  <div class="map-frame">
    <div id="map"></div>
  </div>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, shallowRef, watch } from 'vue';
import L from 'leaflet';
import { FullScreen } from 'leaflet.fullscreen';
import type { LayerGroup, Map } from 'leaflet';
import type { WeatherResponse } from './types/weather';
import 'leaflet/dist/leaflet.css';
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
const userLayer = shallowRef<LayerGroup | null>(null);

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
    zoomControl: false,
  }).setView([props.mapCenter.lat, props.mapCenter.lon], 12);

  map.value.addControl(new FullScreen({
    position: 'topleft',
    forceSeparateButton: true,
    title: '全螢幕',
    titleCancel: '離開全螢幕',
  }));

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '',
  }).addTo(map.value);

  markersLayer.value = L.layerGroup().addTo(map.value);
  userLayer.value = L.layerGroup().addTo(map.value);
};

const renderMarker = (): void => {
  initMap();

  if (!map.value || !markersLayer.value) {
    return;
  }

  map.value.setView([props.mapCenter.lat, props.mapCenter.lon], 12);
  markersLayer.value.clearLayers();
  userLayer.value?.clearLayers();

  const userMarker = L.marker([props.mapCenter.lat, props.mapCenter.lon], {
    icon: L.divIcon({
      className: 'user-location-marker',
      html: '<span></span>',
      iconSize: [24, 24],
      iconAnchor: [12, 12],
    }),
  }).addTo(userLayer.value ?? markersLayer.value);
  userMarker.bindPopup('<h3>您的位置</h3>');

  props.weather.stations.forEach((station) => {
    const marker = L.marker([station.lat, station.lon]).addTo(markersLayer.value as LayerGroup);
    marker.bindPopup(`
      <h3>${station.stationName ?? '氣象測站'}</h3>
      <p>${station.county ?? ''}${station.town ?? ''}</p>
      <p>氣溫：${formatValue(station.temperature, ' °C')}</p>
      <p>濕度：${formatValue(station.humidity, ' %')}</p>
      <p>風速：${formatValue(station.windSpeed, ' m/s')}</p>
      <p>天氣：${station.weatherText ?? '無資料'}</p>
    `);
  });
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

:deep(.user-location-marker) {
  display: grid;
  place-items: center;
  border-radius: 999px;
  background: rgba(31, 111, 235, 0.18);
}

:deep(.user-location-marker span) {
  display: block;
  width: 14px;
  height: 14px;
  border: 3px solid #ffffff;
  border-radius: 999px;
  background: var(--color-primary);
  box-shadow: 0 0 0 2px var(--color-primary), 0 6px 14px rgba(23, 78, 166, 0.35);
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
