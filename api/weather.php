<?php

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/CwaClient.php';
require_once __DIR__ . '/GeoService.php';
require_once __DIR__ . '/WeatherService.php';

$lat = getQueryFloat('lat');
$lon = getQueryFloat('lon');

if ($lat === null || $lon === null) {
    sendError('MISSING_LOCATION', 'lat and lon query parameters are required', 400);
}

if ($lat < 20 || $lat > 27 || $lon < 118 || $lon > 123) {
    sendError('LOCATION_OUT_OF_RANGE', 'lat/lon must be within Taiwan area', 400);
}

try {
    $client = new CwaClient(requireApiKey());
    $service = new WeatherService($client, new GeoService());

    sendJson($service->getWeather($lat, $lon));
} catch (RuntimeException $error) {
    sendError('WEATHER_SERVICE_ERROR', 'Unable to load weather data', 502, $error->getMessage());
}
