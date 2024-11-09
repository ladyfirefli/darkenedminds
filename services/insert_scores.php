<?php
session_start();
$config = include('../../private_html/config.php');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['team_id'], $_POST['round_number'])) {
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
