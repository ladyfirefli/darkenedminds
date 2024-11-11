<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Include database connection configuration
$config = include('../../private_html/config.php');
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch teams and their scores for a specific round (default to round 1)
$round_number = isset($_POST['round_number']) ? (int)$_POST['round_number'] : 1;
$sql_scores = "
    SELECT s.team_id, s.placement, s.master_kills, s.master_damage, s.master_revives,
           s.padawan_kills, s.padawan_damage, s.padawan_revives,
           master.gamertag AS master_name, padawan.gamertag AS padawan_name
    FROM Scores s
    JOIN Teams t ON s.team_id = t.team_id
    JOIN registrations master ON t.master_player_id = master.id
    JOIN registrations padawan ON t.padawan_player_id = padawan.id
    WHERE s.round_number = ?
";
$stmt = $conn->prepare($sql_scores);
$stmt->bind_param("i", $round_number);
$stmt->execute();
$result_scores = $stmt->get_result();
$stmt->close();

// Fetch teams and their scores for a specific round (default to round 1)
$round_number = isset($_POST['round_number']) ? (int)$_POST['round_number'] : 1;
$sql_points = "
    SELECT t.team_id,
           master.gamertag AS master_name,
           s.master_kills_points,
           s.master_damage_points,
           padawan.gamertag AS padawan_name,
           s.padawan_kills_points,
           s.padawan_damage_points,
           s.revives_points,
           s.placement_points,
           s.total_score
    FROM Scores s
    JOIN Teams t ON s.team_id = t.team_id
    JOIN registrations master ON t.master_player_id = master.id
    JOIN registrations padawan ON t.padawan_player_id = padawan.id
    WHERE s.round_number = ?
";
$stmt = $conn->prepare($sql_points);
$stmt->bind_param("i", $round_number);
$stmt->execute();
$result_points = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Scoring Dashboard</title>
    <link rel="stylesheet" href="../css/tourneystyles.css">
    <style>
        /* Styling for narrower input fields */
        input[type="number"] {
            width: 60px;
            /* Adjust width as needed */
            padding: 5px;
            text-align: center;
        }
    </style>
</head>
<header>
    <div class="header-content"></div>
</header>

<body>
    <nav>
        <ul>
            <li><img src="../assets/DMlogowebsite.png" alt="Logo" class="logo-img"></li>
            <li><a href="../index.html">Home</a></li>
            <li><a href="../view/Tournament.php">Tournament</a></li>
            <li><a href="https://shop.darkenedminds.com">Merch Store</a></li>
            <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
            <li><a href="scoring_dashboard.php" class="current-page">Scoring</a></li>
            <li><a href="../../private_html/services/logout.php">Logout</a></li>
        </ul>
    </nav>
    <section id="scoring">
        <h1>Scoring Dashboard</h1>

        <!-- Round Selection -->
        <form method="POST" style="margin-bottom: 20px;">
            <label for="round_number">Select Round:</label>
            <select name="round_number" id="round_number" onchange="this.form.submit()">
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo $i === $round_number ? 'selected' : ''; ?>>
                        Round <?php echo $i; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </form>

        <!-- Scores Table -->
        <form method="POST" action="../services/update_scores.php">
            <input type="hidden" name="round_number" value="<?php echo $round_number; ?>">
            <table border="1">
                <tr>
                    <th>Team</th>
                    <th>Placement</th>
                    <th>Master (Kills)</th>
                    <th>Damage</th>
                    <th>Revives</th>
                    <th>Padawan (Kills)</th>
                    <th>Damage</th>
                    <th>Revives</th>
                </tr>

                <?php while ($row = $result_scores->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['master_name']) . " & " . htmlspecialchars($row['padawan_name']); ?></td>
                        <td><input type="number" name="teams[<?php echo $row['team_id']; ?>][placement]" value="<?php echo $row['placement']; ?>" required></td>
                        <td><input type="number" name="teams[<?php echo $row['team_id']; ?>][master_kills]" value="<?php echo $row['master_kills']; ?>" required></td>
                        <td><input type="number" name="teams[<?php echo $row['team_id']; ?>][master_damage]" value="<?php echo $row['master_damage']; ?>" required></td>
                        <td><input type="number" name="teams[<?php echo $row['team_id']; ?>][master_revives]" value="<?php echo $row['master_revives']; ?>" required></td>
                        <td><input type="number" name="teams[<?php echo $row['team_id']; ?>][padawan_kills]" value="<?php echo $row['padawan_kills']; ?>" required></td>
                        <td><input type="number" name="teams[<?php echo $row['team_id']; ?>][padawan_damage]" value="<?php echo $row['padawan_damage']; ?>" required></td>
                        <td><input type="number" name="teams[<?php echo $row['team_id']; ?>][padawan_revives]" value="<?php echo $row['padawan_revives']; ?>" required></td>
                    </tr>
                <?php endwhile; ?>
            </table>
            <button type="submit">Update All</button>
        </form>


        <!-- Players Points Table -->
        <h2>Points Earned for Round <?php echo $round_number; ?></h2>
        <table border="1">
            <tr>
                <th>Master Player</th>
                <th>Kills</th>
                <!-- <th>Damage</th> -->
                <th>Padawan Player</th>
                <th>Kills</th>
                <th>Damage</th>
                <th>Team Revives</th>
                <th>Placement Points</th>
                <th>Total Points</th>
            </tr>

            <?php while ($row = $result_points->fetch_assoc()):
                // Calculate total points for the team
                // $master_points = $row['master_kills'] + $row['master_damage'] + $row['master_revives'];
                // $padawan_points = $row['padawan_kills'] + $row['padawan_damage'] + $row['padawan_revives'];
                // $placement_points = $row['placement']; // Assuming placement points are already defined
                // $total_points = $master_points + $padawan_points + $placement_points;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['master_name']); ?></td>
                    <td align="center"><?php echo htmlspecialchars($row['master_kills_points']); ?></td>
                    <!-- <td><?php echo htmlspecialchars($row['master_damage_points']); ?></td> -->
                    <td><?php echo htmlspecialchars($row['padawan_name']); ?></td>
                    <td align="center"><?php echo htmlspecialchars($row['padawan_kills_points']); ?></td>
                    <td align="center"><?php echo htmlspecialchars($row['padawan_damage_points']); ?></td>
                    <td align="center"><?php echo htmlspecialchars($row['revives_points']); ?></td>
                    <td align="center"><?php echo htmlspecialchars($row['placement_points']); ?></td>
                    <td align="center"><?php echo htmlspecialchars($row['total_score']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </section>
</body>

</html>

<?php
$conn->close();
?>