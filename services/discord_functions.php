<?php
require_once 'utils.php';

function verifyDiscordName($discord_name)
{
    if (!$discord_name) {
        return [
            'success' => false,
            'message' => 'Discord Name is required.',
        ];
    }

    // Fetch Discord data
    $member_data = checkDiscordMember($discord_name);

    if ($member_data) {
        return [
            'success' => true,
            'discord_id' => $member_data['discord_id'],
            'discord_username' => $member_data['discord_username'],
            'discord_discriminator' => $member_data['discord_discriminator'],
            'discord_roles' => $member_data['discord_roles'],
            'discord_joined_at' => $member_data['discord_joined_at'],
        ];
    } else {
        // return [
        //     'success' => false,
        //     'message' => 'Discord name not found in the server. Please join the Discord server first.',
        // ];
        $inviteResponse = createDiscordInvite();

        if ($inviteResponse['success']) {
            return [
                'success' => false,
                'message' => 'Discord name not found in the server. Please join the Discord server first.',
                'invite_link' => $inviteResponse['invite_link']
            ];
        }

        return [
            'success' => false,
            'message' => 'Discord name not found in the Server. Error creating invite:' . $inviteResponse['message']
        ];
    }
}

function checkDiscordMember($discord_name)
{
    $discord_config = include '../../private_html/discord_config.php';
    $bot_token = $discord_config['discord_bot_token']; // Replace with your bot's token
    $guild_id = $discord_config['discord_guild_id'];  // Replace with your Discord server's ID

    // Discord API endpoint
    $url = "https://discord.com/api/guilds/$guild_id/members/search?query=" . urlencode($discord_name);

    // Set up the HTTP headers with authorization
    $headers = [
        "Authorization: Bot $bot_token",
        "Content-Type: application/json",
    ];

    // Make the API request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check for errors
    if ($http_code !== 200) {
        customLog("Discord API request failed with HTTP Code $http_code. Response: $response");
        return false;
    }

    // Decode the response
    $members = json_decode($response, true);

    // Parse and return the member data
    foreach ($members as $member) {
        if (isset($member['user']['username']) && strcasecmp($member['user']['username'], $discord_name) === 0) {
            // Extract necessary fields
            return [
                'discord_id' => $member['user']['id'] ?? null,
                'discord_username' => $member['user']['username'] ?? null,
                'discord_discriminator' => $member['user']['discriminator'] ?? null,
                'discord_roles' => isset($member['roles']) ? json_encode($member['roles']) : null,
                'discord_joined_at' => $member['joined_at'] ?? null
            ];
        }
    }

    // If no matching member found
    return false;
}

function createDiscordInvite()
{
    // Load configuration
    $discord_config = include '../../private_html/discord_config.php';
    $bot_token = $discord_config['discord_bot_token']; // Bot token
    $guild_id = $discord_config['discord_guild_id'];   // Guild ID

    // Step 1: Get the system channel
    $system_channel_id = getGuildSystemChannel($guild_id, $bot_token);
    if (!$system_channel_id) {
        customLog("No system channel found for guild ID: $guild_id");
        return [
            'success' => false,
            'message' => 'Unable to find the system channel for the guild.'
        ];
    }

    // Step 2: Create an invite for the system channel
    $inviteUrl = "https://discord.com/api/v10/channels/$system_channel_id/invites";
    $headers = [
        "Authorization: Bot $bot_token",
        "Content-Type: application/json"
    ];
    $inviteData = json_encode([
        'max_age' => 3600, // Invite valid for 1 hour
        'max_uses' => 1,   // Invite valid for one use
        'unique' => true
    ]);

    // Initialize cURL to create the invite
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $inviteUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $inviteData);

    $inviteResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $inviteResponseData = json_decode($inviteResponse, true);
        return [
            'success' => true,
            'invite_link' => "https://discord.gg/" . $inviteResponseData['code']
        ];
    }

    // Log error and return failure
    customLog("Failed to create invite for system channel. HTTP Code: $httpCode, Response: $inviteResponse");
    return [
        'success' => false,
        'message' => 'Unable to create an invite for the system channel.'
    ];
}

function getGuildSystemChannel($guild_id, $bot_token)
{
    // Fetch the guild's system channel
    $url = "https://discord.com/api/v10/guilds/$guild_id";
    $headers = [
        "Authorization: Bot $bot_token",
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        customLog("Failed to fetch guild system channel. HTTP Code: $httpCode, Response: $response");
        return null;
    }

    $guild = json_decode($response, true);
    return $guild['system_channel_id'] ?? null;
}


?>
