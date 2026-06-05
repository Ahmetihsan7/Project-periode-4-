<?php
/**
 * Instellingenbeheer - Aurora Theater Admin
 * 
 * Beheert de algemene theater-instellingen.
 * Alleen toegankelijk voor admins.
 */

// Laad db en functies om redirects te kunnen verwerken vóór HTML output
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Controleer toegang vóór redirect
checkAccess(['admin', 'medewerker']);

// Extra veiligheid check: Alleen admins mogen hier komen
if (!hasRole('admin')) {
    setFlashMessage('error', 'Toegang geweigerd: Alleen beheerders mogen instellingen beheren.');
    header('Location: dashboard.php');
    exit;
}

// Blokkeer POST verzoeken
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_settings') {
    setFlashMessage('error', 'Fout: Systeeminstellingen wijzigen is momenteel uitgeschakeld.');
    header('Location: instellingen.php');
    exit;
}

// Inclusief header (HTML start)
include '../includes/admin_header.php';

// Haal alle instellingen op uit de database voor het formulier
$settings_data = [];
$res = $conn->query("SELECT sleutel, waarde, beschrijving FROM instellingen");
if ($res && $res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        $settings_data[$row['sleutel']] = [
            'waarde' => $row['waarde'],
            'beschrijving' => $row['beschrijving']
        ];
    }
}

// Fallback waarden indien database leeg is (garandeert werking)
$get_val = function($key, $default = '') use ($settings_data) {
    return $settings_data[$key]['waarde'] ?? $default;
};

$get_desc = function($key, $default = '') use ($settings_data) {
    return $settings_data[$key]['beschrijving'] ?? $default;
};
?>

<div class="admin-card" style="max-width: 800px;">
    <h3>Systeem & Website Instellingen</h3>
    <p style="color: var(--admin-text-muted); margin-bottom: 30px;">
        Hier beheert u de dynamische instellingen die op de frontend van de website worden weergegeven en gebruikt voor boekingsberekeningen.
    </p>
    
    <form action="instellingen.php" method="POST" class="settings-list">
        <input type="hidden" name="action" value="update_settings">
        
        <!-- Algemene gegevens -->
        <div class="settings-item">
            <h4 style="color: var(--admin-primary); margin-bottom: 15px;">1. Algemene Informatie</h4>
            <div class="form-row">
                <div class="form-group">
                    <label for="theater_naam">Theaternaam</label>
                    <input type="text" id="theater_naam" name="theater_naam" class="form-control" 
                           value="<?php echo sanitize($get_val('theater_naam', 'Aurora Theater')); ?>" required disabled>
                    <span style="font-size: 0.75rem; color: var(--admin-text-muted); display: block; margin-top: 5px;">
                        <?php echo sanitize($get_desc('theater_naam', 'De naam van het theater getoond op de website')); ?>
                    </span>
                </div>
                
                <div class="form-group">
                    <label for="ticket_toeslag">Servicekosten Toeslag per boeking (€)</label>
                    <input type="number" step="0.01" min="0" id="ticket_toeslag" name="ticket_toeslag" class="form-control" 
                           value="<?php echo floatval($get_val('ticket_toeslag', '1.50')); ?>" required disabled>
                    <span style="font-size: 0.75rem; color: var(--admin-text-muted); display: block; margin-top: 5px;">
                        <?php echo sanitize($get_desc('ticket_toeslag', 'Servicekosten per ticket boeking')); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Contact details -->
        <div class="settings-item">
            <h4 style="color: var(--admin-primary); margin-bottom: 15px;">2. Contactgegevens</h4>
            <div class="form-row">
                <div class="form-group">
                    <label for="contact_email">E-mailadres Klantenservice</label>
                    <input type="email" id="contact_email" name="contact_email" class="form-control" 
                           value="<?php echo sanitize($get_val('contact_email', 'info@auroratheater.nl')); ?>" required disabled>
                    <span style="font-size: 0.75rem; color: var(--admin-text-muted); display: block; margin-top: 5px;">
                        <?php echo sanitize($get_desc('contact_email', 'Algemeen contact e-mailadres')); ?>
                    </span>
                </div>
                
                <div class="form-group">
                    <label for="contact_telefoon">Telefoonnummer</label>
                    <input type="text" id="contact_telefoon" name="contact_telefoon" class="form-control" 
                           value="<?php echo sanitize($get_val('contact_telefoon', '020-1234567')); ?>" required disabled>
                    <span style="font-size: 0.75rem; color: var(--admin-text-muted); display: block; margin-top: 5px;">
                        <?php echo sanitize($get_desc('contact_telefoon', 'Algemeen telefoonnummer')); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Adres details -->
        <div class="settings-item" style="border-bottom: none; padding-bottom: 0;">
            <h4 style="color: var(--admin-primary); margin-bottom: 15px;">3. Locatie & Adres</h4>
            <div class="form-row">
                <div class="form-group">
                    <label for="adres_straat">Straatnaam en Huisnummer</label>
                    <input type="text" id="adres_straat" name="adres_straat" class="form-control" 
                           value="<?php echo sanitize($get_val('adres_straat', 'Theaterplein 1')); ?>" required disabled>
                    <span style="font-size: 0.75rem; color: var(--admin-text-muted); display: block; margin-top: 5px;">
                        <?php echo sanitize($get_desc('adres_straat', 'Adres straat en huisnummer')); ?>
                    </span>
                </div>
                
                <div class="form-group">
                    <label for="adres_stad">Stad / Plaats</label>
                    <input type="text" id="adres_stad" name="adres_stad" class="form-control" 
                           value="<?php echo sanitize($get_val('adres_stad', 'Amsterdam')); ?>" required disabled>
                    <span style="font-size: 0.75rem; color: var(--admin-text-muted); display: block; margin-top: 5px;">
                        <?php echo sanitize($get_desc('adres_stad', 'Stad van het theater')); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Opslaanknoppen - DISABLED -->
        <div style="display: flex; gap: 10px; margin-top: 30px;">
            <a href="dashboard.php" class="btn-secondary">Terug naar Dashboard</a>
        </div>
    </form>
</div>

<?php
// Inclusief footer
include '../includes/admin_footer.php';
?>
