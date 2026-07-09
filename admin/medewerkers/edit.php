<?php
/**
 * Medewerker Contract Wijzigen - Aurora Theater Admin
 * 
 * Wijzigt de contract- en accountgegevens van een medewerker in een transactie.
 * Alleen toegankelijk voor beheerders (Admins).
 */
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Beveiliging: Alleen admins mogen medewerkers beheren/wijzigen
checkAccess(['admin']);

$error = '';
$success = '';
$employee_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$emp_data = null;

// Haal de bestaande medewerker en gekoppelde gebruiker op
if ($employee_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT m.*, g.naam, g.email, g.rol FROM medewerkers m JOIN gebruikers g ON m.gebruiker_id = g.id WHERE m.id = ?");
        $stmt->execute([$employee_id]);
        $emp_data = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Fout bij ophalen van gegevens: " . $e->getMessage();
    }
}

// Verwerk het formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $emp_data) {
    $voornaam = sanitize($_POST['voornaam'] ?? '');
    $achternaam = sanitize($_POST['achternaam'] ?? '');
    $functie = sanitize($_POST['functie'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $telefoon = sanitize($_POST['telefoon'] ?? '');
    $salaris = isset($_POST['salaris']) && $_POST['salaris'] !== '' ? floatval($_POST['salaris']) : null;
    $aangenomen_op = sanitize($_POST['aangenomen_op'] ?? '');

    // Validatie op lege velden (Unhappy Scenario)
    if (empty($voornaam) || empty($achternaam) || empty($functie) || empty($email) || empty($telefoon) || empty($aangenomen_op)) {
        $error = "Controleer de ingevoerde gegevens: Vul alle verplichte velden in.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Controleer de ingevoerde gegevens: Vul een geldig e-mailadres in.";
    } else {
        try {
            // Controleer of het e-mailadres al in gebruik is bij een ANDERE gebruiker
            $check_stmt = $pdo->prepare("SELECT id FROM gebruikers WHERE email = ? AND id != ?");
            $check_stmt->execute([$email, $emp_data['gebruiker_id']]);
            $existing_user = $check_stmt->fetch();

            if ($existing_user) {
                $error = "Controleer de ingevoerde gegevens: Dit e-mailadres is al in gebruik.";
            } else {
                // Start transactie voor consistente updates
                $pdo->beginTransaction();

                // 1. Update gebruikersaccount
                $fullname = trim($voornaam . ' ' . $achternaam);
                $user_stmt = $pdo->prepare("UPDATE gebruikers SET naam = ?, email = ? WHERE id = ?");
                $user_stmt->execute([$fullname, $email, $emp_data['gebruiker_id']]);

                // 2. Update medewerkers contractgegevens
                $emp_stmt = $pdo->prepare("UPDATE medewerkers SET voornaam = ?, achternaam = ?, functie = ?, salaris = ?, aangenomen_op = ?, telefoon = ? WHERE id = ?");
                $emp_stmt->execute([$voornaam, $achternaam, $functie, $salaris, $aangenomen_op, $telefoon, $employee_id]);

                $pdo->commit();

                // Happy scenario: "Gegevens succesvol gewijzigd"
                setFlashMessage('success', 'Gegevens succesvol gewijzigd');
                header('Location: ../medewerkers.php');
                exit;
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Controleer de ingevoerde gegevens: " . $e->getMessage();
        }
    }
}

// Inclusief header (HTML start)
include '../../includes/admin_header.php';
?>

<div class="admin-card">
    <h3>Medewerker Wijzigen</h3>
    <p style="color: var(--admin-text-muted); margin-bottom: 25px;">
        Pas de contract- en persoonsgegevens van de medewerker aan.
    </p>

    <!-- Foutmeldingen weergeven -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error" id="form-error">
            <span class="alert-icon">✗</span>
            <span class="alert-text"><?php echo sanitize($error); ?></span>
            <button class="alert-close" onclick="document.getElementById('form-error').style.display='none'">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (!$emp_data): ?>
        <div class="alert alert-error">Gegevens niet gevonden.</div>
        <a href="../medewerkers.php" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border-radius: 30px; font-weight: bold; font-size: 0.95rem; margin-top: 15px;">Terug naar overzicht</a>
    <?php else: ?>
        <!-- Responsive Formulier -->
        <form action="edit.php?id=<?php echo $employee_id; ?>" method="POST" class="my-4">
            <div class="form-row">
                <div class="form-group">
                    <label for="voornaam">Voornaam *</label>
                    <input type="text" id="voornaam" name="voornaam" class="form-control" placeholder="Bijv. Sarah" value="<?php echo sanitize($emp_data['voornaam']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="achternaam">Achternaam *</label>
                    <input type="text" id="achternaam" name="achternaam" class="form-control" placeholder="Bijv. de Beus" value="<?php echo sanitize($emp_data['achternaam']); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="functie">Functie *</label>
                    <input type="text" id="functie" name="functie" class="form-control" placeholder="Bijv. Kassa & Publieksbegeleiding" value="<?php echo sanitize($emp_data['functie']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">E-mailadres (Inlogadres) *</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Bijv. sarah@auroratheater.nl" value="<?php echo sanitize($emp_data['email']); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="telefoon">Telefoonnummer *</label>
                    <input type="text" id="telefoon" name="telefoon" class="form-control" placeholder="Bijv. 0612345678" value="<?php echo sanitize($emp_data['telefoon']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="salaris">Bruto Maandsalaris (€)</label>
                    <input type="number" step="0.01" min="0" id="salaris" name="salaris" class="form-control" placeholder="Bijv. 2500.00" value="<?php echo $emp_data['salaris']; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="aangenomen_op">Aangenomen Op *</label>
                    <input type="date" id="aangenomen_op" name="aangenomen_op" class="form-control" value="<?php echo $emp_data['aangenomen_op']; ?>" required>
                </div>
                <div class="form-group" style="display: flex; align-items: center; padding-top: 25px; color: var(--admin-text-muted); font-size: 0.85rem;">
                    ℹ️ Let op: Het aanpassen van de naam of het e-mailadres zal ook direct het gekoppelde systeemaccount bijwerken.
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn-primary">
                    <span>Opslaan</span>
                </button>
                <a href="../medewerkers.php" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border-radius: 30px; font-weight: bold; font-size: 0.95rem;">Annuleren</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php
// Inclusief footer
include '../../includes/admin_footer.php';
?>
