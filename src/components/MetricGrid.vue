<template>
  <section class="metric-grid" aria-label="即時觀測指標">
    <article v-for="metric in metrics" :key="metric.label" class="metric">
      <span class="metric-label">{{ metric.label }}</span>
      <strong>{{ metric.value }}</strong>
    </article>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { WeatherCurrent } from '../types/weather';

const props = defineProps<{
  current: WeatherCurrent
}>();

const formatValue = (value: number | null, unit = ''): string => {
  if (value === null) {
    return '無資料';
  }

  return `${value}${unit}`;
};

const formatWindDirection = (value: number | null): string => {
  if (value === null) {
    return '無資料';
  }

  const directions = ['北', '東北', '東', '東南', '南', '西南', '西', '西北'];
  const index = Math.round(value / 45) % 8;

  return `${directions[index]}（${Math.round(value)}°）`;
};

const metrics = computed(() => [
  { label: '濕度', value: formatValue(props.current.humidity, '%') },
  { label: '氣壓', value: formatValue(props.current.pressure, ' hPa') },
  { label: '風速', value: formatValue(props.current.windSpeed, ' m/s') },
  { label: '風向', value: formatWindDirection(props.current.windDirection) },
  { label: '10 分鐘雨量', value: formatValue(props.current.rainfall10min, ' mm') },
  { label: 'UV', value: formatValue(props.current.uvIndex) },
]);
</script>

<style scoped>
.metric-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: var(--space-4);
}

.metric {
  display: grid;
  gap: var(--space-2);
  min-height: 92px;
  padding: var(--space-4);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  background: var(--color-surface);
  box-shadow: var(--shadow-card);
}

.metric-label {
  color: var(--color-text-muted);
  font-size: var(--font-size-sm);
}

strong {
  align-self: end;
  font-size: var(--font-size-lg);
}

@media (min-width: 900px) {
  .metric-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}
</style>
