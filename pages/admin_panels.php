<?php
require_once '../services/tournament_functions.php';
include_once '../services/database.php'; 

$conn = getTourneyDatabaseConnection();

// Fetch dropdown data
$games = getDropdownData($conn, 'Games');
$tournamentTypes = getDropdownData($conn, 'Tournament_Types');

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Create Tournament</title>
</head>
<body>
    <h1>Create a New Tournament</h1>
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
</body>
</html>
