export interface WeatherStationCoordinate {
  StationLatitude: number | string
  StationLongitude: number | string
}

export interface WeatherStationGeoInfo {
  Coordinates: WeatherStationCoordinate[]
}

export interface WeatherStationElement {
  AirTemperature: number | string
  RelativeHumidity: number | string
  WindSpeed: number | string
  Weather: string
}

export interface WeatherStation {
  StationName: string
  GeoInfo: WeatherStationGeoInfo
  WeatherElement: WeatherStationElement
}

export interface CwaWeatherResponse {
  records: {
    Station: WeatherStation[]
  }
}

export interface WeatherResponse {
  station?: WeatherStation
  forecast?: unknown
  umbrellaAdvice?: unknown
}
