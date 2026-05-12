<?php
// =============================================
// Algemene helperfuncties
// =============================================

require_once __DIR__ . '/../config/database.php';

// Start sessie als die nog niet actief is
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -----------------------------------------------
// Authenticatie helpers
// -----------------------------------------------

/** Controleer of de gebruiker is ingelogd */
function isIngelogd(): bool {
    return isset($_SESSION['gebruiker_id']);
}

/** Controleer of de ingelogde gebruiker een bepaalde rol heeft */
function heeftRol(string $rol): bool {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === $rol;
}

/** Stuur niet-ingelogde gebruikers door naar login */
function vereisLogin(): void {
    if (!isIngelogd()) {
        header('Location: /auth/login.php');
        exit;
    }
}

/** Stuur gebruikers zonder de juiste rol door */
function vereisRol(string $rol): void {
    vereisLogin();
    if (!heeftRol($rol)) {
        header('Location: /index.php?error=geen_toegang');
        exit;
    }
}

// -----------------------------------------------
// Show-functies
// -----------------------------------------------

/** Haal alle aankomende shows op */
function getAankomendShows(int $limiet = 6): array {
    $db = getDB();
    $stmt = $db->prepare(
        'SELECT * FROM shows WHERE datum >= CURDATE() ORDER BY datum ASC LIMIT :limiet'
    );
    $stmt->bindValue(':limiet', $limiet, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/** Haal één show op via ID */
function getShowById(int $id): array|false {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM shows WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

/** Haal alle shows op (voor admin) */
function getAlleShows(): array {
    $db = getDB();
    return $db->query('SELECT * FROM shows ORDER BY datum DESC')->fetchAll();
}

/** Maak een nieuwe show aan */
function maakShow(array $data): bool {
    $db = getDB();
    $stmt = $db->prepare(
        'INSERT INTO shows (titel, beschrijving, datum, tijd, locatie, capaciteit, prijs)
         VALUES (:titel, :beschrijving, :datum, :tijd, :locatie, :capaciteit, :prijs)'
    );
    return $stmt->execute($data);
}

/** Verwijder een show */
function verwijderShow(int $id): bool {
    $db = getDB();
    $stmt = $db->prepare('DELETE FROM shows WHERE id = :id');
    return $stmt->execute([':id' => $id]);
}

// -----------------------------------------------
// Gebruiker-functies
// -----------------------------------------------

/** Registreer een nieuwe bezoeker */
function registreerGebruiker(string $naam, string $email, string $wachtwoord): bool {
    $db = getDB();
    $hash = password_hash($wachtwoord, PASSWORD_DEFAULT);
    $stmt = $db->prepare(
        'INSERT INTO gebruikers (naam, email, wachtwoord, rol) VALUES (:naam, :email, :wachtwoord, "bezoeker")'
    );
    return $stmt->execute([':naam' => $naam, ':email' => $email, ':wachtwoord' => $hash]);
}

/** Login een gebruiker en sla sessie op */
function loginGebruiker(string $email, string $wachtwoord): bool {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM gebruikers WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $gebruiker = $stmt->fetch();

    if ($gebruiker && password_verify($wachtwoord, $gebruiker['wachtwoord'])) {
        $_SESSION['gebruiker_id'] = $gebruiker['id'];
        $_SESSION['naam']         = $gebruiker['naam'];
        $_SESSION['email']        = $gebruiker['email'];
        $_SESSION['rol']          = $gebruiker['rol'];
        return true;
    }
    return false;
}

/** Haal alle medewerkers op */
function getAlleMedewerkers(): array {
    $db = getDB();
    return $db->query("SELECT * FROM gebruikers WHERE rol = 'medewerker' ORDER BY naam")->fetchAll();
}

/** Maak een medewerker aan */
function maakMedewerker(string $naam, string $email, string $wachtwoord): bool {
    $db = getDB();
    $hash = password_hash($wachtwoord, PASSWORD_DEFAULT);
    $stmt = $db->prepare(
        'INSERT INTO gebruikers (naam, email, wachtwoord, rol) VALUES (:naam, :email, :wachtwoord, "medewerker")'
    );
    return $stmt->execute([':naam' => $naam, ':email' => $email, ':wachtwoord' => $hash]);
}

// -----------------------------------------------
// Ticket-functies
// -----------------------------------------------

/** Koop een ticket */
function koopTicket(int $gebruikerId, int $showId, int $aantal): bool {
    $show = getShowById($showId);
    if (!$show) return false;

    $totaal = $show['prijs'] * $aantal;
    $qr = bin2hex(random_bytes(16));

    $db = getDB();
    $stmt = $db->prepare(
        'INSERT INTO tickets (gebruiker_id, show_id, aantal, totaalprijs, qr_code)
         VALUES (:gid, :sid, :aantal, :totaal, :qr)'
    );
    return $stmt->execute([
        ':gid'    => $gebruikerId,
        ':sid'    => $showId,
        ':aantal' => $aantal,
        ':totaal' => $totaal,
        ':qr'     => $qr,
    ]);
}

/** Haal tickets van een gebruiker op */
function getTicketsVanGebruiker(int $gebruikerId): array {
    $db = getDB();
    $stmt = $db->prepare(
        'SELECT t.*, s.titel, s.datum, s.tijd, s.locatie
         FROM tickets t
         JOIN shows s ON t.show_id = s.id
         WHERE t.gebruiker_id = :gid
         ORDER BY t.gekocht_op DESC'
    );
    $stmt->execute([':gid' => $gebruikerId]);
    return $stmt->fetchAll();
}

/** Valideer een ticket via QR-code (medewerker) */
function valideerTicket(string $qrCode): array|false {
    $db = getDB();
    $stmt = $db->prepare(
        'SELECT t.*, s.titel, s.datum, g.naam AS bezoeker
         FROM tickets t
         JOIN shows s ON t.show_id = s.id
         JOIN gebruikers g ON t.gebruiker_id = g.id
         WHERE t.qr_code = :qr LIMIT 1'
    );
    $stmt->execute([':qr' => $qrCode]);
    return $stmt->fetch();
}

/** Markeer ticket als gebruikt */
function markeerTicketGebruikt(string $qrCode): bool {
    $db = getDB();
    $stmt = $db->prepare("UPDATE tickets SET status = 'gebruikt' WHERE qr_code = :qr");
    return $stmt->execute([':qr' => $qrCode]);
}

// -----------------------------------------------
// Dashboard statistieken (admin)
// -----------------------------------------------

/** Haal dashboard statistieken op */
function getDashboardStats(): array {
    $db = getDB();
    return [
        'totaal_shows'      => $db->query("SELECT COUNT(*) FROM shows")->fetchColumn(),
        'totaal_tickets'    => $db->query("SELECT COUNT(*) FROM tickets")->fetchColumn(),
        'totaal_bezoekers'  => $db->query("SELECT COUNT(*) FROM gebruikers WHERE rol='bezoeker'")->fetchColumn(),
        'totaal_omzet'      => $db->query("SELECT COALESCE(SUM(totaalprijs), 0) FROM tickets WHERE status='actief'")->fetchColumn(),
        'aankomende_shows'  => $db->query("SELECT COUNT(*) FROM shows WHERE datum >= CURDATE()")->fetchColumn(),
    ];
}

// -----------------------------------------------
// Sanitisatie helpers
// -----------------------------------------------

/** Sanitiseer output voor HTML */
function h(string $tekst): string {
    return htmlspecialchars($tekst, ENT_QUOTES, 'UTF-8');
}

/** Formatteer datum naar Nederlands */
function formatDatum(string $datum): string {
    $maanden = ['', 'januari', 'februari', 'maart', 'april', 'mei', 'juni',
                'juli', 'augustus', 'september', 'oktober', 'november', 'december'];
    [$jaar, $maand, $dag] = explode('-', $datum);
    return (int)$dag . ' ' . $maanden[(int)$maand] . ' ' . $jaar;
}

/** Formatteer prijs naar euro */
function formatPrijs(float $prijs): string {
    return '€ ' . number_format($prijs, 2, ',', '.');
}
