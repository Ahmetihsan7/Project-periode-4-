<?php
/**
 * Nieuw Account Toevoegen - Aurora Theater Admin
 * 
 * Maakt een nieuw gebruikersaccount aan en slaat dit op in de database.
 * Alleen toegankelijk voor beheerders (Admins).
 */
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Beveiliging: Alleen admins mogen accounts aanmaken
checkAccess(['admin']);

$error = '';
$success = '';

// Verwerk het formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam = sanitize($_POST['naam'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $wachtwoord = $_POST['wachtwoord'] ?? '';
    $rol = sanitize($_POST['rol'] ?? '');

    // Validatie op lege velden (Unhappy Scenario: "Vul alle verplichte velden in")
    if (empty($naam) || empty($email) || empty($wachtwoord) || empty($rol)) {
        $error = "Vul alle verplichte velden in.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Vul een geldig e-mailadres in.";
    } else {
        // Map rol van formulier naar database enum ('klant', 'medewerker', 'admin')
        $db_rol = 'klant';
        if ($rol === 'Medewerker') {
            $db_rol = 'medewerker';
        } elseif ($rol === 'Administrator') {
            $db_rol = 'admin';
        }

        // Controle op bestaand emailadres (Unhappy Scenario: "Email bestaat al")
        $check_stmt = $conn->prepare("SELECT id FROM gebruikers WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_stmt->close();

        if ($check_result && $check_result->num_rows > 0) {
            $error = "Email bestaat al.";
        } else {
            // Wachtwoord hashen voor beveiliging
            $hashed_password = password_hash($wachtwoord, PASSWORD_DEFAULT);

            // Opslaan in database met prepared statement
            $insert_stmt = $conn->prepare("INSERT INTO gebruikers (naam, email, wachtwoord, rol) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssss", $naam, $email, $hashed_password, $db_rol);

            if ($insert_stmt->execute()) {
                // EXTRA: Voeg na succesvolle toevoeging automatisch een melding toe aan de tabel meldingen
                $system_name = 'Systeem';
                $system_email = 'info@auroratheater.nl';
                $log_subject = 'Nieuw account aangemaakt';
                $log_message = "Er is een nieuw account aangemaakt voor: $naam (Rol: $rol, Email: $email).";
                $log_priority = 'laag';
                $log_date = date('Y-m-d');
                $log_status = 'nieuw';

                $log_stmt = $conn->prepare("INSERT INTO meldingen (naam, email, onderwerp, bericht, status, prioriteit, datum) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $log_stmt->bind_param("sssssss", $system_name, $system_email, $log_subject, $log_message, $log_status, $log_priority, $log_date);
                $log_stmt->execute();
                $log_stmt->close();

                // Happy scenario: "Account succesvol aangemaakt"
                setFlashMessage('success', 'Account succesvol aangemaakt.');
                header('Location: ../accounts.php');
                exit;
            } else {
                $error = "Fout bij het opslaan van het account: " . $conn->error;
            }
            $insert_stmt->close();
        }
    }
}

// Inclusief header (HTML start)
include '../../includes/admin_header.php';
?>

<div class="admin-card">
    <h3>Nieuw Account Toevoegen</h3>
    <p style="color: var(--admin-text-muted); margin-bottom: 25px;">
        Vul de onderstaande gegevens in om een nieuw gebruikersaccount aan te maken.
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
                <label for="naam">Naam *</label>
                <input type="text" id="naam" name="naam" class="form-control" placeholder="Bijv. Mark de Vries" value="<?php echo isset($_POST['naam']) ? sanitize($_POST['naam']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">E-mailadres *</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Bijv. mark@example.com" value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="wachtwoord">Wachtwoord *</label>
                <input type="password" id="wachtwoord" name="wachtwoord" class="form-control" placeholder="Voer een sterk wachtwoord in" required>
            </div>
            
            <div class="form-group">
                <label for="rol">Rol *</label>
                <select id="rol" name="rol" class="form-control" required>
                    <option value="">-- Kies een rol --</option>
                    <option value="Bezoeker" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'Bezoeker') ? 'selected' : ''; ?>>Bezoeker (Klant)</option>
                    <option value="Medewerker" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'Medewerker') ? 'selected' : ''; ?>>Medewerker</option>
                    <option value="Administrator" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'Administrator') ? 'selected' : ''; ?>>Administrator (Admin)</option>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn-primary">
                <span>Account Opslaan</span>
            </button>
            <a href="../accounts.php" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border-radius: 30px; font-weight: bold; font-size: 0.95rem;">Annuleren</a>
        </div>
    </form>
</div>

<?php
// Inclusief footer
include '../../includes/admin_footer.php';
?>
