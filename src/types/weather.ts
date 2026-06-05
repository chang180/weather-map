export type AdviceLevel = 'urgent' | 'suggest' | 'none'

export interface WeatherMeta {
  fetchedAt: string
  locationMethod: 'nearest_station' | string
  datasets: {
    observation: string | null
    forecast: string | null
  }
  stationDistanceKm: number | null
}

export interface WeatherLocation {
  lat: number
  lon: number
  county: string
  town: string
}

export interface WeatherCurrent {
  stationName: string | null
  stationId: string | null
  observedAt: string | null
  temperature: number | null
  humidity: number | null
  pressure: number | null
  windSpeed: number | null
  windDirection: number | null
  weatherText: string | null
  rainfall10min: number | null
  uvIndex: number | null
}

export interface ForecastPeriod {
  startTime: string | null
  endTime: string | null
  wx: string | null
  temperature: number | null
  pop: number | null
}

export interface ForecastDay {
  date: string
  wx: string | null
  maxTemp: number | null
  minTemp: number | null
  pop: number | null
  periods: ForecastPeriod[]
}

export interface WeatherForecast {
  locationName: string | null
  days: ForecastDay[]
}

export interface UmbrellaAdvice {
  level: AdviceLevel
  reason: string
  icon: string
}

export interface WeatherResponse {
  meta: WeatherMeta
  location: WeatherLocation
  current: WeatherCurrent
  forecast: WeatherForecast
  advice: UmbrellaAdvice
}

export interface ApiErrorResponse {
  error: {
    code: string
    message: string
    details?: unknown
  }
}
