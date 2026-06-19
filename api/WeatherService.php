<?php

class WeatherService
{
    private $client;
    private $geoService;
    private $adminAreaService;

    public function __construct(
        CwaClient $client,
        GeoService $geoService,
        AdministrativeAreaService $adminAreaService
    ) {
        $this->client = $client;
        $this->geoService = $geoService;
        $this->adminAreaService = $adminAreaService;
    }

    public function getWeather($lat, $lon)
    {
        $observation = $this->client->getObservation();
        $stations = isset($observation['records']['Station']) ? $observation['records']['Station'] : array();
        $nearest = $this->geoService->findNearestStation($stations, $lat, $lon);
        $station = $nearest['station'];
        $stationGeoInfo = isset($station['GeoInfo']) ? $station['GeoInfo'] : array();
        $stationFallback = array(
            'county' => isset($stationGeoInfo['CountyName']) ? $stationGeoInfo['CountyName'] : '',
            'town' => isset($stationGeoInfo['TownName']) ? $stationGeoInfo['TownName'] : '',
        );
        $adminArea = $this->adminAreaService->resolve($lat, $lon, $stationFallback);
        $county = $adminArea['county'];
        $town = $adminArea['town'];

        $forecast = $this->client->getForecastByCounty($county);
        $forecastLocation = $this->geoService->findForecastLocation($forecast, $county, $town);
        if (!$forecastLocation) {
            $forecast = $this->client->getTaiwanForecast();
            $forecastLocation = $this->geoService->findForecastLocation($forecast, $county, $town);
        }
        if (!$forecastLocation) {
            throw new RuntimeException('No matching forecast location for ' . $county . $town);
        }

        $current = $this->buildCurrent($station);
        $forecastPayload = $this->buildForecast($forecastLocation);
        $advice = $this->buildAdvice($current, $forecastPayload);

        return array(
            'meta' => array(
                'fetchedAt' => date('c'),
                'locationMethod' => $adminArea['method'],
                'datasets' => array(
                    'observation' => CwaClient::OBSERVATION_DATASET,
                    'forecast' => isset($forecast['result']['resource_id']) ? $forecast['result']['resource_id'] : null,
                ),
                'stationDistanceKm' => round($nearest['distanceKm'], 2),
                'observationStationName' => isset($station['StationName']) ? $station['StationName'] : null,
                'observationStationId' => isset($station['StationId']) ? $station['StationId'] : null,
            ),
            'location' => array(
                'lat' => $lat,
                'lon' => $lon,
                'county' => $county,
                'town' => $town,
            ),
            'current' => $current,
            'stations' => $this->buildStations($stations),
            'forecast' => $forecastPayload,
            'advice' => $advice,
        );
    }

    private function buildCurrent($station)
    {
        $element = isset($station['WeatherElement']) ? $station['WeatherElement'] : array();

        return array(
            'stationName' => isset($station['StationName']) ? $station['StationName'] : null,
            'stationId' => isset($station['StationId']) ? $station['StationId'] : null,
            'observedAt' => isset($station['ObsTime']['DateTime']) ? $station['ObsTime']['DateTime'] : null,
            'temperature' => $this->toNumber(isset($element['AirTemperature']) ? $element['AirTemperature'] : null),
            'humidity' => $this->toNumber(isset($element['RelativeHumidity']) ? $element['RelativeHumidity'] : null),
            'pressure' => $this->toNumber(isset($element['AirPressure']) ? $element['AirPressure'] : null),
            'windSpeed' => $this->toNumber(isset($element['WindSpeed']) ? $element['WindSpeed'] : null),
            'windDirection' => $this->toNumber(isset($element['WindDirection']) ? $element['WindDirection'] : null),
            'weatherText' => isset($element['Weather']) ? $element['Weather'] : null,
            'rainfall10min' => $this->toNumber(isset($element['Now']['Precipitation']) ? $element['Now']['Precipitation'] : null),
            'uvIndex' => $this->toNumber(isset($element['UVIndex']) ? $element['UVIndex'] : null),
        );
    }

    private function buildStations($stations)
    {
        $payload = array();

        foreach ($stations as $station) {
            $coordinate = $this->getWgs84Coordinate($station);
            if (!$coordinate) {
                continue;
            }

            $element = isset($station['WeatherElement']) ? $station['WeatherElement'] : array();
            $geoInfo = isset($station['GeoInfo']) ? $station['GeoInfo'] : array();

            $payload[] = array(
                'stationName' => isset($station['StationName']) ? $station['StationName'] : null,
                'stationId' => isset($station['StationId']) ? $station['StationId'] : null,
                'lat' => $coordinate['lat'],
                'lon' => $coordinate['lon'],
                'county' => isset($geoInfo['CountyName']) ? $geoInfo['CountyName'] : null,
                'town' => isset($geoInfo['TownName']) ? $geoInfo['TownName'] : null,
                'observedAt' => isset($station['ObsTime']['DateTime']) ? $station['ObsTime']['DateTime'] : null,
                'temperature' => $this->toNumber(isset($element['AirTemperature']) ? $element['AirTemperature'] : null),
                'humidity' => $this->toNumber(isset($element['RelativeHumidity']) ? $element['RelativeHumidity'] : null),
                'windSpeed' => $this->toNumber(isset($element['WindSpeed']) ? $element['WindSpeed'] : null),
                'weatherText' => isset($element['Weather']) ? $element['Weather'] : null,
            );
        }

        return $payload;
    }

