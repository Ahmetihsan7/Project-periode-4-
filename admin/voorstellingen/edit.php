<?php
/**
 * Voorstelling Wijzigen - Aurora Theater Admin
 * 
 * Wijzigt de gegevens van een theatershow in de programmering.
 * Toegankelijk voor admins en medewerkers.
 */
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Beveiliging: Toegankelijk voor admin en medewerker
checkAccess(['admin', 'medewerker']);

$error = '';
$success = '';
$show_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$show_data = null;

// Haal de bestaande show op
if ($show_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM voorstellingen WHERE id = ?");
        $stmt->execute([$show_id]);
        $show_data = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Fout bij ophalen van gegevens: " . $e->getMessage();
    }
}

// Verwerk het formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $show_data) {
    $titel = sanitize($_POST['titel'] ?? '');
    $beschrijving = sanitize($_POST['beschrijving'] ?? '');
    $datum = sanitize($_POST['datum'] ?? '');
    $tijd = sanitize($_POST['tijd'] ?? '');
    $locatie = sanitize($_POST['locatie'] ?? '');
    $afbeelding = sanitize($_POST['afbeelding'] ?? '');
    $plaatsen = isset($_POST['plaatsen']) ? intval($_POST['plaatsen']) : 0;
    $prijs = isset($_POST['prijs']) ? floatval($_POST['prijs']) : 0.00;

    // Combineer datum en tijd
    $datum_tijd = !empty($datum) && !empty($tijd) ? $datum . ' ' . $tijd : '';

    // Validatie (Unhappy Scenario)
    if (empty($titel) || empty($beschrijving) || empty($datum) || empty($tijd) || empty($locatie) || empty($afbeelding) || $plaatsen <= 0 || $prijs <= 0) {
        $error = "Controleer de ingevoerde gegevens: Vul alle velden correct in en voer een geldige capaciteit en prijs in.";
    } else {
        try {
            // Update in database met PDO
            // Merk op dat we de beschikbare plaatsen en max plaatsen op de nieuwe capaciteit updaten.
            // Dit is hoe de create.php het ook doet.
            $update_stmt = $pdo->prepare("UPDATE voorstellingen SET titel = ?, beschrijving = ?, afbeelding = ?, datum_tijd = ?, zaal = ?, prijs = ?, beschikbare_plaatsen = ?, max_plaatsen = ? WHERE id = ?");
            $update_stmt->execute([$titel, $beschrijving, $afbeelding, $datum_tijd, $locatie, $prijs, $plaatsen, $plaatsen, $show_id]);

            // Happy scenario: "Gegevens succesvol gewijzigd"
            setFlashMessage('success', 'Gegevens succesvol gewijzigd');
            header('Location: ../voorstellingen.php');
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
    <h3>Voorstelling Wijzigen</h3>
    <p style="color: var(--admin-text-muted); margin-bottom: 25px;">
        Pas de details van de theatershow aan.
    </p>

    <!-- Foutmeldingen weergeven -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error" id="form-error">
            <span class="alert-icon">✗</span>
            <span class="alert-text"><?php echo sanitize($error); ?></span>
            <button class="alert-close" onclick="document.getElementById('form-error').style.display='none'">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (!$show_data): ?>
        <div class="alert alert-error">Gegevens niet gevonden.</div>
        <a href="../voorstellingen.php" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border-radius: 30px; font-weight: bold; font-size: 0.95rem; margin-top: 15px;">Terug naar overzicht</a>
    <?php else: ?>
        <?php
        // Splits datum en tijd voor invoervelden
        $split_date = date('Y-m-d', strtotime($show_data['datum_tijd']));
        $split_time = date('H:i', strtotime($show_data['datum_tijd']));
        ?>
        <!-- Responsive Formulier -->
        <form action="edit.php?id=<?php echo $show_id; ?>" method="POST" class="my-4">
            <div class="form-row">
                <div class="form-group">
                    <label for="titel">Titel van de voorstelling *</label>
                    <input type="text" id="titel" name="titel" class="form-control" placeholder="Bijv. Romeo en Julia" value="<?php echo sanitize($show_data['titel']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="locatie">Locatie (Zaal) *</label>
                    <select id="locatie" name="locatie" class="form-control" required>
                        <option value="Grote Zaal A" <?php echo ($show_data['zaal'] === 'Grote Zaal A') ? 'selected' : ''; ?>>Grote Zaal A (Max. 150 stoelen)</option>
                        <option value="Koninklijke Zaal" <?php echo ($show_data['zaal'] === 'Koninklijke Zaal') ? 'selected' : ''; ?>>Koninklijke Zaal (Max. 150 stoelen)</option>
                        <option value="Intieme Zaal B" <?php echo ($show_data['zaal'] === 'Intieme Zaal B') ? 'selected' : ''; ?>>Intieme Zaal B (Max. 50 stoelen)</option>
                    </select>
                </div>
            </div>

            <div class="form-group form-group-full">
                <label for="beschrijving">Beschrijving / Samenvatting *</label>
                <textarea id="beschrijving" name="beschrijving" class="form-control" placeholder="Schrijf hier een korte introductie of plot..." required><?php echo sanitize($show_data['beschrijving']); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="datum">Datum *</label>
                    <input type="date" id="datum" name="datum" class="form-control" value="<?php echo $split_date; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="tijd">Tijdstip *</label>
                    <input type="time" id="tijd" name="tijd" class="form-control" value="<?php echo $split_time; ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="plaatsen">Aantal beschikbare plaatsen *</label>
                    <input type="number" min="1" max="150" id="plaatsen" name="plaatsen" class="form-control" placeholder="Bijv. 150" value="<?php echo intval($show_data['max_plaatsen']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="prijs">Ticketprijs (€) *</label>
                    <input type="number" step="0.01" min="0.01" id="prijs" name="prijs" class="form-control" placeholder="Bijv. 24.50" value="<?php echo floatval($show_data['prijs']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="afbeelding">Afbeelding URL *</label>
                <input type="text" id="afbeelding" name="afbeelding" class="form-control" placeholder="Bijv. assets/images/romeo.png" value="<?php echo sanitize($show_data['afbeelding']); ?>" onchange="document.getElementById('img-preview').src = '../../' + this.value; document.getElementById('img-preview-box').style.display='flex';" required>
                <div class="image-preview-box" id="img-preview-box" style="margin-top: 15px; width: 120px; height: 160px; border: 1px dashed var(--admin-border); border-radius: 8px; align-items: center; justify-content: center; overflow: hidden; display: flex; background-color: var(--admin-bg-dark);">
                    <img id="img-preview" src="../../<?php echo sanitize($show_data['afbeelding']); ?>" alt="Poster preview" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn-primary">
                    <span>Opslaan</span>
                </button>
                <a href="../voorstellingen.php" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border-radius: 30px; font-weight: bold; font-size: 0.95rem;">Annuleren</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php
// Inclusief footer
include '../../includes/admin_footer.php';
?>
