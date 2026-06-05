<template>
  <section class="current-card">
    <div class="card-head">
      <p class="eyebrow">即時觀測</p>
      <h2 class="location-title">{{ location.county }}{{ location.town }}</h2>
      <p class="station-source">
        觀測來源：{{ observationStationName }}
        <span v-if="meta.stationDistanceKm !== null"> · 約 {{ formatValue(meta.stationDistanceKm, ' km') }}</span>
      </p>
    </div>

    <div class="temperature">{{ formatValue(current.temperature, '°C') }}</div>

    <div class="summary">
      <span>{{ current.weatherText ?? '無資料' }}</span>
      <span>觀測時間 {{ formatDateTime(current.observedAt) }}</span>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { WeatherCurrent, WeatherLocation, WeatherMeta } from '../types/weather';

const props = defineProps<{
  current: WeatherCurrent
  location: WeatherLocation
  meta: WeatherMeta
}>();

const observationStationName = computed(
  () => props.meta.observationStationName ?? props.current.stationName ?? '最近測站',
);

const formatValue = (value: number | null, unit: string): string => {
  if (value === null) {
    return '無資料';
  }

  return `${value}${unit}`;
};

const formatDateTime = (value: string | null): string => {
  if (!value) {
    return '無資料';
  }

  return new Intl.DateTimeFormat('zh-TW', {
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(value));
};
</script>

<style scoped>
.current-card {
  display: grid;
  gap: var(--space-4);
  min-height: 100%;
  padding: var(--space-6);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  background: var(--color-surface);
  box-shadow: var(--shadow-card);
}

.card-head,
h2,
.summary {
  margin: 0;
}

.eyebrow {
  margin: 0;
  color: var(--color-text-muted);
  font-size: var(--font-size-sm);
  font-weight: 700;
}

.location-title {
  margin-top: var(--space-1);
  font-size: var(--font-size-lg);
}

.station-source {
  margin: var(--space-2) 0 0;
  color: var(--color-text-muted);
  font-size: var(--font-size-sm);
}

.temperature {
  font-size: var(--font-size-xxl);
  font-weight: 800;
  line-height: 1;
}

.summary {
  display: grid;
  gap: var(--space-2);
  color: var(--color-text-muted);
  font-size: var(--font-size-sm);
}
</style>
