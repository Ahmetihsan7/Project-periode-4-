<?php
// =============================================
// Header — geladen op elke pagina
// =============================================
require_once __DIR__ . '/functions.php';

// Bepaal actieve pagina voor nav-highlight
$huidigePagina = basename($_SERVER['PHP_SELF']);
$siteNaam = 'TicketApp';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $metaDescription ?? 'Koop tickets voor de beste shows en evenementen.' ?>">
    <title><?= isset($paginaTitel) ? h($paginaTitel) . ' | ' . $siteNaam : $siteNaam ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="/assets/CSS/style.css">
</head>
<body>

<!-- ===================== NAVIGATIEBALK ===================== -->
<header class="navbar" id="navbar">
    <div class="navbar-inner">

        <!-- Logo -->
        <a href="/index.php" class="navbar-logo">
            <span class="logo-icon">🎭</span>
            <span class="logo-text"><?= $siteNaam ?></span>
        </a>

        <!-- Hamburger (mobiel) -->
        <button class="navbar-toggle" id="navbarToggle" aria-label="Menu openen">
            <span></span><span></span><span></span>
        </button>

        <!-- Nav links -->
        <nav class="navbar-nav" id="navbarNav">
            <a href="/index.php" class="nav-link <?= $huidigePagina === 'index.php' ? 'active' : '' ?>">Home</a>
            <a href="/shows.php" class="nav-link <?= $huidigePagina === 'shows.php' ? 'active' : '' ?>">Shows</a>

            <?php if (isIngelogd()): ?>
                <?php if (heeftRol('admin')): ?>
                    <a href="/admin/dashboard.php" class="nav-link <?= str_contains($huidigePagina, 'admin') ? 'active' : '' ?>">Admin</a>
                <?php elseif (heeftRol('medewerker')): ?>
                    <a href="/employee/dashboard.php" class="nav-link">Dashboard</a>
                <?php else: ?>
                    <a href="/bezoeker/mijn-tickets.php" class="nav-link">Mijn Tickets</a>
                <?php endif; ?>

                <div class="nav-user-menu">
                    <button class="nav-user-btn" id="userMenuBtn">
                        <span class="user-avatar"><?= strtoupper(substr($_SESSION['naam'], 0, 1)) ?></span>
                        <span><?= h($_SESSION['naam']) ?></span>
                        <svg width="12" height="12" viewBox="0 0 12 12"><path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/></svg>
                    </button>
                    <div class="user-dropdown" id="userDropdown">
                        <?php if (heeftRol('bezoeker')): ?>
                            <a href="/bezoeker/profiel.php" class="dropdown-item">👤 Mijn profiel</a>
                            <a href="/bezoeker/mijn-tickets.php" class="dropdown-item">🎟️ Mijn tickets</a>
                            <a href="/bezoeker/account-instellingen.php" class="dropdown-item">⚙️ Instellingen</a>
                            <hr style="border:none; border-top:1px solid var(--border); margin:4px 0;">
                        <?php endif; ?>
                        <a href="/auth/logout.php" class="dropdown-item logout">🚪 Uitloggen</a>
                    </div>
                </div>

            <?php else: ?>
                <a href="/auth/login.php" class="nav-link">Inloggen</a>
                <a href="/auth/register.php" class="nav-btn">Registreren</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<!-- Spacer voor fixed navbar -->
<div class="navbar-spacer"></div>

<!-- Flash berichten -->
<?php if (isset($_SESSION['flash'])): ?>
    <div class="flash-message flash-<?= h($_SESSION['flash']['type']) ?>" id="flashMessage">
        <?= h($_SESSION['flash']['bericht']) ?>
        <button onclick="this.parentElement.remove()" class="flash-close">&times;</button>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<!-- Foutmelding via URL -->
<?php if (isset($_GET['error']) && $_GET['error'] === 'geen_toegang'): ?>
    <div class="flash-message flash-error">
        Je hebt geen toegang tot deze pagina.
        <button onclick="this.parentElement.remove()" class="flash-close">&times;</button>
    </div>
<?php endif; ?>
