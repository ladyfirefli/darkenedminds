<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../admin/admin_login.php");
    exit;
}

// Include database connection configuration
$config = include('../../private_html/config.php');

// Function to reset tables and IDs
function resetTeamsAndScores($config) {
    // Connect to the database
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    if ($conn->connect_error) {
        return "Connection failed: " . $conn->connect_error;
    }

    // Start transaction to ensure atomic operation
    $conn->begin_transaction();

    try {
        // Delete all rows and reset IDs in Scores table
        $conn->query("DELETE FROM Scores");
        $conn->query("ALTER TABLE Scores AUTO_INCREMENT = 1");

        // Delete all rows and reset IDs in Teams table
        $conn->query("DELETE FROM Teams");
        $conn->query("ALTER TABLE Teams AUTO_INCREMENT = 1");

        // Commit transaction
        $conn->commit();

        // Close the connection
        $conn->close();

        return "Teams and Scores tables reset successfully.";
    } catch (Exception $e) {
        // Roll back transaction if an error occurs
        $conn->rollback();
        $conn->close();
        return "Error resetting tables: " . $e->getMessage();
    }
}

// Call the function and store the message in a session variable to display on the admin dashboard
$_SESSION['reset_message'] = resetTeamsAndScores($config);

// Redirect back to the admin dashboard page
header("Location: ../admin/admin_dashboard.php");
exit;
?>
