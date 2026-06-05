<?php
/**
 * Uitloggen - Aurora Theater
 * 
 * Beëindigt de huidige gebruikerssessie en redirect naar homepage.
 */

require_once 'includes/functions.php';

// Leeg alle sessie-variabelen
$_SESSION = [];

// Verwijder de sessie cookie indien aanwezig
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Vernietig de sessie
session_destroy();

// Start een nieuwe sessie voor het flashbericht
session_start();
setFlashMessage('success', 'U bent succesvol uitgelogd. Tot ziens bij Aurora Theater!');

// Redirect naar homepage
header('Location: index.php');
exit;
