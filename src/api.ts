import axios from 'axios';
import type { CwaWeatherResponse, WeatherStation } from './types/weather';

const API_URL = 'https://chang180backend.com/api/weather.php';

export const getWeatherData = async (): Promise<WeatherStation[]> => {
  try {
    const response = await axios.get<CwaWeatherResponse>(API_URL);
    // return response.data;
    return response.data.records.Station;
  } catch (error) {
    console.error('Error fetching weather data:', error);
    throw error;
  }
};
