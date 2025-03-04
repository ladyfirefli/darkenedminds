<?php
// Include necessary files and functions
include '../services/get_signup_count.php';
$config = include('../../private_html/config.php');
include '../services/fetch_scores.php';
include '../services/fetch_round_scores.php';
include '../services/tournament_status.php';

// Fetch the signup count
$signup_count = getSignupCount();

// Check if the tournament has started
$tournament_started = isTournamentStarted();

// Fetch scores only if the tournament has started
$scores = [];
if ($tournament_started) {
    $result_scores = fetchScores();
    if ($result_scores && $result_scores->num_rows > 0) {
        while ($row = $result_scores->fetch_assoc()) {
            $scores[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Master and Padawan Tournament</title>
    <link rel="stylesheet" href="../css/padawantourneystyles.css">
</head>

<body>
   <!-- Navigation Bar -->
   <?php include 'navbar.php'; ?>

    <header>
        <div class="header-content">
        </div>
    </header>

    <section id="about">
        <h2>About the Tournament</h2>
        <p>
            Join our 'Master and Padawan' duos tournament where players will compete for the top prize.
            Masters and Padawans will battle it out across 4 rounds with points awarded for kills, damage,
            and revives.
        </p>
        <p>
            To encourage mentorship throughout the tournament, the Padawan's points are doubled!
        </p>
        <h2>Current Pot is: <strong>$<?php echo htmlspecialchars($signup_count * 2 + 50); ?></strong> </h2>
        <p align="center">Each player on the winning Team will receive <strong>$<?php echo htmlspecialchars($signup_count + 50 / 2); ?></strong></p>

    </section>


    <?php if ($tournament_started): ?>
        <!-- Results Section -->
        <section id="results">

            <h2>Tournament Results</h2>
            <div class="table-wrapper">
                <table border="1">
                    <tr>
                        <!-- <th>Team ID</th> -->
                        <th>Team Total Score</th>
                        <th>Master GamerTag</th>
                        <th>Padawan GamerTag</th>
                        <th>Master Total Score</th>
                        <th>Padawan Total Score</th>
                        <th>Total Revives Points</th> <!-- New column for total revives points -->
                        <th>Total Placement Points</th> <!-- New column for total placement points -->
                    </tr>
                    <?php if (count($scores) > 0): ?>
                        <?php foreach ($scores as $row): ?>
                            <tr>
                                <!-- <td><?php echo htmlspecialchars($row['team_id']); ?></td> -->
                                <td align="center"><?php echo htmlspecialchars($row['team_total_score']); ?></td>
                                <td><?php echo htmlspecialchars($row['master']); ?></td>
                                <td><?php echo htmlspecialchars($row['padawan']); ?></td>
                                <td align="center"><?php echo htmlspecialchars($row['master_total_score']); ?></td>
                                <td align="center"><?php echo htmlspecialchars($row['padawan_total_score']); ?></td>
                                <td align="center"><?php echo htmlspecialchars($row['revives_total']); ?></td> <!-- Display revives total -->
                                <td align="center"><?php echo htmlspecialchars($row['placement_total']); ?></td> <!-- Display placement total -->
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">No scores available yet.</td>
                        </tr>
                    <?php endif; ?>
                </table>

            </div>

            <div class="scoreboard-container">
                <?php

                // Fetch team data and scores for each round
                for ($round_number = 1; $round_number <= 4; $round_number++) {
                    echo "<h2 class='round-title'>Round $round_number Summary</h2>";

                    // Fetch scores for the current round
                    $round_scores = fetchRoundScores($round_number);
                    // Check if there are scores to display
                    if (count($round_scores) > 0) {

                    // Display table for the round
                    echo "<table class='score-table'>
                    <thead>
                        <tr>
                            <th>Master</th>
                            <th>Padawan</th>
                            <th>Placement Points</th>
                            <th>Master Kills Points</th>
                            <th>Padawan Kills Points</th>
                            <th>Padawan Damage Points</th>
                            <th>Revives Points</th>
                            <th>Total Score</th>
                        </tr>
                    </thead>
                    <tbody>";

                        foreach ($round_scores as $row) {
                            echo "<tr>
                        <td>{$row['master']}</td>
                        <td>{$row['padawan']}</td>
                        <td>{$row['placement_points']}</td>
                        <td>{$row['master_kills_points']}</td>
                        <td>{$row['padawan_kills_points']}</td>
                        <td>{$row['padawan_damage_points']}</td>
                        <td>{$row['revives_points']}</td>
                        <td>{$row['total_score']}</td>
                      </tr>";
                        }
                        echo "</tbody></table>";
                    } else {
                        echo "<p>No scores available for this round.</p>";
                    }
                }
                ?>
            </div>
        </section>

    <?php else: ?>
        <section id="registration">
            <h2>Registration</h2>
            <p>To register, fill out the form below or send your team stats and info to: <a href="mailto:tournament@darkenedminds.com">tournament@darkenedminds.com</a></p>
            <p>Include your Apex Legends stat screens and mention if you’re Master or Padawan!</p>
            <h3>Example stats screen:</h3>
            <div class="stats-image">
                <img src="../assets/Screenshot_2024-10-23_160750.png" alt="stats" class="stats-img">
            </div>
            <p>Hover over your Gamer Tag in the lobby and Inspect.</p>

            <!-- registration.html -->
            <div class="form-wrapper">
                <div class="form-container">
                    <form action="../services/register.php" method="POST" enctype="multipart/form-data" class="registration-form">
                        <h2>Tournament Registration</h2>

                        <div class="form-group">
                            <label for="gamertag">GamerTag:</label>
                            <input type="text" name="gamertag" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email (optional):</label>
                            <input type="email" name="email">
                        </div>

                        <div class="form-group">
                            <label>Role:</label>
                            <label><input type="radio" name="role" value="master" required> Master</label>
                            <label><input type="radio" name="role" value="padawan" required> Padawan</label>
                        </div>

                        <div class="form-group">
                            <label for="partner">Partner's GamerTag (optional):</label>
                            <input type="text" name="partner">
                        </div>

                        <div class="form-group">
                            <label for="screenshot">Game Stats Screenshot:</label>
                            <input type="file" name="screenshot" accept="image/*" required>
                        </div>

                        <button type="submit">Register</button>
                    </form>
                </div>
            </div>

        </section>

    <?php endif; ?>

    <section id="signup-count" style="text-align: center; margin-top: 20px;">
        <h2>Current Signups for the Tournament</h2>
        <p><strong><?php echo htmlspecialchars($signup_count); ?></strong> players have signed up!</p>
    </section>

    <section id="prizes">
        <h2>Prizes</h2>
        <ul>
            <li>
                <div class="prize-image">
                    <img src="../assets/banknotes_hires.png" alt="Cash" class="prize-img">
                </div>
                <div class="prize-content">
                    <strong>1st Place:</strong> Cash Pool
                </div>
            </li>
            <li>
                <div class="prize-image">
                    <img src="../assets/hoodie.png" alt="Custom Hoodie" class="prize-img">
                </div>
                <div class="prize-content">
                    <strong>2nd Place:</strong> Custom Hoodie
                </div>
            </li>
            <li>
                <div class="prize-image">
                    <img src="../assets/sticker.png" alt="Stickers" class="prize-img">
                </div>
                <div class="prize-content">
                    <strong>3rd Place:</strong> Stickers
                </div>
            </li>
        </ul>
    </section>

    <section id="Rules">
        <h2>Rules</h2>
        <h3> Role Assignment</h3>
        <p>In order to keep it as fair as possible, we ask that
            each player submit their Stat screens with registration. We will evaluate the KDR, average damage, and account level to rank registered players as Master or Padawan.
        </p>

        <h3> Team Assignment</h3>
        <p>While we understand wanting to come prepared with a pre-assembled team, we cannot guarantee that pairs will be placed together. If we can keep
            requested partners together, we will. However, we would like to keep the lobby balanced. </p>
        <h3>Drop Assignment</h3>
        <p>Drop zones will be randomely assigned each match.</p>
        <h3>Rounds</h3>
        <p>There will be four rounds to compete for the top spot as a team.</p>
        <h3>Map Assignment</h3>
        <p>Each round of the tournament will take place on a different map. No map will be repeated, ensuring a unique battlefield experience for every round. Get ready to adapt and strategize as the terrain changes throughout the competition!</p>
        <h3>Entry Fee</h3>
        <p>
            Entry fee per person will be $2.
        </p>
        <h3>Join us!</h3>
        <p> <a href="https://discord.gg/FqqZ6XTNDW">Join our Discord</a> </p>
    </section>

    <section id="scoring">
        <h2>Scoring System</h2>
        <h3>Eliminations</h3>
        <ul>
            <li><strong>Master:</strong> 1 Point</li>
            <li><strong>Padawan:</strong> 2 Points</li>
        </ul>
        <h4>Kill Caps</h4>
        <ul>
            <li><strong>Master:</strong> 5 Eliminations</li>
            <li><strong>Padawan:</strong> 8 Eliminations</li>
        </ul>

        <h4>Padawan Damage Bonus</h4>
        <p>
            Padawan damage will be multiplied by .25% (.0025) and rounded down to the nearest whole number.
        </p>

        <ul>
            <li>000-399 damage = 0 points</li>
            <li>400-799 damage = 1 points</li>
            <li>800-1199 damage = 2 points</li>
        </ul>
        <h3>Revives</h3>
        <ul>
            <p>Capped at 2 revives to prevent farming.</p>
            <li><strong>Master:</strong> 1 Point</li>
            <li><strong>Padawan:</strong> 1 Point</li>
        </ul>
        <h3>Placement Points</h3>
        <ul>
            <li><strong>1st place:</strong> 12 points</li>
            <li><strong>2nd place:</strong> 9 points</li>
            <li><strong>3rd place:</strong> 7 points</li>
            <li><strong>4th place:</strong> 5 points</li>
            <li><strong>5th place:</strong> 4 points</li>
            <li><strong>6th place:</strong> 3 points</li>
            <li><strong>7th place:</strong> 3 points</li>
            <li><strong>8th place:</strong> 2 points</li>
            <li><strong>9th place:</strong> 2 points</li>
            <li><strong>10th place:</strong> 2 points</li>
            <li><strong>11th-15th place:</strong> 1 point</li>
            <li><strong>16th-20th place:</strong> 0 points</li>
        </ul>
    </section>

    <footer>
        <p>Sponsored by Darkened Minds Discord Admins</p>
    </footer>
</body>

</html>
<?php
// Close the database connection
if (isset($conn)) {
    $conn->close();
}
?>