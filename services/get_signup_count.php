<?php
function getSignupCount() {
    // Load database configuration
    $config = include('../../private_html/config.php');

    // Establish a database connection
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    // Check for a successful connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Perform the query to get the signup count
    $sql = "SELECT COUNT(*) AS signup_count FROM registrations";
    $result = $conn->query($sql);

    // Default to 0 if there's an issue
    $signup_count = 0;
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $signup_count = $row['signup_count'];
    }

    // Close the database connection
    $conn->close();

    return $signup_count;
}
?>
