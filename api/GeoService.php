<?php

class GeoService
{
    private $aliases;

    public function __construct($aliasesPath = null)
    {
        $aliasesPath = $aliasesPath ?: __DIR__ . '/data/township-aliases.json';
        $this->aliases = array();

        if (is_readable($aliasesPath)) {
            $decoded = json_decode(file_get_contents($aliasesPath), true);
            if (is_array($decoded)) {
                $this->aliases = $decoded;
            }
        }
    }

    public function findNearestStation($stations, $lat, $lon)
    {
        $nearest = null;
        $nearestDistance = null;

        foreach ($stations as $station) {
            $coordinate = $this->getWgs84Coordinate($station);
            if (!$coordinate) {
                continue;
            }

            $distance = $this->haversine($lat, $lon, $coordinate['lat'], $coordinate['lon']);
            if ($nearestDistance === null || $distance < $nearestDistance) {
                $nearest = $station;
                $nearestDistance = $distance;
            }
        }

        if (!$nearest) {
            throw new RuntimeException('No usable observation station coordinates');
        }

        return array(
            'station' => $nearest,
            'distanceKm' => $nearestDistance,
        );
    }

    public function findForecastLocation($forecast, $countyName, $townName)
    {
        $locations = $this->getForecastLocations($forecast);
        $normalizedTown = $this->normalizeName($townName);
        $normalizedCounty = $this->normalizeName($countyName);
        $candidates = array($normalizedTown);

        $aliasKey = $normalizedCounty . '/' . $normalizedTown;
        if (isset($this->aliases[$aliasKey]) && is_array($this->aliases[$aliasKey])) {
            $candidates = array_merge($candidates, $this->aliases[$aliasKey]);
        }
        if (isset($this->aliases[$normalizedTown]) && is_array($this->aliases[$normalizedTown])) {
            $candidates = array_merge($candidates, $this->aliases[$normalizedTown]);
        }

        foreach ($candidates as $candidate) {
            $matched = $this->findLocationByName($locations, $candidate);
            if ($matched) {
                return $matched;
            }
        }

        return $this->findLocationByName($locations, $normalizedCounty);
    }

    private function getForecastLocations($forecast)
    {
        if (!isset($forecast['records']['Locations'][0]['Location'])) {
            return array();
        }

        return $forecast['records']['Locations'][0]['Location'];
    }

    private function findLocationByName($locations, $name)
    {
        $normalizedName = $this->normalizeName($name);
        foreach ($locations as $location) {
            if ($this->normalizeName(isset($location['LocationName']) ? $location['LocationName'] : '') === $normalizedName) {
                return $location;
            }
        }

        return null;
    }

    private function getWgs84Coordinate($station)
    {
        $coordinates = isset($station['GeoInfo']['Coordinates']) ? $station['GeoInfo']['Coordinates'] : array();
        $fallback = null;

        foreach ($coordinates as $coordinate) {
            $lat = $this->toFloat(isset($coordinate['StationLatitude']) ? $coordinate['StationLatitude'] : null);
            $lon = $this->toFloat(isset($coordinate['StationLongitude']) ? $coordinate['StationLongitude'] : null);
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

    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    private function toFloat($value)
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function normalizeName($name)
    {
        return str_replace('台', '臺', (string) $name);
    }
}
