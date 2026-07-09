<?php
/**
 * Melding Wijzigen - Aurora Theater Admin
 * 
 * Wijzigt handmatig een geplaatste melding of notificatie.
 * Toegankelijk voor admins en medewerkers.
 */
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Beveiliging: Toegankelijk voor admin en medewerker
checkAccess(['admin', 'medewerker']);

$error = '';
$success = '';
$message_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg_data = null;

// Haal de bestaande melding op via PDO
if ($message_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM meldingen WHERE id = ?");
        $stmt->execute([$message_id]);
        $msg_data = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Fout bij ophalen van gegevens: " . $e->getMessage();
    }
}

// Verwerk het formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $msg_data) {
    $titel = sanitize($_POST['titel'] ?? '');
    $bericht = sanitize($_POST['bericht'] ?? '');
    $prioriteit = sanitize($_POST['prioriteit'] ?? 'gemiddeld');
    $datum = sanitize($_POST['datum'] ?? date('Y-m-d'));

    // Validatie (Unhappy Scenario)
    if (empty($titel) || empty($bericht) || empty($prioriteit) || empty($datum)) {
        $error = "Controleer de ingevoerde gegevens: Vul alle verplichte velden in.";
    } else {
        try {
            // Update in database met PDO
            $update_stmt = $pdo->prepare("UPDATE meldingen SET onderwerp = ?, bericht = ?, prioriteit = ?, datum = ? WHERE id = ?");
            $update_stmt->execute([$titel, $bericht, $prioriteit, $datum, $message_id]);

            // Happy scenario: "Gegevens succesvol gewijzigd"
            setFlashMessage('success', 'Gegevens succesvol gewijzigd');
            header('Location: ../meldingen.php');
            exit;
        } catch (PDOException $e) {
            $error = "Controleer de ingevoerde gegevens: " . $e->getMessage();
        }
    }
}

// Inclusief header (HTML start)
include '../../includes/admin_header.php';
?>

<div class="admin-card">
    <h3>Melding Wijzigen</h3>
    <p style="color: var(--admin-text-muted); margin-bottom: 25px;">
        Pas de details van de dashboard melding of notificatie aan.
    </p>

    <!-- Foutmeldingen weergeven -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error" id="form-error">
            <span class="alert-icon">✗</span>
            <span class="alert-text"><?php echo sanitize($error); ?></span>
            <button class="alert-close" onclick="document.getElementById('form-error').style.display='none'">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (!$msg_data): ?>
        <div class="alert alert-error">Gegevens niet gevonden.</div>
        <a href="../meldingen.php" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border-radius: 30px; font-weight: bold; font-size: 0.95rem; margin-top: 15px;">Terug naar overzicht</a>
    <?php else: ?>
        <!-- Responsive Formulier -->
        <form action="edit.php?id=<?php echo $message_id; ?>" method="POST" class="my-4">
            <div class="form-row">
                <div class="form-group">
                    <label for="titel">Titel *</label>
                    <input type="text" id="titel" name="titel" class="form-control" placeholder="Bijv. Systeemonderhoud gepland" value="<?php echo sanitize($msg_data['onderwerp']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="prioriteit">Prioriteit *</label>
                    <select id="prioriteit" name="prioriteit" class="form-control" required>
                        <option value="laag" <?php echo ($msg_data['prioriteit'] === 'laag') ? 'selected' : ''; ?>>Laag</option>
                        <option value="gemiddeld" <?php echo ($msg_data['prioriteit'] === 'gemiddeld') ? 'selected' : ''; ?>>Gemiddeld</option>
                        <option value="hoog" <?php echo ($msg_data['prioriteit'] === 'hoog') ? 'selected' : ''; ?>>Hoog ⚠️</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="datum">Datum *</label>
                    <!-- Gebruik gemaakt_op als datum NULL is -->
                    <?php $display_date = !empty($msg_data['datum']) ? $msg_data['datum'] : date('Y-m-d', strtotime($msg_data['gemaakt_op'])); ?>
                    <input type="date" id="datum" name="datum" class="form-control" value="<?php echo $display_date; ?>" required>
                </div>
                <div class="form-group" style="display: flex; align-items: center; padding-top: 25px; color: var(--admin-text-muted); font-size: 0.85rem;">
                    ℹ️ Meldingen met prioriteit 'hoog' worden extra geaccentueerd in het overzicht.
                </div>
            </div>

            <div class="form-group form-group-full">
                <label for="bericht">Bericht *</label>
                <textarea id="bericht" name="bericht" class="form-control" placeholder="Schrijf hier de inhoud van de melding..." required><?php echo sanitize($msg_data['bericht']); ?></textarea>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn-primary">
                    <span>Opslaan</span>
                </button>
                <a href="../meldingen.php" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border-radius: 30px; font-weight: bold; font-size: 0.95rem;">Annuleren</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php
// Inclusief footer
include '../../includes/admin_footer.php';
?>
