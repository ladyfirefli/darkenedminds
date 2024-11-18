<?php

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
        error_log("Discord API request failed with HTTP Code $http_code. Response: $response");
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
