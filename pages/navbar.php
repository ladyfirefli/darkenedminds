<!-- navbar.php -->
<?php
$current_page = basename($_SERVER['PHP_SELF']); // Get the current page filename
// for the tournament submenu, make sure to add new pages as needed
$is_tournament_page = in_array($current_page, ['Tournament.php', 'WinterFortniteTournament.php', 'PadawanTournament.php']);
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
            <a href="Tournament.php" class="<?php echo $is_tournament_page ? 'current-page' : ''; ?>">Tournament</a>
            <ul class="submenu">
                <li><a href="WinterFortniteTournament.php" class="<?php echo $current_page == 'WinterFortniteTournament.php' ? 'current-page' : ''; ?>">Current Tournament</a></li>
                <li><a href="PadawanTournament.php" class="<?php echo $current_page == 'PadawanTournament.php' ? 'current-page' : ''; ?>">Past Tournaments</a></li>
            </ul>
        </li>
        <li><a href="https://shop.darkenedminds.com">Merch Store</a></li>
    </ul>
</nav>
