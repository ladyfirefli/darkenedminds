<?php
session_start();

// Load configuration with hashed password
$config = include('../../private_html/config.php');

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_password = $_POST['password'];

    // Verify the entered password against the hashed password
    if (password_verify($entered_password, $config['admin_hashed_password'])) {
        $_SESSION['logged_in'] = true;
        header("Location: admin_panels.php");
        exit;
    } else {
        $error = "Incorrect password. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/adminstyles.css">
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'admin_navbar.php'; ?> <!-- Include your navigation bar -->


    <header>
        <div class="header-content">
        </div>
    </header>

    <section id="admin-login">
        <div class="form-wrapper">
            <div class="form-container">
                <h2>Admin Access</h2>
                <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
                <form action="admin_login.php" method="POST">
                    <div class="form-group">
                        <label for="password">Enter Admin Password:</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit">Login</button>
                </form>
            </div>
        </div>
    </section>

    <footer>
        <p>Sponsored by Darkened Minds Discord Admins</p>
    </footer>
</body>
</html>
