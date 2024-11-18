<?php

// Include database connection and helper functions
include_once 'database.php';  
$conn = getTourneyDatabaseConnection();

require_once('discord_functions.php');

// Get form data
$gamertag = $_POST['gamertag'];
$platform = $_POST['platform'];
$email = !empty($_POST['email']) ? $_POST['email'] : NULL;
$discord_name = $_POST['discord_name'] ?? null;

if (!$discord_name) {
    die('Discord name is required.');
}
if (!$platform) {
    die('Platform selection is required.');
}


// Check if the user is in the Discord server
if (!checkDiscordMember($discord_name)) {
    die('Discord name not found in the server. Please join the Discord server first.');
} else {

// Proceed with registration if the Discord name exists
echo "Discord name verified. Proceeding with registration...";
// Continue saving user data to the database

// Grab game stats?
$stats = getFortnitePlayerStats($gamertag, $platform);

if (isset($stats['error'])) {
    echo $stats['error']; // Display the error message
} else {
    echo "Player Stats for {$stats['data']['account']['name']}:\n";
    echo "Wins: {$stats['data']['stats']['all']['overall']['wins']}\n";
    echo "Kills: {$stats['data']['stats']['all']['overall']['kills']}\n";
    echo "Matches Played: {$stats['data']['stats']['all']['overall']['matches']}\n";
}


    // Insert user data into the Players table
    // $stmt = $conn->prepare("
    //     INSERT INTO Players (
    //         gamertag, email, discord_name, discord_id, discord_username, discord_discriminator, discord_roles, discord_joined_at
    //     ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    // ");
    // $stmt->bind_param(
    //     "sssssss",
    //     $gamertag,
    //     $email,
    //     $discord_name,
    //     $member_data['discord_id'],
    //     $member_data['discord_username'],
    //     $member_data['discord_discriminator'],
    //     $member_data['discord_roles'],
    //     $member_data['discord_joined_at']
    // );

// if ($stmt->execute()) {
//     // Send confirmation email with attachment
//     $to = "auto-registration@darkenedminds.com";
//     $subject = "Testing Tournament Registration";
//     $message = "
//         A new participant has registered for the tournament:
        
//         GamerTag: $gamertag
//         " . ($email ? "Email: $email\n" : "") . "
//         Discord Name: $discord_name
//     ";

//     // Boundary for separating different parts of the email
//     $boundary = md5(uniqid(time()));

//     // Headers for email with attachment
//     $headers = "From: no-reply@darkenedminds.com\r\n";
//     $headers .= "MIME-Version: 1.0\r\n";
//     $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

//     // Plain text message
//     $body = "--$boundary\r\n";
//     $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
//     $body .= $message . "\r\n";

//     // Send the email
//     mail($to, $subject, $body, $headers);

//     // Redirect to confirmation page
//     header("Location: ../pages/regresponse.php");
//     exit;
// } else {
//     echo "Error: " . $stmt->error;
// }
}

//  $stmt->close();
//  $conn->close();

 function getFortnitePlayerStats($playerName, $platform) {
    $fortnite_config = include '../../private_html/fortnite_config.php';
    $apiKey = $fortnite_config['fortnite_api_key']; // Replace with your Fortnite API key
    $url = $fortnite_config['fortnite_api_url'] . urlencode($playerName) . "&platform=" . urlencode($platform);
    error_log("Fortnite API URL $url");
    $headers = [
        "Authorization: $apiKey",
        "Content-Type: application/json"
    ];

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Execute request and handle response
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Handle errors
    if ($httpCode === 403) {
        return [
            'error' => 'The player\'s stats are private. Please make them public to access stats.'
        ];
    }

    if ($httpCode !== 200) {
        error_log("Fortnite API request failed with HTTP Code $httpCode. Response: $response");
        return [
            'error' => 'An error occurred while fetching stats. Please try again later.'
        ];
    }

    return json_decode($response, true); // Decode and return the response as an array
}

?>