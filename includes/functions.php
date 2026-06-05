<?php
/**
 * Helper Functies - Aurora Theater
 * 
 * Dit bestand bevat herbruikbare functies voor authenticatie,
 * validatie, beveiliging en flash-meldingen.
 */

// Start sessie veilig indien deze nog niet gestart is
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Maak input veilig tegen XSS (Cross-Site Scripting)
 * 
 * @param string $data De invoerwaarde
 * @return string De geschoonde waarde
 */
function sanitize($data) {
    if ($data === null) return '';
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Controleer of de gebruiker is ingelogd
 * 
 * @return bool True als ingelogd, anders false
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Haal de rol van de ingelogde gebruiker op
 * 
 * @return string|null De rol (klant, medewerker, admin) of null
 */
function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Controleer of de ingelogde gebruiker een specifieke rol heeft
 * 
 * @param array|string $allowedRoles Toegestane rol(len)
 * @return bool True als toegestaan, anders false
 */
function hasRole($allowedRoles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $role = getUserRole();
    if (is_array($allowedRoles)) {
        return in_array($role, $allowedRoles);
    }
    
    return $role === $allowedRoles;
}

/**
 * Beveilig een pagina voor specifieke rollen.
 * Indien niet geautoriseerd, redirect naar login of index.
 * 
 * @param array|string $allowedRoles Toegestane rol(len)
 */
function checkAccess($allowedRoles) {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'U moet eerst inloggen om deze pagina te bezoeken.');
        header('Location: ' . getRootUrl() . 'login.php');
        exit;
    }
    
    if (!hasRole($allowedRoles)) {
        setFlashMessage('error', 'U heeft geen toegang tot deze pagina.');
        header('Location: ' . getRootUrl() . 'index.php');
        exit;
    }
}

/**
 * Bepaal de basismap (root URL) dynamisch
 * Dit helpt met redirects vanaf admin-submappen.
 * 
 * @return string Root URL path
 */
function getRootUrl() {
    // Bepaal de root map dynamisch
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    
    // Zorg ervoor dat het eindigt met een slash
    if (substr($script_dir, -1) !== '/') {
        $script_dir .= '/';
    }
    
    // Als we in de admin-map zitten, neem dan de bovenliggende map als root
    if (strpos($script_dir, '/admin/') !== false) {
        $script_dir = str_replace('/admin/', '/', $script_dir);
    }
    
    return $script_dir;
}

/**
 * Sla een flash-bericht op in de sessie om eenmalig te tonen na een redirect
 * 
 * @param string $type De soort melding ('success' of 'error')
 * @param string $message Het te tonen bericht
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Toon de flash-melding indien aanwezig en verwijder deze uit de sessie
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        $typeClass = $flash['type'] === 'success' ? 'alert-success' : 'alert-error';
        $icon = $flash['type'] === 'success' ? '✓' : '✗';
        
        echo "<div class='alert {$typeClass}' id='flash-alert'>
                <span class='alert-icon'>{$icon}</span>
                <span class='alert-text'>" . sanitize($flash['message']) . "</span>
                <button class='alert-close' onclick=\"document.getElementById('flash-alert').style.display='none'\">&times;</button>
              </div>";
              
        unset($_SESSION['flash_message']);
    }
}

/**
 * Haal een dynamische instelling op uit de database.
 * Als de database leeg of onbereikbaar is, wordt de standaardwaarde gebruikt.
 * Dit garandeert dat de site werkt zonder database-errors.
 * 
 * @param string $sleutel De instellingssleutel
 * @param string $default Standaardwaarde indien niet gevonden
 * @return string De instellingswaarde
 */
function getSetting($sleutel, $default = '') {
    global $conn;
    
    // Fallback als $conn niet geactiveerd is
    if (!isset($conn) || !$conn) {
        return $default;
    }
    
    // Voorkom errors als de tabel niet bestaat of leeg is
    $waarde = $default;
    
    $query = "SELECT waarde FROM instellingen WHERE sleutel = ? LIMIT 1";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('s', $sleutel);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result && $row = $result->fetch_assoc()) {
                $waarde = $row['waarde'];
            }
        }
        $stmt->close();
    }
    
    return $waarde;
}

/**
 * Formatteer een datum en tijd naar Nederlands formaat
 * 
 * @param string $datetime SQL datetime string
 * @param bool $showTime Of ook de tijd getoond moet worden
 * @return string Geformatteerde datum
 */
function formatteerDatum($datetime, $showTime = true) {
    if (empty($datetime)) return '';
    $timestamp = strtotime($datetime);
    
    $maanden = [
        1 => 'januari', 'februari', 'maart', 'april', 'mei', 'juni',
        'juli', 'augustus', 'september', 'oktober', 'november', 'december'
    ];
    
    $dag = date('j', $timestamp);
    $maandNummer = date('n', $timestamp);
    $jaar = date('Y', $timestamp);
    
    $geformatteerd = "{$dag} {$maanden[$maandNummer]} {$jaar}";
    
    if ($showTime) {
        $tijd = date('H:i', $timestamp);
        $geformatteerd .= " om {$tijd} uur";
    }
    
    return $geformatteerd;
}

/**
 * Formatteer een bedrag naar euro-valuta
 * 
 * @param float $bedrag Het bedrag
 * @return string Geformatteerd geldbedrag
 */
function formatteerGeld($bedrag) {
    return '€ ' . number_format((float)$bedrag, 2, ',', '.');
}
