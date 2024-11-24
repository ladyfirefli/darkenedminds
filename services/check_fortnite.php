<?php
header('Content-Type: application/json');

$fortnite_config = include '../../private_html/fortnite_config.php';
$apiKey = $fortnite_config['fortnite_api_key']; // Replace with your Fortnite API key

// Get input from the AJAX request
$input = json_decode(file_get_contents('php://input'), true);
$gamertag = $input['gamertag'] ?? null;
$platform = $input['platform'] ?? null;

// Validate input
if (!$gamertag || !$platform) {
    echo json_encode([
        'success' => false,
        'message' => 'Gamertag and platform are required.',
    ]);
    exit;
}

// Call the Fortnite API
$url = $fortnite_config['fortnite_api_url'] . urlencode($gamertag) . "&platform=" . urlencode($platform);

$headers = [
    "Authorization: $apiKey",
    "Content-Type: application/json"
];

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Execute and process the response
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 403) {
    echo json_encode([
        'success' => false,
        'message' => 'The player\'s stats are private. Please make them public in Fortnite settings.',
    ]);
    exit;
}

if ($httpCode !== 200) {
    echo json_encode([
        'success' => false,
        'message' => "Error fetching Fortnite stats. HTTP Code: $httpCode.",
    ]);
    exit;
}

$data = json_decode($response, true);

// Extract relevant data
if (isset($data['data'])) {
    $stats = $data['data']['stats']['all']['overall'] ?? [];
    echo json_encode([
        'success' => true,
        'gamertag' => $data['data']['account']['name'],
        'platform' => $platform,
        'stats' => [
            'wins' => $stats['wins'] ?? 0,
            'kills' => $stats['kills'] ?? 0,
            'matches' => $stats['matches'] ?? 0,
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Unable to retrieve stats for the provided gamertag and platform.',
    ]);
}


?>