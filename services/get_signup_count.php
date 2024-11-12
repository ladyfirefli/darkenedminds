<?php
    
include_once 'database.php';     // Load database configuration

function getSignupCount() {

    $conn = getDatabaseConnection(); // Make $conn available within the function

    // Perform the query to get the signup count
    $sql = "SELECT COUNT(*) AS signup_count FROM registrations";
    $result = $conn->query($sql);

    // Default to 0 if there's an issue
    $signup_count = 0;
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $signup_count = $row['signup_count'];
    }
    
    $conn->close();
    return $signup_count;
}
?>
