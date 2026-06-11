<?php
/**
 * Registratiepagina - Aurora Theater
 * 
 * Maakt een nieuw klantaccount aan.
 */

// Paginatitel instellen
$page_title = "Registreren";

// Header inladen (dit laadt automatisch db.php en functions.php in)
include 'includes/header.php';

// Redirect als gebruiker al is ingelogd
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Registratie verwerking (Happy/Unhappy Scenario)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $naam = sanitize($_POST['naam'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $wachtwoord = $_POST['wachtwoord'] ?? '';
    $wachtwoord_check = $_POST['wachtwoord_check'] ?? '';
    
    // Unhappy scenario: lege velden
    if (empty($naam) || empty($email) || empty($wachtwoord) || empty($wachtwoord_check)) {
        setFlashMessage('error', 'Registratie mislukt: Vul a.u.b. alle velden in.');
    } 
    // Unhappy scenario: e-mailadres niet geldig
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlashMessage('error', 'Registratie mislukt: Vul a.u.b. een geldig e-mailadres in.');
    }
    // Unhappy scenario: wachtwoorden komen niet overeen
    elseif ($wachtwoord !== $wachtwoord_check) {
        setFlashMessage('error', 'Registratie mislukt: Wachtwoorden komen niet overeen.');
    }
    // Wachtwoord sterkte controle (schoolopdracht eis)
    elseif (strlen($wachtwoord) < 6) {
        setFlashMessage('error', 'Registratie mislukt: Wachtwoord moet minimaal 6 tekens bevatten.');
    }
    else {
        // Controleer of e-mail al bestaat
        $email_check_query = "SELECT id FROM gebruikers WHERE email = ? LIMIT 1";
        $email_exists = false;
        
        if ($stmt = $conn->prepare($email_check_query)) {
            $stmt->bind_param('s', $email);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    $email_exists = true;
                }
            }
            $stmt->close();
        }
        
        if ($email_exists) {
            // Unhappy scenario: e-mail al in gebruik
            setFlashMessage('error', 'Registratie mislukt: Dit e-mailadres is al geregistreerd.');
        } else {
            // Hash het wachtwoord met BCrypt
            $hashed_password = password_hash($wachtwoord, PASSWORD_BCRYPT);
            $default_role = 'klant';
            
            // Opslaan in database via prepared statement
            $insert_query = "INSERT INTO gebruikers (naam, email, wachtwoord, rol) VALUES (?, ?, ?, ?)";
            if ($stmt = $conn->prepare($insert_query)) {
                $stmt->bind_param('ssss', $naam, $email, $hashed_password, $default_role);
                if ($stmt->execute()) {
                    // Happy scenario
                    setFlashMessage('success', 'Account succesvol aangemaakt! U kunt nu inloggen met uw gegevens.');
                    header('Location: login.php');
                    exit;
                } else {
                    setFlashMessage('error', 'Databasefout: Account kon niet worden opgeslagen.');
                }
                $stmt->close();
            }
        }
    }
}
?>

<div class="auth-wrapper">
    <div class="auth-card" style="max-width: 520px;">
        <h2>Account aanmaken</h2>
        <p class="auth-subtitle">Registreer u als klant van Aurora Theater</p>
        
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="reg-naam">Volledige Naam</label>
                <input type="text" id="reg-naam" name="naam" class="form-control" placeholder="Uw voor- en achternaam" value="<?php echo isset($naam) ? $naam : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="reg-email">E-mailadres</label>
                <input type="email" id="reg-email" name="email" class="form-control" placeholder="naam@voorbeeld.nl" value="<?php echo isset($email) ? $email : ''; ?>" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="reg-password">Wachtwoord</label>
                    <input type="password" id="reg-password" name="wachtwoord" class="form-control" placeholder="Min. 6 tekens" required>
                </div>
                
                <div class="form-group">
                    <label for="reg-password-check">Herhaal Wachtwoord</label>
                    <input type="password" id="reg-password-check" name="wachtwoord_check" class="form-control" placeholder="Herhaal" required>
                </div>
            </div>
            
            <button type="submit" name="action" value="register" class="btn-primary" style="width: 100%; margin-top: 15px;">
                <span>Registreren</span>
            </button>
        </form>
        
        <div class="auth-footer">
            Heeft u al een account? <a href="login.php">Inloggen</a>
        </div>
    </div>
</div>

<?php 
// Footer inladen
include 'includes/footer.php'; 
?>

<?php // Registration validation ?>
