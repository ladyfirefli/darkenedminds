<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../admin/admin_login.php");
    exit;
}

$config = include('../../private_html/config.php');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$round_number = (int)$_POST['round_number'];
$teams = $_POST['teams'];

foreach ($teams as $team_id => $data) {
    $stmt = $conn->prepare("
        UPDATE Scores SET 
            placement = ?, 
            master_kills = ?, master_damage = ?, master_revives = ?, 
            padawan_kills = ?, padawan_damage = ?, padawan_revives = ? 
        WHERE team_id = ? AND round_number = ?");
    $stmt->bind_param(
        "iiiiiiiii", 
        $data['placement'], 
        $data['master_kills'], $data['master_damage'], $data['master_revives'],
        $data['padawan_kills'], $data['padawan_damage'], $data['padawan_revives'], 
        $team_id, 
        $round_number
    );
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Location: ../admin/scoring_dashboard.php?round_number=$round_number");
?>
