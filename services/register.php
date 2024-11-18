<?php

// Include database connection and helper functions
include_once 'database.php';  
$conn = getTourneyDatabaseConnection();

require_once('discord_functions.php');

// Get form data
$gamertag = $_POST['gamertag'];
$email = !empty($_POST['email']) ? $_POST['email'] : NULL;
$discord_name = $_POST['discord_name'] ?? null;

if (!$discord_name) {
    die('Discord name is required.');
}

// Check if the user is in the Discord server
if (!checkDiscordMember($discord_name)) {
    die('Discord name not found in the server. Please join the Discord server first.');
} else {

// Proceed with registration if the Discord name exists
echo "Discord name verified. Proceeding with registration...";
// Continue saving user data to the database

// Grab game stats?

// Prepare and bind
// $stmt = $conn->prepare("INSERT INTO registrations (gamertag, email, role, partner, screenshot) VALUES (?, ?, ?, ?, ?)");
// $stmt->bind_param("sssss", $gamertag, $email, $role, $partner, $screenshot);

    // Insert user data into the Players table
    $stmt = $conn->prepare("
        INSERT INTO Players (
            gamertag, email, discord_name, discord_id, discord_username, discord_discriminator, discord_roles, discord_joined_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "sssssss",
        $gamertag,
        $email,
        $discord_name,
        $member_data['discord_id'],
        $member_data['discord_username'],
        $member_data['discord_discriminator'],
        $member_data['discord_roles'],
        $member_data['discord_joined_at']
    );

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

 $stmt->close();
 $conn->close();
?>