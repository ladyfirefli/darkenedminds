<!-- navbar.php -->
<?php
$current_page = basename($_SERVER['PHP_SELF']); // Get the current page filename
// for the tournament submenu, make sure to add new pages as needed
$is_tournament_page = in_array($current_page, ['Tournament.php', 'WinterFortniteTournament.php', 'PadawanTournament.php']);
$is_admin_page = in_array($current_page, ['admin_login.php', 'admin_panels.php', 'admin_dashboard.php', 'scoring_dashboard.php']);
// include navbar.css here so it doesn't have to be included on everypage
echo '<link rel="stylesheet" href="../css/navbar.css">';
?>

<nav id="main-navbar">
    <ul>
        <li>
            <img src="../assets/DMlogowebsite.png" alt="Logo" class="logo-img">
        </li>
        <li><a href="../index.html" class="<?php echo $current_page == 'index.html' ? 'current-page' : ''; ?>">Home</a></li>
        <li class="dropdown">
            <a href="../pages/Tournament.php" class="<?php echo $is_tournament_page ? 'current-page' : ''; ?>">Tournament</a>
            <ul class="submenu">
                <li><a href="../pages/WinterFortniteTournament.php" class="<?php echo $current_page == 'WinterFortniteTournament.php' ? 'current-page' : ''; ?>">Current Tournament</a></li>
                <li><a href="../pages/PadawanTournament.php" class="<?php echo $current_page == 'PadawanTournament.php' ? 'current-page' : ''; ?>">Past Tournaments</a></li>
            </ul>
        </li>
        <li><a href="https://shop.darkenedminds.com">Merch Store</a></li>
        <li class="dropdown">
            <a href="admin_login.php" class="<?php echo $is_admin_page ? 'current-page' : ''; ?>">Admin</a>
            <ul class="submenu">
            <li><a href="admin_panels.php" class="<?php echo $current_page == 'admin_panels.php' ? 'current-page' : ''; ?>">New Panels</a></li>
            <li><a href="admin_dashboard.php" class="<?php echo $current_page == 'admin_dashboard.php' ? 'current-page' : ''; ?>">Padawan Dashboard</a></li>
            <li><a href="scoring_dashboard.php" class="<?php echo $current_page == 'scoring_dashboard.php' ? 'current-page' : ''; ?>">Padawan Scoring</a></li>
            <li><a href="../services/logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>
