<?php
/**
 * Nieuwe Medewerker Toevoegen - Aurora Theater Admin
 * 
 * Voegt een nieuwe medewerker toe door een gebruikersaccount aan te maken
 * en contractgegevens op te slaan in een transactie.
 * Alleen toegankelijk voor beheerders (Admins).
 */

// Zorg dat db.php niet de gehele pagina blokkeert met die() bij een verbindingsfout
$ignore_db_error = true;
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Beveiliging: Alleen admins mogen medewerkers beheren
checkAccess(['admin']);

$error = '';
$success = '';

// 1. Controleer eerst of de databaseverbinding geldig is (GET en POST)
if (!$conn || $conn->connect_error) {
    // Unhappy Scenario: "Geen verbinding mogelijk met de database..."
    $error = "Geen verbinding mogelijk met de database. Medewerker kon niet worden toegevoegd. Probeer het later opnieuw.";
}

// Verwerk het formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Voer geen INSERT query of andere DB logica uit wanneer de verbinding mislukt
    if (!$conn || $conn->connect_error) {
        $error = "Geen verbinding mogelijk met de database. Medewerker kon niet worden toegevoegd. Probeer het later opnieuw.";
    } else {
        $voornaam = sanitize($_POST['voornaam'] ?? '');
        $achternaam = sanitize($_POST['achternaam'] ?? '');
        $functie = sanitize($_POST['functie'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $telefoon = sanitize($_POST['telefoon'] ?? '');

        // Validatie op lege velden
        if (empty($voornaam) || empty($achternaam) || empty($functie) || empty($email) || empty($telefoon)) {
            $error = "Medewerker kon niet worden toegevoegd: Vul alle verplichte velden in.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Medewerker kon niet worden toegevoegd: Vul een geldig e-mailadres in.";
        } else {
            // Stel mysqli in om uitzonderingen te werpen voor correcte try/catch afhandeling
            $previous_report_mode = mysqli_report(MYSQLI_REPORT_OFF);
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            // 2. Gebruik try/catch voor mysqli error handling
            try {
                // Controleer of de gebruiker (email) al bestaat
                $check_stmt = $conn->prepare("SELECT id FROM gebruikers WHERE email = ?");
                $check_stmt->bind_param("s", $email);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $check_stmt->close();

                if ($check_result && $check_result->num_rows > 0) {
                    $error = "Medewerker kon niet worden toegevoegd: E-mailadres bestaat al.";
                } else {
                    // Start transactie om beide tabellen consistent te vullen
                    $conn->begin_transaction();
                    
                    // 1. Maak gebruikersaccount aan met rol 'medewerker'
                    $fullname = trim($voornaam . ' ' . $achternaam);
                    // Genereer een standaard/veilig wachtwoord dat later gewijzigd kan worden
                    $default_pass = password_hash('WelkomMedewerker123!', PASSWORD_DEFAULT);
                    
                    $user_stmt = $conn->prepare("INSERT INTO gebruikers (naam, email, wachtwoord, rol) VALUES (?, ?, ?, 'medewerker')");
                    $user_stmt->bind_param("sss", $fullname, $email, $default_pass);
                    $user_stmt->execute();
                    $gebruiker_id = $conn->insert_id;
                    $user_stmt->close();

                    // 2. Koppel contractdetails in de medewerkers tabel
                    $salaris = 2500.00; // Standaard bruto startsalaris als fallback
                    $aangenomen_op = date('Y-m-d');
                    
                    $emp_stmt = $conn->prepare("INSERT INTO medewerkers (gebruiker_id, functie, salaris, aangenomen_op, voornaam, achternaam, telefoon) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $emp_stmt->bind_param("isdssss", $gebruiker_id, $functie, $salaris, $aangenomen_op, $voornaam, $achternaam, $telefoon);
                    $emp_stmt->execute();
                    $emp_stmt->close();

                    // 3. EXTRA: Voeg automatisch een melding toe
                    $system_name = 'Systeem';
                    $system_email = 'info@auroratheater.nl';
                    $log_subject = 'Nieuwe medewerker toegevoegd';
                    $log_message = "Nieuwe medewerker toegevoegd: $voornaam $achternaam (Functie: $functie, E-mail: $email, Tel: $telefoon).";
                    $log_priority = 'gemiddeld';
                    $log_date = date('Y-m-d');
                    $log_status = 'nieuw';

                    $log_stmt = $conn->prepare("INSERT INTO meldingen (naam, email, onderwerp, bericht, status, prioriteit, datum) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $log_stmt->bind_param("sssssss", $system_name, $system_email, $log_subject, $log_message, $log_status, $log_priority, $log_date);
                    $log_stmt->execute();
                    $log_stmt->close();

                    // Transactie voltooien
                    $conn->commit();

                    // Herstel de originele mysqli rapportage modus
                    mysqli_report($previous_report_mode);

                    // Happy scenario: "Medewerker succesvol toegevoegd."
                    setFlashMessage('success', 'Medewerker succesvol toegevoegd.');
                    header('Location: ../medewerkers.php');
                    exit;
                }
            } catch (mysqli_sql_exception $e) {
                // Rolback bij eventuele fouten
                if (isset($conn) && $conn->in_transaction) {
                    $conn->rollback();
                }
                mysqli_report($previous_report_mode);

                // Als de uitzondering voortkomt uit een verbroken verbinding
                if (isset($conn) && $conn->ping() === false) {
                    $error = "Geen verbinding mogelijk met de database. Medewerker kon niet worden toegevoegd. Probeer het later opnieuw.";
                } else {
                    $error = "Medewerker kon niet worden toegevoegd: " . $e->getMessage();
                }
            } catch (Exception $e) {
                if (isset($conn) && $conn->in_transaction) {
                    $conn->rollback();
                }
                mysqli_report($previous_report_mode);
                $error = "Medewerker kon niet worden toegevoegd: " . $e->getMessage();
            }
        }
    }
}

// Inclusief header (HTML start)
include '../../includes/admin_header.php';
?>

<div class="admin-card">
    <h3>Nieuwe Medewerker Toevoegen</h3>
    <p style="color: var(--admin-text-muted); margin-bottom: 25px;">
        Vul de contract- en persoonsgegevens in om een nieuwe medewerker te registeren en te koppelen aan een systeemaccount.
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
                <label for="voornaam">Voornaam *</label>
                <input type="text" id="voornaam" name="voornaam" class="form-control" placeholder="Bijv. Sarah" value="<?php echo isset($_POST['voornaam']) ? sanitize($_POST['voornaam']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="achternaam">Achternaam *</label>
                <input type="text" id="achternaam" name="achternaam" class="form-control" placeholder="Bijv. de Beus" value="<?php echo isset($_POST['achternaam']) ? sanitize($_POST['achternaam']) : ''; ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="functie">Functie *</label>
                <input type="text" id="functie" name="functie" class="form-control" placeholder="Bijv. Kassa & Publieksbegeleiding" value="<?php echo isset($_POST['functie']) ? sanitize($_POST['functie']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">E-mailadres (Inlogadres) *</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Bijv. sarah@auroratheater.nl" value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="telefoon">Telefoonnummer *</label>
                <input type="text" id="telefoon" name="telefoon" class="form-control" placeholder="Bijv. 0612345678" value="<?php echo isset($_POST['telefoon']) ? sanitize($_POST['telefoon']) : ''; ?>" required>
            </div>
            <div class="form-group" style="display: flex; align-items: center; padding-top: 25px; color: var(--admin-text-muted); font-size: 0.85rem;">
                ℹ️ Let op: Er wordt automatisch een systeemaccount aangemaakt voor deze medewerker met het inlogwachtwoord <strong>WelkomMedewerker123!</strong>
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn-primary">
                <span>Medewerker Opslaan</span>
            </button>
            <a href="../medewerkers.php" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border-radius: 30px; font-weight: bold; font-size: 0.95rem;">Annuleren</a>
        </div>
    </form>
</div>

<?php
// Inclusief footer
include '../../includes/admin_footer.php';
?>
