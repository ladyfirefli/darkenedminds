<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../admin/admin_login.php");
    exit;
}

// Include database configuration
$config = include('../../private_html/config.php');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check connection
if ($conn->connect_error) {
    $_SESSION['reset_message'] = "Connection failed: " . $conn->connect_error;
    header("Location: ../admin/admin_dashboard.php");
    exit;
}

// Define the number of rounds to prepopulate
$num_rounds = 5;

// Fetch all team IDs from the Teams table
$team_ids = [];
$result_teams = $conn->query("SELECT team_id FROM Teams");
if ($result_teams) {
    while ($row = $result_teams->fetch_assoc()) {
        $team_ids[] = $row['team_id'];
    }
    $result_teams->free();
} else {
    $_SESSION['reset_message'] = "Error fetching team IDs: " . $conn->error;
    header("Location: ../admin/admin_dashboard.php");
    exit;
}

// Prepare the insert statement
$insert_stmt = $conn->prepare("
    INSERT IGNORE INTO Scores (
        team_id, round_number, placement, master_kills, master_damage, master_revives,
        padawan_kills, padawan_damage, padawan_revives
    ) VALUES (?, ?, 0, 0, 0, 0, 0, 0, 0)
");

if ($insert_stmt === false) {
    $_SESSION['reset_message'] = "Error preparing statement: " . $conn->error;
    $conn->close();
    header("Location: ../admin/admin_dashboard.php");
    exit;
}

// Insert zero values for each team and each round
try {
    foreach ($team_ids as $team_id) {
        for ($round_number = 2; $round_number <= $num_rounds; $round_number++) {
            $insert_stmt->bind_param("ii", $team_id, $round_number);
            $insert_stmt->execute();
        }
    }

    $_SESSION['reset_message'] = "Prepopulation of Scores table with zero values is complete.";
} catch (Exception $e) {
    $_SESSION['reset_message'] = "Error prepopulating scores: " . $e->getMessage();
}

// Close the statement and connection
$insert_stmt->close();
$conn->close();

// Redirect to admin dashboard
header("Location: ../admin/admin_dashboard.php");
exit;
?>
