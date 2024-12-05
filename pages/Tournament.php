<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tournament Hub - Darkened Minds</title>
    <link rel="stylesheet" href="../css/navbar.css"> <!-- Include your navbar CSS -->
    <link rel="stylesheet" href="../css/styles.css"> <!-- Overall styles for this site -->
    <link rel="stylesheet" href="../css/tournament.css"> <!-- Specific styles for this page -->
</head>
<body>
    <?php include 'navbar.php'; ?> <!-- Include your navigation bar -->

    <header class="tournament-header">
        <div class="header-content">
            <h1>Darkened Minds Tournament Hub</h1>
        </div>
    </header>

    <section class="current-tournament">
        <h2>Current Tournament</h2>
        <p>Compete. Collaborate. Conquer.</p>
        <div class="tournament-card">
            <h3><a href="WinterFortniteTournament.php">Winter Classic Fortnite Tournament</a></h3>
            <p>
                Ready to prove your skills? Join our current Fortnite tournament and battle for glory.<br>
                <strong>Date:</strong> February 2nd 2025<br>
                <strong>Registration:</strong> Ends February 1st<br>
                <strong>Entry Fee:</strong> $5
            </p>
            <a href="WinterFortniteTournament.php" class="button">View Details</a>
        </div>
    </section>

    <section class="past-tournaments">
        <h2>Past Tournaments</h2>
        <div class="tournament-grid">
            <div class="tournament-card">
                <h3><a href="past_tournaments.php?tournament_id=1">Padawan Masters - October 2024</a></h3>
                <p>Highlights and stats from our October showdown.</p>
                <a href="past_tournaments.php?tournament_id=1" class="button">View Results</a>
            </div>
            <!-- Add more past tournaments as needed -->
        </div>
    </section>

    <footer>
        <p>Sponsored by the Darkened Minds Discord Admins | <a href="https://discord.gg/yourinvite">Join Our Community</a></p>
    </footer>
</body>
</html>
