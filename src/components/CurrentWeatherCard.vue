<template>
  <section class="current-card">
    <div>
      <p class="eyebrow">即時觀測</p>
      <h2>{{ current.stationName ?? '最近測站' }}</h2>
      <p class="location">{{ location.county }}{{ location.town }}</p>
    </div>

    <div class="temperature">{{ formatValue(current.temperature, '°C') }}</div>

    <div class="summary">
      <span>{{ current.weatherText ?? '無資料' }}</span>
      <span>觀測 {{ formatDateTime(current.observedAt) }}</span>
      <span>距離 {{ formatValue(meta.stationDistanceKm, ' km') }}</span>
    </div>
  </section>
</template>

<script setup lang="ts">
import type { WeatherCurrent, WeatherLocation, WeatherMeta } from '../types/weather';

defineProps<{
  current: WeatherCurrent
  location: WeatherLocation
  meta: WeatherMeta
}>();

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

.eyebrow,
.location,
h2 {
  margin: 0;
}

.eyebrow {
  color: var(--color-text-muted);
  font-size: var(--font-size-sm);
  font-weight: 700;
}

h2 {
  margin-top: var(--space-1);
  font-size: var(--font-size-lg);
}

.location,
.summary {
  color: var(--color-text-muted);
}

.temperature {
  font-size: var(--font-size-xxl);
  font-weight: 800;
  line-height: 1;
}

.summary {
  display: grid;
  gap: var(--space-2);
  font-size: var(--font-size-sm);
}
</style>
