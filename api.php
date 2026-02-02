<?php
// 1. Mas malinis na CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Timeout settings para sa malalaking audio files
set_time_limit(120); 
ini_set('max_execution_time', 120);
ini_set('display_errors', 0); 

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 2. Pagkuha ng Inputs (with defaults)
$prompt  = isset($_GET['prompt']) ? trim($_GET['prompt']) : '';
$voice   = isset($_GET['voice'])  ? trim($_GET['voice'])  : 'alloy';
$emotion = isset($_GET['emotion'])? trim($_GET['emotion']): 'neutral';
$apiKey  = isset($_GET['key'])    ? trim($_GET['key'])    : '';
$seed    = isset($_GET['seed'])   ? intval($_GET['seed']) : rand(1, 999999);
$debug   = isset($_GET['debug'])  && $_GET['debug'] == '1';

if (empty($prompt)) {
    header("Content-Type: application/json");
    http_response_code(400);
    echo json_encode(["error" => "Missing prompt. Usage: api.php?prompt=Hello"]);
    exit;
}

// 3. API Configuration
$apiUrl = "https://gen.pollinations.ai/v1/chat/completions";
$systemInstruction = "Only repeat what I say. Now say with proper emphasis in a \"{$emotion}\" emotion this statement.";

$payload = [
    "model" => "openai-audio",
    "modalities" => ["text", "audio"],
    "audio" => ["voice" => $voice, "format" => "mp3"],
    "messages" => [
        ["role" => "system", "content" => $systemInstruction],
        ["role" => "user", "content" => $prompt]
    ],
    "seed" => $seed
];

// 4. cURL Execution (Ang "Axios" ng PHP)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 90);

// CRITICAL: SSL Fix para sa Render/Cloud environments
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// Spoof User Agent
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

$headers = ["Content-Type: application/json"];
if (!empty($apiKey)) {
    $headers[] = "Authorization: Bearer " . $apiKey;
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// 5. Error Handling
if ($curlError || $httpCode !== 200) {
    header("Content-Type: application/json");
    http_response_code(500);
    echo json_encode([
        "error" => "External API Error", 
        "details" => $curlError, 
        "http_code" => $httpCode
    ]);
    exit;
}

$data = json_decode($response, true);

if ($debug) {
    header("Content-Type: application/json");
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// 6. Audio Streaming Logic
if (isset($data['choices'][0]['message']['audio']['data'])) {
    $base64Audio = $data['choices'][0]['message']['audio']['data'];
    $binaryAudio = base64_decode($base64Audio);

    // Linisin ang buffer para walang corrupt data
    if (ob_get_length()) ob_clean();
    
    header('Content-Type: audio/mpeg');
    header('Content-Length: ' . strlen($binaryAudio));
    header('Cache-Control: no-cache');
    
    echo $binaryAudio;
    exit;
} else {
    header("Content-Type: application/json");
    http_response_code(502);
    echo json_encode(["error" => "Invalid API Response Structure"]);
    exit;
}
?>        ["role" => "system", "content" => $systemInstruction],
        ["role" => "user", "content" => $prompt]
    ],
    "seed" => $seed
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 90);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

$headers = ["Content-Type: application/json"];
if (!empty($apiKey)) { $headers[] = "Authorization: Bearer " . $apiKey; }
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError || $httpCode !== 200) {
    header("Content-Type: application/json");
    http_response_code(500);
    echo json_encode([
        "error" => "External API Error", 
        "details" => $curlError, 
        "http_code" => $httpCode, 
        "response_body" => $response
    ]);
    exit;
}

$data = json_decode($response, true);

if ($debug) {
    header("Content-Type: application/json");
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

if (isset($data['choices'][0]['message']['audio']['data'])) {
    $base64Audio = $data['choices'][0]['message']['audio']['data'];
    $binaryAudio = base64_decode($base64Audio);
    if (ob_get_length()) ob_clean();
    header('Content-Type: audio/mpeg');
    header('Content-Length: ' . strlen($binaryAudio));
    header('Content-Disposition: inline; filename="speech.mp3"');
    header('Cache-Control: no-cache');
    echo $binaryAudio;
    exit;
} else {
    header("Content-Type: application/json");
    http_response_code(502);
    echo json_encode(["error" => "Invalid API Response Structure", "raw_response" => $data]);
    exit;
}
?>        ["role" => "system", "content" => $systemInstruction],
        ["role" => "user", "content" => $prompt]
    ],
    "seed" => $seed
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 90);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

$headers = ["Content-Type: application/json"];
if (!empty($apiKey)) { $headers[] = "Authorization: Bearer " . $apiKey; }
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError || $httpCode !== 200) {
    header("Content-Type: application/json");
    http_response_code(500);
    echo json_encode([
        "error" => "External API Error", 
        "details" => $curlError, 
        "http_code" => $httpCode, 
        "response_body" => $response
    ]);
    exit;
}

$data = json_decode($response, true);

if ($debug) {
    header("Content-Type: application/json");
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

if (isset($data['choices'][0]['message']['audio']['data'])) {
    $base64Audio = $data['choices'][0]['message']['audio']['data'];
    $binaryAudio = base64_decode($base64Audio);
    if (ob_get_length()) ob_clean();
    header('Content-Type: audio/mpeg');
    header('Content-Length: ' . strlen($binaryAudio));
    header('Content-Disposition: inline; filename="speech.mp3"');
    header('Cache-Control: no-cache');
    echo $binaryAudio;
    exit;
} else {
    header("Content-Type: application/json");
    http_response_code(502);
    echo json_encode(["error" => "Invalid API Response Structure", "raw_response" => $data]);
    exit;
}
?>