    private function buildForecast($location)
    {
        $elements = $this->indexElements(isset($location['WeatherElement']) ? $location['WeatherElement'] : array());
        $periods = array();
        $times = isset($elements['天氣現象']['Time']) ? $elements['天氣現象']['Time'] : array();

        foreach ($times as $index => $time) {
            $periods[] = array(
                'startTime' => isset($time['StartTime']) ? $time['StartTime'] : null,
                'endTime' => isset($time['EndTime']) ? $time['EndTime'] : null,
                'wx' => $this->elementValue($time, 'Weather'),
                'temperature' => $this->toNumber($this->timeElementValue($elements, '溫度', $index, 'Temperature')),
                'pop' => $this->toNumber($this->timeElementValue($elements, '3小時降雨機率', $index, 'ProbabilityOfPrecipitation')),
            );
        }

        $days = $this->groupForecastDays($periods);

        return array(
            'locationName' => isset($location['LocationName']) ? $location['LocationName'] : null,
            'days' => $days,
        );
    }

    private function buildAdvice($current, $forecast)
    {
        $periods = array();
        foreach ($forecast['days'] as $day) {
            foreach ($day['periods'] as $period) {
                $periods[] = $period;
            }
        }

        $firstPop = isset($periods[0]['pop']) ? $periods[0]['pop'] : null;
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $todayPop = $this->maxPopForDate($forecast['days'], $today);
        $tomorrowPop = $this->maxPopForDate($forecast['days'], $tomorrow);
        $weatherText = isset($current['weatherText']) ? $current['weatherText'] : '';
        $rainfall = isset($current['rainfall10min']) ? (float) $current['rainfall10min'] : 0;
        // CWA 10 分鐘雨量最小刻度為 0.5 mm，晴天測站亦常出現此讀數，僅 >= 1.0 視為有效降雨。
        $activeRainfall = $rainfall >= 1.0;

        $urgent = $activeRainfall || preg_match('/雨|雷/u', $weatherText) || ($firstPop !== null && $firstPop >= 50);
        $suggest = $urgent || ($todayPop !== null && $todayPop >= 40) || ($tomorrowPop !== null && $tomorrowPop >= 50);

        if ($urgent) {
            return array(
                'level' => 'urgent',
                'reason' => '目前或未來短時間有降雨風險，建議立即帶傘。',
                'icon' => 'umbrella',
            );
        }

        if ($suggest) {
            return array(
                'level' => 'suggest',
                'reason' => '今日或明日降雨機率偏高，出門建議帶傘。',
                'icon' => 'umbrella',
            );
        }

        return array(
            'level' => 'none',
            'reason' => '目前降雨風險較低，可視行程決定是否帶傘。',
            'icon' => 'sun',
        );
    }

    private function groupForecastDays($periods)
    {
        $groups = array();

        foreach ($periods as $period) {
            if (empty($period['startTime'])) {
                continue;
            }

            $date = substr($period['startTime'], 0, 10);
            if (!isset($groups[$date])) {
                $groups[$date] = array(
                    'date' => $date,
                    'wx' => $period['wx'],
                    'maxTemp' => null,
                    'minTemp' => null,
                    'pop' => null,
                    'periods' => array(),
                );
            }

            $temp = $period['temperature'];
            $pop = $period['pop'];
            if ($temp !== null) {
                $groups[$date]['maxTemp'] = $groups[$date]['maxTemp'] === null ? $temp : max($groups[$date]['maxTemp'], $temp);
                $groups[$date]['minTemp'] = $groups[$date]['minTemp'] === null ? $temp : min($groups[$date]['minTemp'], $temp);
            }
            if ($pop !== null) {
                $groups[$date]['pop'] = $groups[$date]['pop'] === null ? $pop : max($groups[$date]['pop'], $pop);
            }

            $groups[$date]['periods'][] = $period;
        }

        return array_slice(array_values($groups), 0, 3);
    }

    private function maxPopForDate($days, $date)
    {
        foreach ($days as $day) {
            if ($day['date'] === $date) {
                return $day['pop'];
            }
        }

        return null;
    }

    private function indexElements($weatherElements)
    {
        $indexed = array();
        foreach ($weatherElements as $element) {
            if (isset($element['ElementName'])) {
                $indexed[$element['ElementName']] = $element;
            }
        }

        return $indexed;
    }

    private function timeElementValue($elements, $elementName, $index, $key)
    {
        if (!isset($elements[$elementName]['Time'][$index])) {
            return null;
        }

        return $this->elementValue($elements[$elementName]['Time'][$index], $key);
    }

    private function elementValue($time, $key)
    {
        if (!isset($time['ElementValue'][0][$key])) {
            return null;
        }

        return $time['ElementValue'][0][$key];
    }

    private function getWgs84Coordinate($station)
    {
        $coordinates = isset($station['GeoInfo']['Coordinates']) ? $station['GeoInfo']['Coordinates'] : array();
        $fallback = null;

        foreach ($coordinates as $coordinate) {
            $lat = $this->toNumber(isset($coordinate['StationLatitude']) ? $coordinate['StationLatitude'] : null);
            $lon = $this->toNumber(isset($coordinate['StationLongitude']) ? $coordinate['StationLongitude'] : null);
            if ($lat === null || $lon === null) {
                continue;
            }

            $item = array('lat' => $lat, 'lon' => $lon);
            if (isset($coordinate['CoordinateName']) && strtoupper($coordinate['CoordinateName']) === 'WGS84') {
                return $item;
            }
            $fallback = $item;
        }

        return $fallback;
    }

    private function toNumber($value)
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        $numeric = (float) $value;

        // CWA 以負值（通常為 -99）表示該測站未提供此要素。
        if ($numeric < 0) {
            return null;
        }

        return $numeric;
    }
}
