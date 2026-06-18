<?php
/**
 * Nieuwe Voorstelling Toevoegen - Aurora Theater Admin
 * 
 * Voegt een nieuwe theatershow toe aan de programmering.
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
    $beschrijving = sanitize($_POST['beschrijving'] ?? '');
    $datum = sanitize($_POST['datum'] ?? '');
    $tijd = sanitize($_POST['tijd'] ?? '');
    $locatie = sanitize($_POST['locatie'] ?? '');
    $afbeelding = sanitize($_POST['afbeelding'] ?? '');
    $plaatsen = isset($_POST['plaatsen']) ? intval($_POST['plaatsen']) : 0;
    $prijs = isset($_POST['prijs']) ? floatval($_POST['prijs']) : 0.00;

    // Combineer datum en tijd
    $datum_tijd = $datum . ' ' . $tijd;
    $performance_timestamp = strtotime($datum_tijd);
    $current_timestamp = time();

    // Validatie (Unhappy Scenario: "Controleer alle ingevoerde gegevens")
    if (empty($titel) || empty($beschrijving) || empty($datum) || empty($tijd) || empty($locatie) || empty($afbeelding) || $plaatsen <= 0 || $prijs <= 0) {
        $error = "Controleer alle ingevoerde gegevens: Vul alle velden correct in en voer een geldige capaciteit en prijs in.";
    } elseif ($performance_timestamp <= $current_timestamp) {
        // Datumcontrole: Voorstelling moet in de toekomst liggen
        $error = "Controleer alle ingevoerde gegevens: De voorstelling moet in de toekomst gepland worden.";
    } else {
        // Opslaan in database
        // beschikbare_plaatsen en max_plaatsen worden beide ingesteld op de capaciteit
        $populair = 0;
        
        $insert_stmt = $conn->prepare("INSERT INTO voorstellingen (titel, beschrijving, afbeelding, datum_tijd, zaal, prijs, beschikbare_plaatsen, max_plaatsen, populair) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("sssssdiii", $titel, $beschrijving, $afbeelding, $datum_tijd, $locatie, $prijs, $plaatsen, $plaatsen, $populair);

        if ($insert_stmt->execute()) {
            // EXTRA: Voeg na succesvolle toevoeging automatisch een melding toe aan de tabel meldingen
            $system_name = 'Systeem';
            $system_email = 'info@auroratheater.nl';
            $log_subject = 'Nieuwe voorstelling toegevoegd';
            $log_message = "Nieuwe voorstelling toegevoegd: '$titel' gepland op $datum_tijd in zaal: $locatie.";
            $log_priority = 'gemiddeld';
            $log_date = date('Y-m-d');
            $log_status = 'nieuw';

            $log_stmt = $conn->prepare("INSERT INTO meldingen (naam, email, onderwerp, bericht, status, prioriteit, datum) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $log_stmt->bind_param("sssssss", $system_name, $system_email, $log_subject, $log_message, $log_status, $log_priority, $log_date);
            $log_stmt->execute();
            $log_stmt->close();

            // Happy scenario: "Voorstelling succesvol toegevoegd"
            setFlashMessage('success', 'Voorstelling succesvol toegevoegd.');
            header('Location: ../voorstellingen.php');
            exit;
        } else {
            $error = "Fout bij het opslaan van de voorstelling: " . $conn->error;
        }
        $insert_stmt->close();
    }
}

// Inclusief header (HTML start)
include '../../includes/admin_header.php';
?>

<div class="admin-card">
    <h3>Nieuwe Voorstelling Toevoegen</h3>
    <p style="color: var(--admin-text-muted); margin-bottom: 25px;">
        Voeg een nieuwe theatervoorstelling toe aan de programmering van Aurora.
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
                <label for="titel">Titel van de voorstelling *</label>
                <input type="text" id="titel" name="titel" class="form-control" placeholder="Bijv. Romeo en Julia" value="<?php echo isset($_POST['titel']) ? sanitize($_POST['titel']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="locatie">Locatie (Zaal) *</label>
                <select id="locatie" name="locatie" class="form-control" required>
                    <option value="">-- Kies een zaal --</option>
                    <option value="Grote Zaal A" <?php echo (isset($_POST['locatie']) && $_POST['locatie'] === 'Grote Zaal A') ? 'selected' : ''; ?>>Grote Zaal A (Max. 150 stoelen)</option>
                    <option value="Koninklijke Zaal" <?php echo (isset($_POST['locatie']) && $_POST['locatie'] === 'Koninklijke Zaal') ? 'selected' : ''; ?>>Koninklijke Zaal (Max. 150 stoelen)</option>
                    <option value="Intieme Zaal B" <?php echo (isset($_POST['locatie']) && $_POST['locatie'] === 'Intieme Zaal B') ? 'selected' : ''; ?>>Intieme Zaal B (Max. 50 stoelen)</option>
                </select>
            </div>
        </div>

        <div class="form-group form-group-full">
            <label for="beschrijving">Beschrijving / Samenvatting *</label>
            <textarea id="beschrijving" name="beschrijving" class="form-control" placeholder="Schrijf hier een korte introductie of plot..." required><?php echo isset($_POST['beschrijving']) ? sanitize($_POST['beschrijving']) : ''; ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="datum">Datum *</label>
                <input type="date" id="datum" name="datum" class="form-control" value="<?php echo isset($_POST['datum']) ? sanitize($_POST['datum']) : date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="tijd">Tijdstip *</label>
                <input type="time" id="tijd" name="tijd" class="form-control" value="<?php echo isset($_POST['tijd']) ? sanitize($_POST['tijd']) : '20:00'; ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="plaatsen">Aantal beschikbare plaatsen *</label>
                <input type="number" min="1" max="150" id="plaatsen" name="plaatsen" class="form-control" placeholder="Bijv. 150" value="<?php echo isset($_POST['plaatsen']) ? intval($_POST['plaatsen']) : 150; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="prijs">Ticketprijs (€) *</label>
                <input type="number" step="0.01" min="0.01" id="prijs" name="prijs" class="form-control" placeholder="Bijv. 24.50" value="<?php echo isset($_POST['prijs']) ? floatval($_POST['prijs']) : ''; ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="afbeelding">Afbeelding URL *</label>
            <input type="text" id="afbeelding" name="afbeelding" class="form-control" placeholder="Bijv. assets/images/romeo.png" value="<?php echo isset($_POST['afbeelding']) ? sanitize($_POST['afbeelding']) : 'assets/images/hero.png'; ?>" onchange="document.getElementById('img-preview').src = '../../' + this.value; document.getElementById('img-preview-box').style.display='flex';" required>
            <div class="image-preview-box" id="img-preview-box" style="margin-top: 15px; width: 120px; height: 160px; border: 1px dashed var(--admin-border); border-radius: 8px; align-items: center; justify-content: center; overflow: hidden; display: flex; background-color: var(--admin-bg-dark);">
                <img id="img-preview" src="../../<?php echo isset($_POST['afbeelding']) ? sanitize($_POST['afbeelding']) : 'assets/images/hero.png'; ?>" alt="Poster preview" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn-primary">
                <span>Voorstelling Opslaan</span>
            </button>
            <a href="../voorstellingen.php" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border-radius: 30px; font-weight: bold; font-size: 0.95rem;">Annuleren</a>
        </div>
    </form>
</div>

<?php
// Inclusief footer
include '../../includes/admin_footer.php';
?>
