<?php
/**
 * Admin Header - Aurora Theater
 * 
 * Beveiligt de admin panelen, start de HTML structuur en include de sidebar.
 */

// Bepaal absolute paden en laad benodigdheden
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

// Controleer of de gebruiker toegang heeft (alleen admin en medewerkers)
checkAccess(['admin', 'medewerker']);

$current_admin_page = basename($_SERVER['PHP_SELF']);
$current_uri = $_SERVER['PHP_SELF'];
$root_url = getRootUrl();

// Bepaal de actieve secties
$is_dashboard = ($current_admin_page === 'dashboard.php');
$is_accounts = ($current_admin_page === 'accounts.php' || strpos($current_uri, '/accounts/') !== false);
$is_medewerkers = ($current_admin_page === 'medewerkers.php' || strpos($current_uri, '/medewerkers/') !== false);
$is_voorstellingen = ($current_admin_page === 'voorstellingen.php' || strpos($current_uri, '/voorstellingen/') !== false);
$is_tickets = ($current_admin_page === 'tickets.php' || strpos($current_uri, '/tickets/') !== false);
$is_meldingen = ($current_admin_page === 'meldingen.php' || strpos($current_uri, '/meldingen/') !== false);
$is_instellingen = ($current_admin_page === 'instellingen.php' || strpos($current_uri, '/instellingen/') !== false);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Dashboard | Aurora Theater</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?php echo $root_url; ?>assets/css/style.css?v=1.0.2">
    <link rel="stylesheet" href="<?php echo $root_url; ?>assets/css/admin.css?v=1.0.2">
</head>
<body>

    <div class="admin-layout">
        
        <!-- Sidebar Menu -->
        <aside class="admin-sidebar" id="admin-sidebar">
            <div class="sidebar-header">
                <a href="<?php echo $root_url; ?>index.php" class="logo">
                    <span class="logo-accent">A</span>urora
                </a>
            </div>
            
            <nav class="sidebar-menu">
                <ul>
                    <li class="<?php echo $is_dashboard ? 'active' : ''; ?>">
                        <a href="<?php echo $root_url; ?>admin/dashboard.php">
                            <span class="icon">📊</span>Dashboard
                        </a>
                    </li>
                    
                    <!-- Alleen Admin mag Accounts en Medewerkers beheren -->
                    <?php if (hasRole('admin')): ?>
                        <li class="<?php echo $is_accounts ? 'active' : ''; ?>">
                            <a href="<?php echo $root_url; ?>admin/accounts.php">
                                <span class="icon">👥</span>Accounts
                            </a>
                        </li>
                        <li class="<?php echo $is_medewerkers ? 'active' : ''; ?>">
                            <a href="<?php echo $root_url; ?>admin/medewerkers.php">
                                <span class="icon">💼</span>Medewerkers
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <li class="<?php echo $is_voorstellingen ? 'active' : ''; ?>">
                        <a href="<?php echo $root_url; ?>admin/voorstellingen.php">
                            <span class="icon">🎭</span>Voorstellingen
                        </a>
                    </li>
                    
                    <li class="<?php echo $is_tickets ? 'active' : ''; ?>">
                        <a href="<?php echo $root_url; ?>admin/tickets.php">
                            <span class="icon">🎟️</span>Tickets
                        </a>
                    </li>
                    
                    <li class="<?php echo $is_meldingen ? 'active' : ''; ?>">
                        <a href="<?php echo $root_url; ?>admin/meldingen.php">
                            <span class="icon">✉️</span>Meldingen
                        </a>
                    </li>
                    
                    <!-- Alleen Admin mag instellingen wijzigen -->
                    <?php if (hasRole('admin')): ?>
                        <li class="<?php echo $is_instellingen ? 'active' : ''; ?>">
                            <a href="<?php echo $root_url; ?>admin/instellingen.php">
                                <span class="icon">⚙️</span>Instellingen
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <li style="border-top: 1px solid var(--admin-border); margin-top: 15px;">
                        <a href="<?php echo $root_url; ?>index.php">
                            <span class="icon">🌐</span>Naar Website
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?php echo $root_url; ?>logout.php">
                            <span class="icon">🚪</span>Uitloggen
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <p>CMS v1.0.0</p>
            </div>
        </aside>
        
        <!-- Rechter content wrapper -->
        <div class="admin-content">
            
            <!-- Top Navigatiebalk -->
            <header class="admin-topnav">
                <div class="topnav-left">
                    <button class="sidebar-toggle-btn" id="sidebar-toggle" aria-label="Toggle menu">☰</button>
                    <h1 class="topnav-title">
                        <?php 
                        // Bepaal de titel op basis van de actieve sectie en de pagina
                        if ($is_dashboard) echo 'Dashboard Overzicht';
                        elseif ($is_accounts) echo strpos($current_uri, 'create.php') !== false ? 'Nieuw Account Toevoegen' : 'Accountbeheer';
                        elseif ($is_medewerkers) echo strpos($current_uri, 'create.php') !== false ? 'Nieuwe Medewerker Toevoegen' : 'Medewerkersbeheer';
                        elseif ($is_voorstellingen) echo strpos($current_uri, 'create.php') !== false ? 'Nieuwe Voorstelling Toevoegen' : 'Voorstellingen Beheer';
                        elseif ($is_tickets) echo strpos($current_uri, 'create.php') !== false ? 'Nieuw Ticket Toevoegen' : 'Ticketverkoop & Boekingen';
                        elseif ($is_meldingen) echo strpos($current_uri, 'create.php') !== false ? 'Nieuwe Melding Toevoegen' : 'Contact Meldingen';
                        elseif ($is_instellingen) echo 'Systeem Instellingen';
                        else echo 'Admin Panel';
                        ?>
                    </h1>
                </div>
                
                <div class="topnav-right">
                    <span class="badge badge-gelezen" style="margin-right: 15px; padding: 6px 14px; font-size: 0.8rem; background-color: rgba(255, 193, 7, 0.15); color: #ffc107; border: 1px solid rgba(255, 193, 7, 0.3); border-radius: 4px; font-weight: 600;">👁️ CMS Alleen Lezen</span>
                    <div class="user-profile">
                        <span><?php echo sanitize($_SESSION['user_name']); ?></span>
                        <span class="user-badge"><?php echo sanitize($_SESSION['user_role']); ?></span>
                    </div>
                </div>
            </header>
            
            <!-- Start van de pagina inhoud -->
            <main class="admin-body">
                <!-- Meldingen container voor admin succes/error alerts -->
                <?php displayFlashMessage(); ?>
