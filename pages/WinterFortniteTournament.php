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
    <title>Fortnite Winter Classic Tournament</title>
    <link rel="stylesheet" href="../css/tourneystyles.css">
</head>

<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <header>
        <div class="header-content">
        </div>
    </header>

    <section id="about">
        <div class="section-container">
            <div class="card">
                <h2>About the Tournament</h2>
                <div class="card-content">
                    <p>
                        Gear up for the ultimate frosty showdown! Join us for an action-packed Duos Zero Build Tournament, randomely assigned duos team up to outwit and outlast the competition. Over the course of 4 thrilling rounds, participants will earn points for eliminations, placements, and teamwork, with special bonuses for completing winter-themed challenges like snowy POI landings, icy weapon eliminations, and creative builds.
                    </p>
                    <p>
                        Prizes await the top teams, including cash rewards, a tournament themed shirt, and exclusive tournament themed stickers! Whether you're a seasoned Fortnite veteran or just getting started, this tournament is designed to celebrate creativity, skill, and teamwork.
                    </p>
                    <p>
                        So join us in this epic winter battle! Remember, stats must be public, and all are welcome to compete for glory in the snow. Sign up now and don’t miss the chance to show off your Fortnite skills in the Winter Classic!
                    </p>
                    <p>
                        Winter-themed activities will score extra points!
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="registration">
        <div class="section-container">
            <div class="card">
                <h2>Registration</h2>
                <div class="card-content">
                    <p>To register, fill out the form below.</p>
                    <p>Please ensure your stats are public in Fortnite so we can attempt to balance teams!</p>
                    <div class="form-wrapper">
                        <form action="../services/register.php" method="POST" enctype="multipart/form-data" class="registration-form">
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
            </div>
        </div>
    </section>

    <section id="signup-count">
        <div class="section-container">
            <div class="card">
                <h2>Current Signups for the Tournament</h2>
                <div class="card-content">
                    <p><strong><?php echo htmlspecialchars($signup_count); ?></strong> players have signed up!</p>
                </div>
            </div>
        </div>
    </section>

    <section id="prizes">
        <div class="section-container">
            <div class="card">
                <h2>Prizes</h2>
                <div class="card-content">
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
                </div>
            </div>
        </div>
    </section>

    <section id="Rules">
        <div class="section-container">
            <div class="card">
                <h2>Rules</h2>
                <div class="card-content">
                    <h3>Entry Fee</h3>
                    <p>Entry fee per person will be $2.</p>
                    <h3>Join us!</h3>
                    <p><a href="https://discord.gg/FqqZ6XTNDW">Join our Discord</a></p>
                    <h2>Tournament Structure</h2>
                    <ul>
                        <li><strong>Format:</strong>
                            <ul>
                                <li><strong>Mode:</strong> Duos Zero Build</li>
                                <li><strong>Rounds:</strong> 4 matches to balance competition and creativity</li>
                            </ul>
                        </li>
                        <li><strong>Teams:</strong>
                            <ul>
                                <li>Random pairings or pre-registered duos</li>
                                <li>Option to include a “mentor and novice” pairing for inclusivity</li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section id="scoring">
        <div class="section-container">
            <div class="card">
                <h2>Scoring System</h2>
                <div class="card-content">
                    <table>
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Duo Elimination</td>
                                <td>10 points per team elimination</td>
                            </tr>
                            <tr>
                                <td>Placement (per match)</td>
                                <td>1st: 50 pts, 2nd: 40 pts, 3rd: 30 pts, etc.</td>
                            </tr>
                            <tr>
                                <td>Winter-Themed Challenges</td>
                                <td>See below for specific bonuses</td>
                            </tr>
                            <tr>
                                <td>Revive Teammate Bonus</td>
                                <td>5 points per revive</td>
                            </tr>
                            <tr>
                                <td>Assist Bonus</td>
                                <td>5 points per confirmed assist</td>
                            </tr>
                        </tbody>
                    </table>
                    <hr>
                    <h2>Winter-Themed Challenges (Creative Bonuses)</h2>
                    <ul>
                        <li><strong>Snowy POI Drop (10 Points):</strong> Teams score points for landing at a snowy or winter-themed POI. (Verify through streaming or honest reporting.)</li>
                        <li><strong>Icy Arsenal Bonus (15 Points):</strong> Use snowy-themed weapons or items for an elimination (e.g., Snowball Launcher, Chiller Grenades).</li>
                        <li><strong>Creative Builds (10 Points):</strong> Even in Zero Build mode, players can earn points for creative use of cover (e.g., hiding behind vehicles, stacking items). Bonus if it resembles a winter shape like an igloo.</li>
                        <li><strong>Festive Gesture Bonus (5 Points):</strong> Perform a festive emote after eliminating a team or reviving your teammate.</li>
                        <li><strong>Storm Survival Bonus (20 Points):</strong> Survive at least 3 storm phases without leaving a snowy area.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>



    <footer>
        <p>Sponsored by Darkened Minds Discord Admins</p>
    </footer>
    <script src="../js/scripts.js"></script>
</body>

</html>
<?php
// Close the database connection
if (isset($conn)) {
    $conn->close();
}
?>