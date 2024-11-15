<?php
session_start();

// Include database connection configuration
$config = include('../../private_html/config.php');
// Include database connection and helper functions
include_once 'database.php';  
$conn = getDatabaseConnection();

// Fetch all registrants
function fetchRegistrants($conn) {
    $sql = "SELECT gamertag, email, role, partner, fee_received, last_season_KDR, last_season_avg FROM registrations ORDER BY last_season_avg DESC";
    return $conn->query($sql);
}

// Fetch available masters
function fetchAvailableMasters($conn) {
    $sql = "SELECT id, gamertag FROM registrations WHERE role = 'Master' AND id NOT IN (SELECT master_player_id FROM Teams)";
    return $conn->query($sql);
}

// Fetch available padawans
function fetchAvailablePadawans($conn) {
    $sql = "SELECT id, gamertag FROM registrations WHERE role = 'Padawan' AND id NOT IN (SELECT padawan_player_id FROM Teams)";
    return $conn->query($sql);
}

// Fetch teams
function fetchTeams($conn) {
    $sql = "SELECT t.team_id, master.gamertag AS master, padawan.gamertag AS padawan
            FROM Teams t
            JOIN registrations master ON t.master_player_id = master.id
            JOIN registrations padawan ON t.padawan_player_id = padawan.id";
    return $conn->query($sql);
}

// Create a new team and insert into the Scores table
function createTeam($conn, $master_id, $padawan_id) {
    $sql_insert_team = "INSERT INTO Teams (master_player_id, padawan_player_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql_insert_team);
    $stmt->bind_param("ii", $master_id, $padawan_id);
    $stmt->execute();
    $new_team_id = $stmt->insert_id;
    $stmt->close();

    // Insert initial score entry
    $sql_insert_score = "INSERT INTO Scores (team_id, round_number, master_kills, master_damage, master_revives, padawan_kills, padawan_damage, padawan_revives, placement) VALUES (?, 1, 0, 0, 0, 0, 0, 0, 0)";
    $stmt = $conn->prepare($sql_insert_score);
    $stmt->bind_param("i", $new_team_id);
    $stmt->execute();
    $stmt->close();
}

// Delete a team and associated scores
function deleteTeam($conn, $team_id) {
    $sql_delete_scores = "DELETE FROM Scores WHERE team_id = ?";
    $stmt = $conn->prepare($sql_delete_scores);
    $stmt->bind_param("i", $team_id);
    $stmt->execute();
    $stmt->close();

    $sql_delete_team = "DELETE FROM Teams WHERE team_id = ?";
    $stmt = $conn->prepare($sql_delete_team);
    $stmt->bind_param("i", $team_id);
    $stmt->execute();
    $stmt->close();
}
?>
