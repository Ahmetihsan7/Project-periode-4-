<?php
// =============================================
// Database configuratie — VOORBEELD BESTAND
// =============================================
// Kopieer dit bestand naar config/database.php
// en vul jouw eigen gegevens in.
// Commit NOOIT het echte database.php bestand!
// =============================================

define('DB_HOST',     'localhost');
define('DB_NAME',     'ticketapp');
define('DB_USER',     'root');
define('DB_PASSWORD', 'jouw_wachtwoord_hier');
define('DB_CHARSET',  'utf8mb4');

/**
 * Geeft een PDO-verbinding terug (singleton patroon)
 */
function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );

        $opties = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $opties);
        } catch (PDOException $e) {
            // Toon nooit de echte fout aan de gebruiker in productie
            error_log('Databasefout: ' . $e->getMessage());
            die('<h1>Er is een technisch probleem. Probeer het later opnieuw.</h1>');
        }
    }

    return $pdo;
}
