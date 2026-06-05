<?php

class CwaClient
{
    const OBSERVATION_DATASET = 'O-A0003-001';
    const TAIWAN_FORECAST_DATASET = 'F-D0047-089';

    private $apiKey;
    private $cache;
    private $ttlSeconds;

    private $countyForecastDatasets = array(
        '宜蘭縣' => 'F-D0047-001',
        '桃園市' => 'F-D0047-005',
        '新竹縣' => 'F-D0047-009',
        '苗栗縣' => 'F-D0047-013',
        '彰化縣' => 'F-D0047-017',
        '南投縣' => 'F-D0047-021',
        '雲林縣' => 'F-D0047-025',
        '嘉義縣' => 'F-D0047-029',
        '屏東縣' => 'F-D0047-033',
        '臺東縣' => 'F-D0047-037',
        '花蓮縣' => 'F-D0047-041',
        '澎湖縣' => 'F-D0047-045',
        '基隆市' => 'F-D0047-049',
        '新竹市' => 'F-D0047-053',
        '嘉義市' => 'F-D0047-057',
        '臺北市' => 'F-D0047-061',
        '高雄市' => 'F-D0047-065',
        '新北市' => 'F-D0047-069',
        '臺中市' => 'F-D0047-073',
        '臺南市' => 'F-D0047-077',
        '連江縣' => 'F-D0047-081',
        '金門縣' => 'F-D0047-085',
    );

    public function __construct($apiKey, ?RedisCache $cache = null, $ttlSeconds = null)
    {
        $this->apiKey = $apiKey;
        $this->cache = $cache;
        $this->ttlSeconds = $ttlSeconds ?: RedisCache::TTL_SECONDS;
    }

    public function getObservation()
    {
        return $this->fetchDataset(self::OBSERVATION_DATASET);
    }

    public function getForecastByCounty($countyName)
    {
        $normalizedCounty = $this->normalizeName($countyName);
        $datasetId = isset($this->countyForecastDatasets[$normalizedCounty])
            ? $this->countyForecastDatasets[$normalizedCounty]
            : self::TAIWAN_FORECAST_DATASET;

        return $this->fetchDataset($datasetId);
    }

    public function getTaiwanForecast()
    {
        return $this->fetchDataset(self::TAIWAN_FORECAST_DATASET);
    }

    public function getCountyDatasetMap()
    {
        return $this->countyForecastDatasets;
    }

    private function fetchDataset($datasetId)
    {
        $cacheKey = 'weather-map:cwa:' . $datasetId;

        if ($this->cache && $this->cache->isAvailable()) {
            $cached = $this->cache->get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $url = 'https://opendata.cwa.gov.tw/api/v1/rest/datastore/' . rawurlencode($datasetId)
            . '?' . http_build_query(array('Authorization' => $this->apiKey));

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($body === false || $curlError) {
            throw new RuntimeException('CWA request failed: ' . $curlError);
        }

        $decoded = json_decode($body, true);
        if ($httpCode < 200 || $httpCode >= 300 || !is_array($decoded)) {
            throw new RuntimeException('CWA dataset ' . $datasetId . ' returned HTTP ' . $httpCode);
        }

        if ($this->cache && $this->cache->isAvailable()) {
            $this->cache->set($cacheKey, $decoded, $this->ttlSeconds);
        }

        return $decoded;
    }

    private function normalizeName($name)
    {
        return str_replace('台', '臺', (string) $name);
    }
}
