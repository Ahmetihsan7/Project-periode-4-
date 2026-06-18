<?php
/**
 * Header - Aurora Theater
 * 
 * Dit bestand start de HTML structuur, importeert de stylesheets
 * en include de navigatiebalk.
 */

// Laad de configuratie en hulpfuncties met absolute paden
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

// Dynamische paginatitel bepalen
$site_title = getSetting('theater_naam', 'Aurora Theater');
$page_title = isset($page_title) ? $page_title . " | " . $site_title : $site_title;
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Welkom bij Aurora Theater. Reserveer eenvoudig tickets voor de leukste theater- en bioscoopvoorstellingen. Ervaar de magie van live podiumkunsten.">
    <title><?php echo $page_title; ?></title>
    
    <!-- Google Fonts: Inter voor moderne strakke typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    
    <!-- Hoofd stylesheet -->
    <link rel="stylesheet" href="<?php echo getRootUrl(); ?>assets/css/style.css?v=1.0.2">
</head>
<body>

    <header class="site-header">
        <?php include __DIR__ . '/navbar.php'; ?>
    </header>

    <!-- Flash message container voor succes- en foutmeldingen (Happy/Unhappy scenario) -->
    <div class="container message-container">
        <?php displayFlashMessage(); ?>
    </div>
