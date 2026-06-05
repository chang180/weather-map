<template>
  <main class="dashboard-shell">
    <header class="dashboard-header">
      <div>
        <p class="eyebrow">個人化氣象</p>
        <h1>Weather Map</h1>
        <p class="header-meta">{{ headerMeta }}</p>
      </div>
      <button type="button" class="locate-button" @click="requestLocation">
        重新定位
      </button>
    </header>

    <section v-if="locationNotice" class="notice">
      {{ locationNotice }}
    </section>

    <section v-if="error" class="notice error">
      {{ error }}
    </section>

    <section v-if="loading && !weather" class="loading-panel">
      氣象資料載入中...
    </section>

    <template v-if="weather">
      <UmbrellaBanner :advice="weather.advice" />

      <section class="dashboard-grid">
        <CurrentWeatherCard
          :current="weather.current"
          :location="weather.location"
          :meta="weather.meta"
        />
        <MetricGrid :current="weather.current" />
      </section>

      <ForecastPanel :forecast="weather.forecast" />

      <section class="map-section">
        <div class="section-head">
          <p class="eyebrow">位置參考</p>
          <h2>地圖輔助</h2>
        </div>
        <WeatherMap :weather="weather" :map-center="mapCenter" />
      </section>
    </template>
  </main>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import WeatherMap from './WeatherMap.vue';
import UmbrellaBanner from './components/UmbrellaBanner.vue';
import CurrentWeatherCard from './components/CurrentWeatherCard.vue';
import ForecastPanel from './components/ForecastPanel.vue';
import MetricGrid from './components/MetricGrid.vue';
import { useWeather } from './composables/useWeather';

const { weather, loading, error, locationNotice, mapCenter, requestLocation } = useWeather();

const headerMeta = computed(() => {
  if (!weather.value) {
    return '定位後顯示最近測站、三天預報與出門建議';
  }

  const fetchedAt = new Intl.DateTimeFormat('zh-TW', {
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(weather.value.meta.fetchedAt));

  return `${weather.value.location.county}${weather.value.location.town} · 更新 ${fetchedAt}`;
});

onMounted(requestLocation);
</script>

<style scoped>
.dashboard-shell {
  display: grid;
  gap: var(--space-6);
  width: min(1120px, 100%);
  margin: 0 auto;
  padding: var(--space-6);
}

.dashboard-header {
  display: grid;
  gap: var(--space-4);
  align-items: center;
  padding-top: var(--space-4);
}

.eyebrow,
.header-meta,
h1,
h2 {
  margin: 0;
}

.eyebrow {
  color: var(--color-text-muted);
  font-size: var(--font-size-sm);
  font-weight: 700;
}

h1 {
  margin-top: var(--space-1);
  font-size: var(--font-size-xl);
  line-height: 1.1;
}

.header-meta {
  margin-top: var(--space-2);
  color: var(--color-text-muted);
}

.locate-button {
  min-height: var(--size-button-height);
  padding: 0 var(--space-5);
  border: 1px solid var(--color-primary);
  border-radius: var(--radius-md);
  background: var(--color-primary);
  color: #ffffff;
  cursor: pointer;
  font-weight: 700;
}

.locate-button:hover {
  background: var(--color-primary-strong);
}

.notice,
.loading-panel,
.map-section {
  padding: var(--space-5);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  background: var(--color-surface);
  box-shadow: var(--shadow-card);
}

.notice {
  background: var(--color-suggest-bg);
  color: var(--color-suggest-text);
}

.notice.error {
  background: var(--color-urgent-bg);
  color: var(--color-urgent-text);
}

.loading-panel {
  color: var(--color-text-muted);
}

.dashboard-grid {
  display: grid;
  gap: var(--space-6);
}

.map-section {
  display: grid;
  gap: var(--space-4);
}

.section-head {
  display: grid;
  gap: var(--space-1);
}

@media (min-width: 900px) {
  .dashboard-shell {
    padding: var(--space-8);
  }

  .dashboard-header {
    grid-template-columns: 1fr auto;
  }

  .dashboard-grid {
    grid-template-columns: minmax(280px, 0.9fr) minmax(420px, 1.4fr);
  }
}
</style>
