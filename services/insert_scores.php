<?php
session_start();

// Include database connection and helper functions
include_once 'database.php';  

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['team_id'], $_POST['round_number'])) {
    $conn = getDatabaseConnection();

    $team_id = (int)$_POST['team_id'];
    $round_number = (int)$_POST['round_number'];
    $placement = (int)$_POST['placement'];
    $master_kills = (int)$_POST['master_kills'];
    $master_damage = (float)$_POST['master_damage'];
    $master_revives = (int)$_POST['master_revives'];
    $padawan_kills = (int)$_POST['padawan_kills'];
    $padawan_damage = (float)$_POST['padawan_damage'];
    $padawan_revives = (int)$_POST['padawan_revives'];

    // Insert the new score into the Scores table
    $sql_insert_score = "
        INSERT INTO Scores (team_id, round_number, placement, master_kills, master_damage, master_revives, padawan_kills, padawan_damage, padawan_revives) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_insert_score);
    $stmt->bind_param("iiiiiiii", $team_id, $round_number, $placement, $master_kills, $master_damage, $master_revives, $padawan_kills, $padawan_damage, $padawan_revives);
    $stmt->execute();
    $stmt->close();

    // // Redirect back to the scores page
    // header("Location: admin_dashboard.php");
    exit;
}

$conn->close();
?>
