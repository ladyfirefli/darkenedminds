<?php

// Include database connection and helper functions
include_once 'database.php';
$conn = getTourneyDatabaseConnection();

require_once('discord_functions.php');
require_once 'utils.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Fetch tournament ID from the form
        $tournament_id = $_POST['tournament_id'] ?? null;

        if (!$tournament_id) {
            throw new Exception('Tournament selection is required.');
        }

        $email = !empty($_POST['email']) ? $_POST['email'] : null;
        $discord_name = $_POST['discord_name'] ?? null;

        $discordData = isset($_POST['discord_data']) ? json_decode($_POST['discord_data'], true) : null;
        $fortniteData = isset($_POST['fortnite_data']) ? json_decode($_POST['fortnite_data'], true) : null;

        if (!$discordData || !$fortniteData) {
            customLog($discordData);
            customLog($fortniteData);
            throw new Exception('Required data is missing. Please verify your Discord and Fortnite details.');
        }

        $gamertag = $fortniteData['gamertag'];
        $platform = $fortniteData['platform'];
        $wins = $fortniteData['stats']['wins'];
        $matches = $fortniteData['stats']['matches'];
        if ($matches > 0) {
            $winRate = ($wins / $matches) * 100;
        } else {
            $winRate = 0; // Default to 0 if matches are 0
        }

        // Call the stored procedure to get or insert the player
        $player_id = getOrInsertPlayer($conn, $gamertag, $email, $discordData, $discord_name);

        if ($player_id) {
            // Assume $conn is already initialized
            $partnerGamertag = null; // Optional
            $additionalData = null; // Optional

            // Register the player and create game stats
            $registrationId = createRegistration($conn, $player_id, $tournament_id, $partnerGamertag, $additionalData);
            $statsId = createGameStats($conn, $registrationId, $tournament_id, $gamertag, null, null, $matches, $winRate, null, null);

            // Send confirmation email
            sendConfirmationEmail("auto-registration@darkenedminds.com", $gamertag, $email, $discord_name);
        }
        echo json_encode([
            'success' => true,
            'message' => "Registration successful!",
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => "Error: " . $e->getMessage(),
        ]);
    } finally {
        $conn->close();
    }
    //exit;
}


function getFortnitePlayerStats($playerName, $platform)
{
    $fortnite_config = include '../../private_html/fortnite_config.php';
    $apiKey = $fortnite_config['fortnite_api_key']; // Replace with your Fortnite API key
    $url = $fortnite_config['fortnite_api_url'] . urlencode($playerName) . "&platform=" . urlencode($platform);
    customLog("Fortnite API URL $url");
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
        customLog("Fortnite API request failed with HTTP Code $httpCode. Response: $response");
        return [
            'error' => 'An error occurred while fetching stats. Please try again later.'
        ];
    }

    return json_decode($response, true); // Decode and return the response as an array
}

function getOrInsertPlayer($conn, $gamertag, $email, $discordData, $discord_name)
{
    $stmt = $conn->prepare("CALL GetOrInsertPlayer(?, ?, ?, ?, ?, ?, ?, ?, @player_id)");
    $stmt->bind_param(
        "ssssssss",
        $gamertag,
        $email,
        $discord_name,
        $discordData['discord_id'],
        $discordData['discord_username'],
        $discordData['discord_discriminator'],
        $discordData['discord_roles'],
        $discordData['discord_joined_at']
    );

    if (!$stmt->execute()) {
        throw new Exception("Failed to execute stored procedure: " . $stmt->error);
    }

    // Retrieve the output parameter
    $result = $conn->query("SELECT @player_id AS player_id");
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row['player_id'];
}

function sendConfirmationEmail($to, $gamertag, $email, $discord_name)
{
    $subject = " Test Tournament Registration Confirmation";
    $message = "
        A new participant has registered for the tournament:

        GamerTag: $gamertag
        " . ($email ? "Email: $email\n" : "") . "
        Discord Name: $discord_name
    ";

    // Boundary for separating different parts of the email
    $boundary = md5(uniqid(time()));

    // Headers for email with attachment
    $headers = "From: no-reply@darkenedminds.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

    // Plain text message
    $body = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $body .= $message . "\r\n";

    // Send the email
    mail($to, $subject, $body, $headers);
}

function createRegistration($conn, $playerId, $tournamentId, $partnerGamertag = null, $additionalData = null)
{
    if (!$conn || $conn->connect_error) {
        throw new Exception("Database connection error: " . $conn->connect_error);
    }

    try {
        // Prepare the SQL to call the stored procedure
        $stmt = $conn->prepare("CALL CreateRegistrationIfNotExists(?, ?, ?, ?, @registration_id)");
        $stmt->bind_param("iiss", $playerId, $tournamentId, $partnerGamertag, $additionalData);

        // Execute the procedure
        if ($stmt->execute()) {
            // Fetch the OUT parameter
            $result = $conn->query("SELECT @registration_id AS registration_id");
            if ($row = $result->fetch_assoc()) {
                return $row['registration_id'];
            } else {
                throw new Exception("Failed to retrieve registration ID.");
            }
        } else {
            throw new Exception("Error executing stored procedure: " . $stmt->error);
        }
    } finally {
        // Clean up
        if (isset($stmt)) {
            $stmt->close();
        }
    }

    return null; // Fallback if something unexpected happens
}

function createGameStats($conn, $registrationId, $tournamentId, $gamertag, $kills = 0, $damage = 0, $matchesPlayed = 0, $winRate = null, $kdr = null, $additionalData = null)
{
    if (!$conn || $conn->connect_error) {
        throw new Exception("Database connection error: " . $conn->connect_error);
    }

    try {
        // Prepare the stored procedure call
        $stmt = $conn->prepare("CALL CreateGameStatsIfNotExists(?, ?, ?, ?, ?, ?, ?, ?, ?, @stats_id)");
        $stmt->bind_param("iisiiidds", $registrationId, $tournamentId, $gamertag, $kills, $damage, $matchesPlayed, $winRate, $kdr, $additionalData);

        // Execute the procedure
        if ($stmt->execute()) {
            // Fetch the output parameter
            $result = $conn->query("SELECT @stats_id AS stats_id");
            if ($row = $result->fetch_assoc()) {
                return $row['stats_id'];
            } else {
                throw new Exception("Failed to retrieve stats ID.");
            }
        } else {
            throw new Exception("Error executing stored procedure: " . $stmt->error);
        }
    } finally {
        // Clean up
        if (isset($stmt)) {
            $stmt->close();
        }
    }

    return null; // Fallback if something unexpected happens
}
