<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

set_time_limit(120); 
ini_set('max_execution_time', 120);
ini_set('display_errors', 0); 

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$prompt = isset($_GET['prompt']) ? trim($_GET['prompt']) : '';
$voice = isset($_GET['voice']) ? trim($_GET['voice']) : 'alloy';
$emotion = isset($_GET['emotion']) ? trim($_GET['emotion']) : 'neutral';
$apiKey = isset($_GET['key']) ? trim($_GET['key']) : '';
$seed = isset($_GET['seed']) ? intval($_GET['seed']) : rand(1, 999999);

if (empty($prompt)) {
    header("Content-Type: application/json");
    http_response_code(400);
    echo json_encode(["error" => "Missing prompt."]);
    exit;
}

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

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 90);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['choices'][0]['message']['audio']['data'])) {
    $binaryAudio = base64_decode($data['choices'][0]['message']['audio']['data']);
    header('Content-Type: audio/mpeg');
    header('Content-Length: ' . strlen($binaryAudio));
    echo $binaryAudio;
} else {
    http_response_code(502);
    echo json_encode(["error" => "API Error"]);
}
?>
