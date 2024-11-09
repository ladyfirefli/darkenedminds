<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Include database connection configuration
$config = include('../../private_html/config.php');

// Connect to the database
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch registrations
$sql_registrants = "SELECT gamertag, email, role, partner, fee_received, last_season_KDR, last_season_avg FROM registrations ORDER BY last_season_avg DESC";
$result_registrants = $conn->query($sql_registrants);

// Fetch available Masters and Padawans who are not yet paired in a team
$sql_available_masters = "
    SELECT id, gamertag 
    FROM registrations 
    WHERE role = 'Master' 
    AND id NOT IN (SELECT master_player_id FROM Teams)";
$masters_result = $conn->query($sql_available_masters);

$sql_available_padawans = "
    SELECT id, gamertag 
    FROM registrations 
    WHERE role = 'Padawan' 
    AND id NOT IN (SELECT padawan_player_id FROM Teams)";
$padawans_result = $conn->query($sql_available_padawans);

// Fetch teams for the dropdown
$sql_teams = "SELECT t.team_id, 
                     master.gamertag AS master, 
                     padawan.gamertag AS padawan 
              FROM Teams t
              JOIN registrations master ON t.master_player_id = master.id 
              JOIN registrations padawan ON t.padawan_player_id = padawan.id";
$teams_result = $conn->query($sql_teams);
if (!$teams_result) {
    die("Query failed: " . $conn->error);
}
// Handle team creation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['master_id'], $_POST['padawan_id'])) {
    $master_id = (int)$_POST['master_id'];
    $padawan_id = (int)$_POST['padawan_id'];

    // Insert the new team into the Teams table
    $sql_insert_team = "INSERT INTO Teams (master_player_id, padawan_player_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql_insert_team);
    $stmt->bind_param("ii", $master_id, $padawan_id);
    $stmt->execute();

    // Get the ID of the newly created team
    $new_team_id = $stmt->insert_id;
    $stmt->close();

    // Insert initial score entry into Scores table for round zero
    $sql_insert_score = "
        INSERT INTO Scores (team_id, round_number, master_kills, master_damage, master_revives, padawan_kills, padawan_damage, padawan_revives, placement) 
        VALUES (?, 1, 0, 0, 0, 0, 0, 0, ?)";
    $stmt = $conn->prepare($sql_insert_score);
    $placement = 0; // Or any other initial placement value you want to set

    // Bind parameters for the team ID and placement
    $stmt->bind_param("ii", $new_team_id, $placement); // Assuming $placement is defined in your code
    $stmt->execute();
    $stmt->close();

    // Refresh available players and teams after creation
    $masters_result = $conn->query($sql_available_masters);
    $padawans_result = $conn->query($sql_available_padawans);
    $teams_result = $conn->query($sql_teams); // Refresh the teams result after a new team is created
}

// Handle team deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_team_id'])) {
    $team_id = (int)$_POST['delete_team_id'];

    // Delete associated scores from Scores table
    $sql_delete_scores = "DELETE FROM Scores WHERE team_id = ?";
    $stmt = $conn->prepare($sql_delete_scores);
    $stmt->bind_param("i", $team_id);
    $stmt->execute();
    $stmt->close();

    // Delete team from Teams table
    $sql_delete_team = "DELETE FROM Teams WHERE team_id = ?";
    $stmt = $conn->prepare($sql_delete_team);
    $stmt->bind_param("i", $team_id);
    $stmt->execute();
    $stmt->close();

    // Refresh teams after deletion
    $teams_result = $conn->query($sql_teams);
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Tournament</title>
    <link rel="stylesheet" href="../css/tourneystyles.css">
</head>

<body>
    <nav>
        <ul>
            <li><img src="../assets/DMlogowebsite.png" alt="Logo" class="logo-img"></li>
            <li><a href="../index.html">Home</a></li>
            <li><a href="../Tournament.php">Tournament</a></li>
            <li><a href="https://shop.darkenedminds.com">Merch Store</a></li>
            <li><a href="admin_dashboard.php" class="current-page">Admin Dashboard</a></li>
            <li><a href="scoring_dashboard.php">Scoring</a></li>
            <li><a href="../../private_html/services/logout.php">Logout</a></li>
        </ul>
    </nav>

    <header>
        <div class="header-content">
            <h1>Admin Dashboard</h1>
        </div>
    </header>

    <!-- Team Setup Section -->
    <section id="team-setup">
        <h2>Set Up Teams</h2>
        <form method="POST">
            <label for="master_id">Select Master:</label>
            <select name="master_id" id="master_id" required>
                <option value="">--Select Master--</option>
                <?php while ($master = $masters_result->fetch_assoc()): ?>
                    <option value="<?php echo $master['id']; ?>">
                        <?php echo htmlspecialchars($master['gamertag']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="padawan_id">Select Padawan:</label>
            <select name="padawan_id" id="padawan_id" required>
                <option value="">--Select Padawan--</option>
                <?php while ($padawan = $padawans_result->fetch_assoc()): ?>
                    <option value="<?php echo $padawan['id']; ?>">
                        <?php echo htmlspecialchars($padawan['gamertag']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit">Create Team</button>
        </form>

        <h3>Current Teams</h3>
        <div class="table-wrapper">
            <table border="1">
                <tr>
                    <th>Team ID</th>
                    <th>Master</th>
                    <th>Padawan</th>
                    <th>Actions</th>
                </tr>
                <?php if ($teams_result && $teams_result->num_rows > 0): ?>
                    <?php while ($team = $teams_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $team['team_id']; ?></td>
                            <td><?php echo htmlspecialchars($team['master']); ?></td>
                            <td><?php echo htmlspecialchars($team['padawan']); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="delete_team_id" value="<?php echo $team['team_id']; ?>">
                                    <button type="submit">Unassign Team</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No teams created yet.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

    </section>

    <section id="finalize-scores">
        <h2>Tournament Setup</h2>
        <form action="../services/prepopulate_scores.php" method="POST">
            <button type="submit" name="finalize_scores">Finalize Scores</button>
        </form>
        <form action="../services/reset_tourney.php" method="POST">
            <button type="submit" name="reset_tourney">Reset Everything</button>
        </form>
    </section>
    <?php
if (isset($_SESSION['reset_message'])) {
    echo "<p>" . htmlspecialchars($_SESSION['reset_message']) . "</p>";
    unset($_SESSION['reset_message']);
}
?>

<section id="registrants">
        <h2>Tournament Registrants</h2>
        <div class="table-wrapper">
            <table border="1">
                <tr>
                    <th>GamerTag</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Partner</th>
                    <th>Paid</th>
                    <th>KDR</th>
                    <th>Average Dmg</th>
                </tr>
                <?php if ($result_registrants && $result_registrants->num_rows > 0): ?>
                    <?php while ($row = $result_registrants->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['gamertag'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['role'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['partner'] ?? ''); ?></td>
                            <td><?php echo $row['fee_received'] ? 'Paid' : ''; ?></td>
                            <td align="center"><?php echo htmlspecialchars($row['last_season_KDR'] ?? ''); ?></td>
                            <td align="center"><?php echo htmlspecialchars($row['last_season_avg'] ?? ''); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No registrants found.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
        <p><a href="logout.php">Logout</a></p>
    </section>
    <footer>
        <p>Sponsored by Darkened Minds Discord Admins</p>
    </footer>
</body>

</html>

<?php
// Close the database connection
$conn->close();
?>