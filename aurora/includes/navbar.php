<?php
/**
 * Navigatiebalk - Aurora Theater
 * 
 * Dit bestand renders de responsive navigatiebalk. Het wordt geïncludeerd in de header.
 */

// Bepaal de huidige bestandsnaam om actieve menu-items te markeren
$current_page = basename($_SERVER['PHP_SELF']);
$root_url = getRootUrl();
?>
<nav class="navbar container">
    <a href="<?php echo $root_url; ?>index.php" class="logo">
        <span class="logo-accent">A</span>urora
    </a>

    <!-- Hamburger menu knop voor mobiel -->
    <button class="menu-toggle" id="mobile-menu-btn" aria-label="Open menu">
        <span class="bar"></span>
        <span class="bar"></span>
        <span class="bar"></span>
    </button>

    <div class="nav-menu" id="nav-menu">
        <ul class="nav-links">
            <li>
                <a href="<?php echo $root_url; ?>index.php" class="<?php echo ($current_page === 'index.php') ? 'active' : ''; ?>">Home</a>
            </li>
            <li>
                <a href="<?php echo $root_url; ?>voorstellingen.php" class="<?php echo ($current_page === 'voorstellingen.php') ? 'active' : ''; ?>">Voorstellingen</a>
            </li>
            <li>
                <a href="<?php echo $root_url; ?>tickets.php" class="<?php echo ($current_page === 'tickets.php') ? 'active' : ''; ?>">Tickets</a>
            </li>
            <li>
                <a href="<?php echo $root_url; ?>index.php#contact">Contact</a>
            </li>
        </ul>

        <div class="auth-buttons">
            <?php if (isLoggedIn()): ?>
                <?php if (hasRole(['admin', 'medewerker'])): ?>
                    <a href="<?php echo $root_url; ?>admin/dashboard.php" class="btn-dashboard">
                        <span>Dashboard</span>
                    </a>
                <?php else: ?>
                    <span class="welcome-user">Welkom, <?php echo sanitize($_SESSION['user_name']); ?></span>
                <?php endif; ?>
                <a href="<?php echo $root_url; ?>logout.php" class="btn-secondary btn-nav">Uitloggen</a>
            <?php else: ?>
                <a href="<?php echo $root_url; ?>login.php" class="btn-login <?php echo ($current_page === 'login.php') ? 'active-link' : ''; ?>">Inloggen</a>
                <a href="<?php echo $root_url; ?>register.php" class="btn-primary btn-nav">Registreren</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
