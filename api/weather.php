<?php
header('Access-Control-Allow-Origin: *'); // 設置CORS頭
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

require_once __DIR__ . '/load-env.php';

loadEnvFile(dirname(__DIR__) . '/.env');

$apiKey = getenv('CWA_API_KEY');
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(array(
        'error' => array(
            'code' => 'MISSING_API_KEY',
            'message' => 'CWA_API_KEY not configured',
        ),
    ));
    exit;
}

$apiUrl = 'https://opendata.cwa.gov.tw/api/v1/rest/datastore/O-A0003-001'; // 使用O-A0003-001資料集

$params = array(
    'Authorization' => $apiKey,
);

// Create the URL with parameters
$url = $apiUrl . '?' . http_build_query($params);

// Initialize cURL
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// Execute the request and fetch the response
$response = curl_exec($ch);

// Check for errors
if(curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
} else {
    echo $response;
}

// Close cURL
curl_close($ch);
?>
