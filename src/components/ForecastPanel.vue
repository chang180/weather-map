<template>
  <section class="forecast-panel">
    <div class="section-head">
      <p class="eyebrow">三天預報</p>
      <h2>{{ forecast.locationName ?? '所在區域' }}</h2>
    </div>

    <div class="forecast-list">
      <article v-for="day in forecast.days.slice(0, 3)" :key="day.date" class="forecast-day">
        <div>
          <h3>{{ formatDate(day.date) }}</h3>
          <p>{{ day.wx ?? '無資料' }}</p>
        </div>
        <div class="forecast-values">
          <span>{{ formatRange(day.minTemp, day.maxTemp) }}</span>
          <span>降雨 {{ formatPop(day.pop) }}</span>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup lang="ts">
import type { WeatherForecast } from '../types/weather';

defineProps<{
  forecast: WeatherForecast
}>();

const formatDate = (value: string): string => {
  return new Intl.DateTimeFormat('zh-TW', {
    weekday: 'short',
    month: '2-digit',
    day: '2-digit',
  }).format(new Date(`${value}T00:00:00+08:00`));
};

const formatRange = (min: number | null, max: number | null): string => {
  if (min === null && max === null) {
    return '溫度無資料';
  }

  return `${min ?? '無資料'} / ${max ?? '無資料'}°C`;
};

const formatPop = (value: number | null): string => {
  if (value === null) {
    return '無資料';
  }

  return `${value}%`;
};
</script>

<style scoped>
.forecast-panel {
  display: grid;
  gap: var(--space-4);
  padding: var(--space-6);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  background: var(--color-surface);
  box-shadow: var(--shadow-card);
}

.section-head,
.eyebrow,
h2,
h3,
p {
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

.forecast-list {
  display: grid;
  gap: var(--space-3);
}

.forecast-day {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-3);
  padding: var(--space-4);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  background: var(--color-surface-muted);
}

.forecast-day p,
.forecast-values {
  color: var(--color-text-muted);
}

.forecast-values {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-3);
  font-size: var(--font-size-sm);
  font-weight: 700;
}

@media (min-width: 760px) {
  .forecast-day {
    grid-template-columns: 1fr auto;
    align-items: center;
  }
}
</style>
