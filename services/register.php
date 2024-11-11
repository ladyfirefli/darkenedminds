<?php
$config = include('../../private_html/config.php');

// Database connection
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$gamertag = $_POST['gamertag'];
$email = !empty($_POST['email']) ? $_POST['email'] : NULL;
$role = $_POST['role'];
$partner = !empty($_POST['partner']) ? $_POST['partner'] : NULL;

// Handle file upload and convert to BLOB
$screenshot = file_get_contents($_FILES["screenshot"]["tmp_name"]);
$screenshotData = base64_encode($screenshot);
$screenshotType = mime_content_type($_FILES["screenshot"]["tmp_name"]);
$screenshotName = basename($_FILES["screenshot"]["name"]);

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO registrations (gamertag, email, role, partner, screenshot) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $gamertag, $email, $role, $partner, $screenshot);

if ($stmt->execute()) {
    // Send confirmation email with attachment
    $to = "auto-registration@darkenedminds.com";
    $subject = "New Tournament Registration";
    $message = "
        A new participant has registered for the tournament:
        
        GamerTag: $gamertag
        " . ($email ? "Email: $email\n" : "") . "
        Role: $role
        Partner's GamerTag: " . ($partner ?? "None") . "

        Screenshot is attached below.
    ";

    // Boundary for separating different parts of the email
    $boundary = md5(uniqid(time()));

    // Headers for email with attachment
    $headers = "From: no-reply@darkenedminds.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

    // Plain text message
    $body = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $body .= $message . "\r\n";

    // Attachment part
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: $screenshotType; name=\"$screenshotName\"\r\n";
    $body .= "Content-Disposition: attachment; filename=\"$screenshotName\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= $screenshotData . "\r\n";
    $body .= "--$boundary--";

    // Send the email
    mail($to, $subject, $body, $headers);

    // Redirect to confirmation page
    header("Location: ../view/regresponse.html");
    exit;
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>