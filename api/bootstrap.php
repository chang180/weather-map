<?php

require_once __DIR__ . '/load-env.php';

loadEnvFile(dirname(__DIR__) . '/.env');
loadEnvFile(dirname(__DIR__, 2) . '/.env');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

$requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

if ($requestMethod === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (PHP_SAPI === 'cli' && empty($_GET) && getenv('QUERY_STRING')) {
    parse_str(getenv('QUERY_STRING'), $_GET);
}

function sendJson($payload, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function sendError($code, $message, $statusCode = 500, $details = null)
{
    $payload = array(
        'error' => array(
            'code' => $code,
            'message' => $message,
        ),
    );

    if ($details !== null) {
        $payload['error']['details'] = $details;
    }

    sendJson($payload, $statusCode);
}

function requireApiKey()
{
    $apiKey = getenv('CWA_API_KEY');
    if (!$apiKey) {
        sendError('MISSING_API_KEY', 'CWA_API_KEY not configured', 500);
    }

    return $apiKey;
}

function getQueryFloat($key)
{
    if (!isset($_GET[$key]) || $_GET[$key] === '') {
        return null;
    }

    if (!is_numeric($_GET[$key])) {
        sendError('INVALID_QUERY', $key . ' must be a number', 400);
    }

    return (float) $_GET[$key];
}
