<?php
// Include necessary files and functions
$config = include('../../private_html/config.php');

include '../services/tournament_functions.php';
include_once '../services/database.php';

$conn = getTourneyDatabaseConnection();

// Fetch dropdown data
$activeTournaments = getActiveTournaments($conn);

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
                        Gear up for the ultimate frosty showdown! Join us for an action-packed Duos Zero Build Tournament, randomely assigned duos team up to outwit and outlast the competition. Over the course of 4 thrilling rounds, participants will earn points for eliminations, accuracy, damage, headshots, and assists
                    </p>
                    <p>
                        Prizes await the top teams, including cash rewards, a tournament themed shirt, and exclusive tournament themed stickers! Whether you're a seasoned Fortnite veteran or just getting started, this tournament is designed to celebrate creativity, skill, and teamwork.
                    </p>
                    <p>
                        So join us in this epic winter battle! Remember, stats must be public, and all are welcome to compete for glory in the snow. Sign up now and don’t miss the chance to show off your Fortnite skills in the Winter Classic!
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
                    <p>Entry fee per person will be $5.</p>
                    <p>To register, fill out the form below.</p>
                    <p>Please ensure your stats are public in Fortnite so we can attempt to balance teams!</p>
                    <div class="form-wrapper">
                        <form id="registerForm" action="../services/register.php" method="POST" enctype="multipart/form-data" class="registration-form" onsubmit="handleRegistration(event)">
                            <div class="form-group">
                                <?php if (count($activeTournaments) === 1): ?>
                                    <!-- Display single tournament as text -->
                                    <label for="tournament">Tournament:</label>
                                    <input type="hidden" name="tournament_id" value="<?php echo $activeTournaments[0]['tournament_id']; ?>">
                                    <p>
                                        <?php
                                        echo htmlspecialchars($activeTournaments[0]['tournament_name'])
                                            . " (Registration ends: "
                                            . htmlspecialchars($activeTournaments[0]['registration_end_date'])
                                            . ")";
                                        ?>
                                    </p>
                                <?php elseif (count($activeTournaments) > 1): ?>
                                    <!-- Display tournaments as a dropdown -->
                                    <label for="tournament">Select a Tournament:</label>
                                    <select id="tournament" name="tournament_id" required>
                                        <option value="">Select a tournament</option>
                                        <?php foreach ($activeTournaments as $tournament): ?>
                                            <option value="<?php echo $tournament['tournament_id']; ?>">
                                                <?php
                                                echo htmlspecialchars($tournament['tournament_name'])
                                                    . " (Registration ends: "
                                                    . htmlspecialchars($tournament['registration_end_date'])
                                                    . ")";
                                                ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <!-- No active tournaments -->
                                    <p>No active tournaments available for registration.</p>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="discord_name">Discord Name:</label>
                                <input type="text" id="discord_name" name="discord_name" onblur="fetchDiscordInfo()" required>
                                <div id="discord-info-message"></div> <!-- Display validation or info here -->
                            </div>
                            <div class="form-group">
                                <label for="gamertag">GamerTag:</label>
                                <input type="text" id="gamertag" name="gamertag" onblur="markGamertagVisited()" required>
                                <div id="fortnite-info-message"></div> <!-- Display Fortnite stats info here -->
                            </div>
                            <div class="form-group">
                                <label for="platform">Select Your Platform:</label>
                                <div>
                                    <input type="radio" id="pc" name="platform" value="pc" onclick="markPlatformVisited()" required>
                                    <label for="pc">PC</label>
                                </div>
                                <div>
                                    <input type="radio" id="xbox" name="platform" value="xbox" onclick="markPlatformVisited()" required>
                                    <label for="xbox">Xbox</label>
                                </div>
                                <div>
                                    <input type="radio" id="psn" name="platform" value="psn" onclick="markPlatformVisited()" required>
                                    <label for="psn">PlayStation</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email">Email (optional):</label>
                                <input type="email" name="email">
                            </div>
                            <button id="registerButton" type="submit" disabled>Register</button>
                        </form>
                    </div>
                    <div id="registrationMessage"></div> <!-- Placeholder for the message -->
                </div>
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
                                <img src="../assets/WinterClassicT.png" alt="Custom T-Shirt" class="prize-img">
                            </div>
                            <div class="prize-content">
                                <strong>2nd Place:</strong> Custom T-Shirt
                            </div>
                        </li>
                        <li>
                            <div class="prize-image">
                                <img src="../assets/WinterClassicSticker.png" alt="Stickers" class="prize-img">
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
                <h2>Tournament Structure</h2>
                <div class="card-content">
                    <ul>
                        <li><strong>Format:</strong>
                            <ul>
                                <li><strong>Mode:</strong> Duos Zero Build</li>
                                <li><strong>Rounds:</strong> 4 matches to balance competition and creativity</li>
                            </ul>
                        </li>
                        <li><strong>Teams:</strong>
                            <ul>
                                <li>Random pairings</li>
                            </ul>
                        </li>
                        <li><strong>Score submission:</strong>
                            <ul>
                                <li>Match Summary Screenshot posted to  <strong>#matchsummary</strong> channel under "Tournaments". </li>
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
            <h2>Tournament Rules & Scoring System</h2>
            <div class="card-content">
                <p>Each player of the team MUST post a picture or screenshot ONLY (we will not accept text) of their match summary at the end of each round to ensure all points are accounted for. Your username must be visible in the screenshot. All screenshots should be posted in the <strong>#matchsummary</strong> channel under "Tournaments". </p>
                <p> <strong>IF YOU DO NOT SEND A SCREENSHOT OF YOUR MATCH SUMMARY BEFORE YOU EXIT, WE WILL HAVE TO GIVE YOU ZERO POINTS AT THE END OF THAT ROUND.</strong></p>
                <h3>Scoring Per Round</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Eliminations</td>
                            <td>2 points each</td>
                        </tr>
                        <tr>
                            <td>Assists</td>
                            <td>1 point each</td>
                        </tr>
                        <tr>
                            <td>Accuracy 0%-10%</td>
                            <td>0 points</td>
                        </tr>
                        <tr>
                            <td>Accuracy 11%-30%</td>
                            <td>1 point</td>
                        </tr>
                        <tr>
                            <td>Accuracy 31%-60%</td>
                            <td>2 points</td>
                        </tr>
                        <tr>
                            <td>Accuracy 61%-100%</td>
                            <td>3 points</td>
                        </tr>
                        <tr>
                            <td>Damage to Players 0-500</td>
                            <td>1 point</td>
                        </tr>
                        <tr>
                            <td>Damage to Players 501-1000</td>
                            <td>2 points</td>
                        </tr>
                        <tr>
                            <td>Damage to Players 1001-1500</td>
                            <td>3 points</td>
                        </tr>
                        <tr>
                            <td>Damage to Players 1501-2000</td>
                            <td>4 points</td>
                        </tr>
                        <tr>
                            <td>Damage to Players 2001+</td>
                            <td>5 points</td>
                        </tr>
                        <tr>
                            <td>Headshots 1-3</td>
                            <td>1 point</td>
                        </tr>
                        <tr>
                            <td>Headshots 4-6</td>
                            <td>2 points</td>
                        </tr>
                        <tr>
                            <td>Headshots 7-9</td>
                            <td>3 points</td>
                        </tr>
                        <tr>
                            <td>Headshots 10+</td>
                            <td>4 points</td>
                        </tr>
                        <tr>
                            <td>1st Place</td>
                            <td>5 points</td>
                        </tr>
                        <tr>
                            <td>2nd Place</td>
                            <td>3 points</td>
                        </tr>
                        <tr>
                            <td>3rd Place</td>
                            <td>2 points</td>
                        </tr>
                    </tbody>
                </table>
                <h3>Tournament Start Details</h3>
                <p>The tournament start time will be <strong>12 CST, 1 EST, 6 UTC</strong>. The first 30 minutes will be registration. It will take some time to get everyone in the lobby, so please be patient with us!</p>
                <p>Please join the <strong>"All Participants - Start"</strong> voice channel under Tournaments in Darkened Minds at start time. We will then give everyone instructions on how to join the custom game. After, we will assign you and your teammate a voice channel number under the tournament section of Darkened Minds to use during the tournament.</p>
            </div>
        </div>
    </div>
</section>



    <footer>
    <p>Sponsored by <a href="../admin/admin_panels.php" target="_blank" style="color: inherit; text-decoration: none;">Darkened Minds Discord Admins</a></p>
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