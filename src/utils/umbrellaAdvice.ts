import type { AdviceLevel } from '../types/weather';

export const getAdviceLabel = (level: AdviceLevel): string => {
  const labels: Record<AdviceLevel, string> = {
    urgent: '務必帶傘',
    suggest: '建議帶傘',
    none: '暫不需帶傘',
  };

  return labels[level];
};

export const getAdviceIcon = (level: AdviceLevel): string => {
  const icons: Record<AdviceLevel, string> = {
    urgent: '☔',
    suggest: '🌂',
    none: '☀️',
  };

  return icons[level];
};

export const getAdviceClass = (level: AdviceLevel): string => {
  const classes: Record<AdviceLevel, string> = {
    urgent: 'advice-urgent',
    suggest: 'advice-suggest',
    none: 'advice-none',
  };

  return classes[level];
};
