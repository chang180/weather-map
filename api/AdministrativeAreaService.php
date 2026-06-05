<?php

class AdministrativeAreaService
{
    const NLSC_ENDPOINT = 'https://api.nlsc.gov.tw/other/TownVillagePointQuery1';

    private $cache;

    public function __construct(?RedisCache $cache = null)
    {
        $this->cache = $cache;
    }

    public function resolve($lat, $lon, ?array $stationFallback = null)
    {
        $cacheKey = $this->buildCacheKey($lat, $lon);
        if ($this->cache && $this->cache->isAvailable()) {
            $cached = $this->cache->get($cacheKey);
            if (is_array($cached) && isset($cached['county'], $cached['town'], $cached['method'])) {
                return $cached;
            }
        }

        $resolved = $this->fetchFromNlsc($lat, $lon);
        if ($resolved) {
            $this->storeCache($cacheKey, $resolved);
            return $resolved;
        }

        if ($stationFallback && isset($stationFallback['county'], $stationFallback['town'])) {
            return array(
                'county' => $this->normalizeName($stationFallback['county']),
                'town' => $this->normalizeName($stationFallback['town']),
                'method' => 'nearest_station_fallback',
            );
        }

        throw new RuntimeException('Unable to resolve administrative area for coordinates');
    }

    private function fetchFromNlsc($lat, $lon)
    {
        $url = self::NLSC_ENDPOINT . '/'
            . rawurlencode((string) $lon) . '/'
            . rawurlencode((string) $lat) . '/4326';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($body === false || $curlError || $httpCode < 200 || $httpCode >= 300) {
            return null;
        }

        $county = $this->extractXmlValue($body, 'ctyName');
        $town = $this->extractXmlValue($body, 'townName');
        if ($county === null || $town === null) {
            return null;
        }

        return array(
            'county' => $this->normalizeName($county),
            'town' => $this->normalizeName($town),
            'method' => 'nlsc_reverse_geocode',
        );
    }

    private function extractXmlValue($xml, $tagName)
    {
        $pattern = '/<' . preg_quote($tagName, '/') . '>(.*?)<\/' . preg_quote($tagName, '/') . '>/s';
        if (!preg_match($pattern, $xml, $matches)) {
            return null;
        }

        $value = trim(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        return $value === '' ? null : $value;
    }

    private function buildCacheKey($lat, $lon)
    {
        return 'weather-map:geo:'
            . number_format((float) $lat, 4, '.', '')
            . ':'
            . number_format((float) $lon, 4, '.', '');
    }

    private function storeCache($cacheKey, array $resolved)
    {
        if ($this->cache && $this->cache->isAvailable()) {
            $this->cache->set($cacheKey, $resolved);
        }
    }

    private function normalizeName($name)
    {
        return str_replace('台', '臺', (string) $name);
    }
}
