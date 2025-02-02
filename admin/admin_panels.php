<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}
require_once '../services/tournament_functions.php';
include_once '../services/database.php';

$conn = getTourneyDatabaseConnection();

// Fetch dropdown data
$games = getDropdownData($conn, 'Games');
$tournamentTypes = getDropdownData($conn, 'Tournament_Types');

// Fetch active tournaments for dropdown
$activeTournaments = getActiveTournaments($conn);

// Check if a tournament is selected from the dropdown
$tournament_id = isset($_GET['tournament_id']) ? intval($_GET['tournament_id']) : null;

// Fetch registrations for the selected tournament
$registrations = fetchRegisteredPlayers($conn, $tournament_id);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Create Tournament</title>

    <link rel="stylesheet" href="../css/adminstyles.css">
</head>

<body>
    <!-- Navigation Bar -->
    <?php include 'admin_navbar.php'; ?> <!-- Include your navigation bar -->

    <header>
        <div class="header-content">
            <h1>Admin Dashboard</h1>
        </div>
    </header>        
    <div class="header-content">
            <h2 style="color:white">View Registrations</h2>
    </div>
    <div class="form-wrapper">
        <div class="form-container">

            <div class="registrations-container"> <!-- Unified container -->
                <!-- Tournament Selection Dropdown -->
                <div class="form-group tournament-dropdown">
                    <label for="tournament_id">Select a Tournament:</label>
                    <select name="tournament_id" id="tournament_id">
                        <option value="">All Tournaments</option>
                        <?php foreach ($activeTournaments as $tournament): ?>
                            <option value="<?php echo $tournament['tournament_id']; ?>">
                                <?php echo htmlspecialchars($tournament['tournament_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Table to Display Registrations -->
                <div class="form-group registrations-table">
                    <table id="registrationsTable">
                        <thead>
                            <tr>
                                <th>Gamertag</th>
                                <th>Fee Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7">Select a tournament to view registrations.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Player Count Display -->
<div class="player-count">
    <strong>Total Players Registered:</strong> <span id="playerCount">0</span>
</div>
            </div>
        </div>
    </div>
    <div class="header-content">
            <h2 style="color:white">Create New Tournament</h2>
    </div>
    <div class="form-wrapper">
        <div class="form-container">
            <!-- <h1>Create a New Tournament</h1> -->
            <form action="../services/create_tournament.php" method="POST">
                <!-- Tournament Name -->
                <div class="form-group">
                    <label for="tournament_name">Tournament Name:</label>
                    <input type="text" id="tournament_name" name="tournament_name" required>
                </div>

                <!-- Game Dropdown -->
                <div class="form-group">
                    <label for="game_id">Game:</label>
                    <select id="game_id" name="game_id" required>
                        <option value="">Select a Game</option>
                        <?php foreach ($games as $game): ?>
                            <option value="<?php echo $game['game_id']; ?>">
                                <?php echo htmlspecialchars($game['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tournament Type Dropdown -->
                <div class="form-group">
                    <label for="tournament_type">Tournament Type:</label>
                    <select id="tournament_type" name="tournament_type" required>
                        <option value="">Select a Type</option>
                        <?php foreach ($tournamentTypes as $type): ?>
                            <option value="<?php echo $type['type_id']; ?>">
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tournament Dates -->
                <div class="form-group">
                    <label for="tournament_date">Tournament Date:</label>
                    <input type="date" id="tournament_date" name="tournament_date" required>
                </div>
                <div class="form-group">
                    <label for="registration_start_date">Registration Start Date:</label>
                    <input type="date" id="registration_start_date" name="registration_start_date" required>
                </div>
                <div class="form-group">
                    <label for="registration_end_date">Registration End Date:</label>
                    <input type="date" id="registration_end_date" name="registration_end_date" required>
                </div>

                <!-- Format -->
                <div class="form-group">
                    <label for="format">Format:</label>
                    <input type="text" id="format" name="format" required>
                </div>

                <!-- Entry Fee -->
                <div class="form-group">
                    <label for="entry_fee">Entry Fee:</label>
                    <input type="number" id="entry_fee" name="entry_fee" step="0.01" required>
                </div>

                <!-- Prizes -->
                <div class="form-group">
                    <label for="prizes">Prizes:</label>
                    <textarea id="prizes" name="prizes" placeholder="Describe prizes for 1st, 2nd, 3rd positions" required></textarea>
                </div>

                <button type="submit">Create Tournament</button>
            </form>
        </div>
    </div>

    <script src="../js/admin_panel_scripts.js"></script>
</body>

</html>