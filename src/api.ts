import axios from 'axios';
import type { ApiErrorResponse, WeatherResponse } from './types/weather';

const API_URL = import.meta.env.VITE_API_URL ?? '/api/weather.php';

export class WeatherApiError extends Error {
  code: string

  constructor(code: string, message: string) {
    super(message);
    this.name = 'WeatherApiError';
    this.code = code;
  }
}

const isApiErrorResponse = (value: unknown): value is ApiErrorResponse => {
  return (
    typeof value === 'object' &&
    value !== null &&
    'error' in value &&
    typeof (value as ApiErrorResponse).error?.code === 'string' &&
    typeof (value as ApiErrorResponse).error?.message === 'string'
  );
};

export const getWeather = async (lat: number, lon: number): Promise<WeatherResponse> => {
  try {
    const response = await axios.get<WeatherResponse>(API_URL, {
      params: { lat, lon },
    });

    return response.data;
  } catch (error) {
    if (axios.isAxiosError(error) && isApiErrorResponse(error.response?.data)) {
      throw new WeatherApiError(error.response.data.error.code, error.response.data.error.message);
    }

    throw new WeatherApiError('NETWORK_ERROR', '無法連線到氣象服務，請稍後再試。');
  }
};
