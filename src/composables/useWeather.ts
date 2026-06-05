import { computed, ref } from 'vue';
import { getWeather, WeatherApiError } from '../api';
import type { WeatherResponse } from '../types/weather';

const DEFAULT_LAT = 23.6978;
const DEFAULT_LON = 120.9605;

const geoOptions: PositionOptions = {
  enableHighAccuracy: true,
  timeout: 10000,
  maximumAge: 0,
};

const getLocationErrorMessage = (error?: GeolocationPositionError): string => {
  if (!error) {
    return '無法取得您的位置，已改以台灣中心顯示。';
  }

  switch (error.code) {
    case error.PERMISSION_DENIED:
      return '定位權限被拒絕，已改以台灣中心顯示。請在瀏覽器設定中允許此網站使用位置資訊。';
    case error.POSITION_UNAVAILABLE:
      return '目前無法取得位置，已改以台灣中心顯示。';
    case error.TIMEOUT:
      return '定位逾時，已改以台灣中心顯示。';
    default:
      return '無法取得您的位置，已改以台灣中心顯示。';
  }
};

const getApiErrorMessage = (error: unknown): string => {
  if (error instanceof WeatherApiError) {
    const messages: Record<string, string> = {
      MISSING_LOCATION: '缺少位置資訊，請重新定位後再試。',
      INVALID_QUERY: '位置格式不正確，請重新定位後再試。',
      LOCATION_OUT_OF_RANGE: '目前僅支援台灣周邊位置。',
      WEATHER_SERVICE_ERROR: '氣象資料暫時無法載入，請稍後再試。',
      MISSING_API_KEY: '氣象服務尚未完成設定，請稍後再試。',
      NETWORK_ERROR: '無法連線到氣象服務，請檢查網路後再試。',
    };

    return messages[error.code] ?? error.message;
  }

  return '氣象資料暫時無法載入，請稍後再試。';
};

export function useWeather() {
  const weather = ref<WeatherResponse | null>(null);
  const loading = ref(false);
  const error = ref('');
  const locationNotice = ref('');
  const requestedLocation = ref({ lat: DEFAULT_LAT, lon: DEFAULT_LON });

  const mapCenter = computed(() => ({
    lat: requestedLocation.value.lat,
    lon: requestedLocation.value.lon,
  }));

  const loadWeather = async (lat: number, lon: number): Promise<void> => {
    loading.value = true;
    error.value = '';
    requestedLocation.value = { lat, lon };

    try {
      weather.value = await getWeather(lat, lon);
    } catch (caughtError) {
      error.value = getApiErrorMessage(caughtError);
      weather.value = null;
    } finally {
      loading.value = false;
    }
  };

  const loadFallbackLocation = (notice: string): void => {
    locationNotice.value = notice;
    void loadWeather(DEFAULT_LAT, DEFAULT_LON);
  };

  const requestLocation = (): void => {
    locationNotice.value = '';
    error.value = '';

    if (!window.isSecureContext) {
      loadFallbackLocation('此頁面未使用 HTTPS，瀏覽器無法詢問定位權限。請改用 HTTPS 網址開啟。');
      return;
    }

    if (!navigator.geolocation) {
      loadFallbackLocation(getLocationErrorMessage());
      return;
    }

    navigator.geolocation.getCurrentPosition(
      (position) => {
        locationNotice.value = '';
        void loadWeather(position.coords.latitude, position.coords.longitude);
      },
      (geoError) => {
        loadFallbackLocation(getLocationErrorMessage(geoError));
      },
      geoOptions,
    );
  };

  return {
    weather,
    loading,
    error,
    locationNotice,
    mapCenter,
    requestLocation,
    loadWeather,
  };
}
