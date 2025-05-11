<?php
// AI Chat API relay for EliteFit member dashboard
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['message']) || trim($data['message']) === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

$apiKey = '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'; // OpenRouter API key set directly
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'API key not configured']);
    exit;
}

// Restrictive prompt for gym-related topics only
$systemPrompt = "You are a helpful EliteFit gym assistant. Only answer questions about fitness, workouts, nutrition, gym etiquette, and gym-related topics. If the question is not gym-related, politely refuse to answer.";

$userMessage = $data['message'];

$payload = [
    'model' => 'openai/gpt-3.5-turbo',
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $userMessage]
    ],
    'max_tokens' => 300,
    'temperature' => 0.6
];

$ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json',
    'HTTP-Referer: https://elitefit.local/' // Optional: set your domain
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode !== 200) {
    http_response_code($httpcode);
    echo json_encode(['error' => 'AI service error', 'details' => $response]);
    exit;
}

$result = json_decode($response, true);
$aiReply = $result['choices'][0]['message']['content'] ?? 'Sorry, I could not process your request.';
echo json_encode(['reply' => $aiReply]);
