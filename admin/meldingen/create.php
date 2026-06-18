<?php
/**
 * Nieuwe Melding Toevoegen - Aurora Theater Admin
 * 
 * Voegt handmatig een systeemmelding of waarschuwing toe.
 * Toegankelijk voor admins en medewerkers.
 */
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Beveiliging: Toegankelijk voor admin en medewerker
checkAccess(['admin', 'medewerker']);

$error = '';
$success = '';

// Verwerk het formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titel = sanitize($_POST['titel'] ?? '');
    $bericht = sanitize($_POST['bericht'] ?? '');
    $prioriteit = sanitize($_POST['prioriteit'] ?? 'gemiddeld');
    $datum = sanitize($_POST['datum'] ?? date('Y-m-d'));

    // Validatie (Unhappy Scenario: "Melding kon niet worden opgeslagen")
    if (empty($titel) || empty($bericht) || empty($prioriteit) || empty($datum)) {
        $error = "Melding kon niet worden opgeslagen: Vul alle verplichte velden in.";
    } else {
        // Om compatibel te blijven met de bestaande `meldingen` tabel, vullen we defaults in voor naam en email
        $system_name = 'Systeem (Handmatig)';
        $system_email = 'info@auroratheater.nl';
        $status = 'nieuw';

        // Opslaan in database
        $insert_stmt = $conn->prepare("INSERT INTO meldingen (naam, email, onderwerp, bericht, status, prioriteit, datum) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("sssssss", $system_name, $system_email, $titel, $bericht, $status, $prioriteit, $datum);

        if ($insert_stmt->execute()) {
            // EXTRA: Voeg na succesvolle toevoeging automatisch een melding toe aan de tabel meldingen
            // Dit is de logmelding die de actie zelf vastlegt
            $log_subject = 'Melding succesvol geplaatst';
            $log_message = "Er is handmatig een nieuwe melding geplaatst met titel: '$titel' en prioriteit: $prioriteit.";
            $log_priority = 'laag';
            $log_date = date('Y-m-d');
            
            $log_stmt = $conn->prepare("INSERT INTO meldingen (naam, email, onderwerp, bericht, status, prioriteit, datum) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $log_stmt->bind_param("sssssss", $system_name, $system_email, $log_subject, $log_message, $status, $log_priority, $log_date);
            $log_stmt->execute();
            $log_stmt->close();

            // Happy scenario: "Melding succesvol geplaatst"
            setFlashMessage('success', 'Melding succesvol geplaatst.');
            header('Location: ../meldingen.php');
            exit;
        } else {
            $error = "Melding kon niet worden opgeslagen: " . $conn->error;
        }
        $insert_stmt->close();
    }
}

// Inclusief header (HTML start)
include '../../includes/admin_header.php';
?>

<div class="admin-card">
    <h3>Nieuwe Melding Geven</h3>
    <p style="color: var(--admin-text-muted); margin-bottom: 25px;">
        Plaats handmatig een nieuwe melding of notificatie in het beheeromgeving dashboard.
    </p>

    <!-- Foutmeldingen weergeven -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error" id="form-error">
            <span class="alert-icon">✗</span>
            <span class="alert-text"><?php echo sanitize($error); ?></span>
            <button class="alert-close" onclick="document.getElementById('form-error').style.display='none'">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Responsive Formulier -->
    <form action="create.php" method="POST" class="my-4">
        <div class="form-row">
            <div class="form-group">
                <label for="titel">Titel *</label>
                <input type="text" id="titel" name="titel" class="form-control" placeholder="Bijv. Systeemonderhoud gepland" value="<?php echo isset($_POST['titel']) ? sanitize($_POST['titel']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="prioriteit">Prioriteit *</label>
                <select id="prioriteit" name="prioriteit" class="form-control" required>
                    <option value="laag" <?php echo (isset($_POST['prioriteit']) && $_POST['prioriteit'] === 'laag') ? 'selected' : ''; ?>>Laag</option>
                    <option value="gemiddeld" <?php echo (!isset($_POST['prioriteit']) || $_POST['prioriteit'] === 'gemiddeld') ? 'selected' : ''; ?>>Gemiddeld</option>
                    <option value="hoog" <?php echo (isset($_POST['prioriteit']) && $_POST['prioriteit'] === 'hoog') ? 'selected' : ''; ?>>Hoog ⚠️</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="datum">Datum *</label>
                <input type="date" id="datum" name="datum" class="form-control" value="<?php echo isset($_POST['datum']) ? sanitize($_POST['datum']) : date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group" style="display: flex; align-items: center; padding-top: 25px; color: var(--admin-text-muted); font-size: 0.85rem;">
                ℹ️ Meldingen met prioriteit 'hoog' worden extra geaccentueerd in het overzicht.
            </div>
        </div>

        <div class="form-group form-group-full">
            <label for="bericht">Bericht *</label>
            <textarea id="bericht" name="bericht" class="form-control" placeholder="Schrijf hier de inhoud van de melding..." required><?php echo isset($_POST['bericht']) ? sanitize($_POST['bericht']) : ''; ?></textarea>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn-primary">
                <span>Melding Opslaan</span>
            </button>
            <a href="../meldingen.php" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border-radius: 30px; font-weight: bold; font-size: 0.95rem;">Annuleren</a>
        </div>
    </form>
</div>

<?php
// Inclusief footer
include '../../includes/admin_footer.php';
?>
