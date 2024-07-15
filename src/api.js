import axios from 'axios';

const API_URL = 'https://api.chang180backend.com/weather.php';

export const getWeatherData = async () => {
  try {
    const response = await axios.get(API_URL);
    // return response.data;
    return response.data.records.Station;
  } catch (error) {
    console.error('Error fetching weather data:', error);
    throw error;
  }
};
