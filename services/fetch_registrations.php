<?php
include_once 'database.php';
require_once 'tournament_functions.php';
require_once 'utils.php';
$conn = getTourneyDatabaseConnection();

$tournament_id = isset($_GET['tournament_id']) ? intval($_GET['tournament_id']) : null;
$registrations = fetchRegisteredPlayers($conn, $tournament_id);

customLog($tournament_id);

$conn->close();
echo json_encode($registrations);
?>
