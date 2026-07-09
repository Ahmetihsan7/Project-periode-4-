<?php
/**
 * Account Wijzigen - Aurora Theater Admin
 * 
 * Wijzigt de gegevens van een bestaand gebruikersaccount.
 * Alleen toegankelijk voor beheerders (Admins).
 */
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Beveiliging: Alleen admins mogen accounts wijzigen
checkAccess(['admin']);

$error = '';
$success = '';
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user = null;

// Haal de bestaande gegevens op via PDO
if ($user_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM gebruikers WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Fout bij het ophalen van gegevens: " . $e->getMessage();
    }
}

// Verwerk het formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $naam = sanitize($_POST['naam'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $wachtwoord = $_POST['wachtwoord'] ?? '';
    $rol = sanitize($_POST['rol'] ?? '');

    // Validatie op lege velden (Unhappy Scenario)
    if (empty($naam) || empty($email) || empty($rol)) {
        $error = "Controleer de ingevoerde gegevens: Vul alle verplichte velden in.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Controleer de ingevoerde gegevens: Vul een geldig e-mailadres in.";
    } else {
        // Map rol van formulier naar database enum
        $db_rol = 'klant';
        if ($rol === 'Medewerker') {
            $db_rol = 'medewerker';
        } elseif ($rol === 'Administrator') {
            $db_rol = 'admin';
        }

        try {
            // Controle op bestaand emailadres bij ANDERE gebruikers (uniek e-mail)
            $check_stmt = $pdo->prepare("SELECT id FROM gebruikers WHERE email = ? AND id != ?");
            $check_stmt->execute([$email, $user_id]);
            $existing_user = $check_stmt->fetch();

            if ($existing_user) {
                $error = "Controleer de ingevoerde gegevens: Dit e-mailadres is al in gebruik.";
            } else {
                // Wachtwoord alleen wijzigen als een nieuw wachtwoord is ingevuld
                if (!empty($wachtwoord)) {
                    $hashed_password = password_hash($wachtwoord, PASSWORD_DEFAULT);
                    $update_stmt = $pdo->prepare("UPDATE gebruikers SET naam = ?, email = ?, wachtwoord = ?, rol = ? WHERE id = ?");
                    $update_stmt->execute([$naam, $email, $hashed_password, $db_rol, $user_id]);
                } else {
                    $update_stmt = $pdo->prepare("UPDATE gebruikers SET naam = ?, email = ?, rol = ? WHERE id = ?");
                    $update_stmt->execute([$naam, $email, $db_rol, $user_id]);
                }

                // Happy scenario: "Gegevens succesvol gewijzigd"
                setFlashMessage('success', 'Gegevens succesvol gewijzigd');
                header('Location: ../accounts.php');
                exit;
            }
        } catch (PDOException $e) {
            $error = "Controleer de ingevoerde gegevens: " . $e->getMessage();
        }
    }
}

// Inclusief header (HTML start)
include '../../includes/admin_header.php';
?>

<div class="admin-card">
    <h3>Account Wijzigen</h3>
    <p style="color: var(--admin-text-muted); margin-bottom: 25px;">
        Pas de onderstaande gegevens aan om het gebruikersaccount bij te werken.
    </p>

    <!-- Foutmeldingen weergeven -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error" id="form-error">
            <span class="alert-icon">✗</span>
            <span class="alert-text"><?php echo sanitize($error); ?></span>
            <button class="alert-close" onclick="document.getElementById('form-error').style.display='none'">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (!$user): ?>
        <div class="alert alert-error">Gegevens niet gevonden.</div>
        <a href="../accounts.php" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border-radius: 30px; font-weight: bold; font-size: 0.95rem; margin-top: 15px;">Terug naar overzicht</a>
    <?php else: ?>
        <!-- Responsive Formulier -->
        <form action="edit.php?id=<?php echo $user_id; ?>" method="POST" class="my-4">
            <div class="form-row">
                <div class="form-group">
                    <label for="naam">Naam *</label>
                    <input type="text" id="naam" name="naam" class="form-control" placeholder="Bijv. Mark de Vries" value="<?php echo sanitize($user['naam']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">E-mailadres *</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Bijv. mark@example.com" value="<?php echo sanitize($user['email']); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="wachtwoord">Wachtwoord (Laat leeg om niet te wijzigen)</label>
                    <input type="password" id="wachtwoord" name="wachtwoord" class="form-control" placeholder="Voer een nieuw wachtwoord in">
                </div>
                
                <div class="form-group">
                    <label for="rol">Rol *</label>
                    <select id="rol" name="rol" class="form-control" required>
                        <option value="Bezoeker" <?php echo ($user['rol'] === 'klant') ? 'selected' : ''; ?>>Bezoeker (Klant)</option>
                        <option value="Medewerker" <?php echo ($user['rol'] === 'medewerker') ? 'selected' : ''; ?>>Medewerker</option>
                        <option value="Administrator" <?php echo ($user['rol'] === 'admin') ? 'selected' : ''; ?>>Administrator (Admin)</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn-primary">
                    <span>Opslaan</span>
                </button>
                <a href="../accounts.php" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border-radius: 30px; font-weight: bold; font-size: 0.95rem;">Annuleren</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php
// Inclusief footer
include '../../includes/admin_footer.php';
?>
