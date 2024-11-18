<?php
header('Content-Type: application/json');

// Include the file with your Discord functions
require '../services/discord_functions.php';

// Get input from the AJAX request
$input = json_decode(file_get_contents('php://input'), true);
$discord_name = $input['discord_name'] ?? '';

// Call the encapsulated function
$response = verifyDiscordName($discord_name);

// Return the response as JSON
echo json_encode($response);
?>