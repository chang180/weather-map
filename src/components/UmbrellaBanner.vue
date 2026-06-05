<template>
  <section class="umbrella-banner" :class="getAdviceClass(advice.level)">
    <div class="advice-icon" aria-hidden="true">{{ getAdviceIcon(advice.level) }}</div>
    <div>
      <p class="eyebrow">出門建議</p>
      <h2>{{ getAdviceLabel(advice.level) }}</h2>
      <p class="reason">{{ advice.reason }}</p>
    </div>
  </section>
</template>

<script setup lang="ts">
import type { UmbrellaAdvice } from '../types/weather';
import { getAdviceClass, getAdviceIcon, getAdviceLabel } from '../utils/umbrellaAdvice';

defineProps<{
  advice: UmbrellaAdvice
}>();
</script>

<style scoped>
.umbrella-banner {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: var(--space-4);
  align-items: center;
  padding: var(--space-6);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-card);
}

.advice-icon {
  display: grid;
  width: var(--size-advice-icon);
  height: var(--size-advice-icon);
  place-items: center;
  border-radius: var(--radius-md);
  background: rgba(255, 255, 255, 0.65);
  font-size: var(--font-size-xl);
}

.eyebrow,
.reason,
h2 {
  margin: 0;
}

.eyebrow {
  color: currentColor;
  font-size: var(--font-size-sm);
  font-weight: 700;
}

h2 {
  margin-top: var(--space-1);
  font-size: var(--font-size-xl);
}

.reason {
  margin-top: var(--space-2);
  color: currentColor;
  font-size: var(--font-size-md);
}

.advice-urgent {
  border-color: var(--color-urgent-border);
  background: var(--color-urgent-bg);
  color: var(--color-urgent-text);
}

.advice-suggest {
  border-color: var(--color-suggest-border);
  background: var(--color-suggest-bg);
  color: var(--color-suggest-text);
}

.advice-none {
  border-color: var(--color-none-border);
  background: var(--color-none-bg);
  color: var(--color-none-text);
}
</style>
