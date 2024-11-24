<?php
// Include the tournament functions
require_once 'tournament_functions.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_once '../../private_html/config.php';
    include_once 'database.php';

    $conn = getTourneyDatabaseConnection();
    // Use the createTournament function to handle form submission
    $message = createTournament($conn, $_POST);

    // Display a success or error message
    echo $message;

}
