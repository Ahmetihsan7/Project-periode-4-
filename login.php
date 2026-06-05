<?php
/**
 * Inlogpagina - Aurora Theater
 * 
 * Verwerkt gebruikerslogin voor klanten, medewerkers en admins.
 */

// Paginatitel instellen
$page_title = "Inloggen";

// Header inladen (dit laadt automatisch db.php en functions.php in)
include 'includes/header.php';

// Redirect als gebruiker al is ingelogd
if (isLoggedIn()) {
    if (hasRole(['admin', 'medewerker'])) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

// Login verwerking (Happy/Unhappy Scenario)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = sanitize($_POST['email'] ?? '');
    $wachtwoord = $_POST['wachtwoord'] ?? ''; // Wachtwoord niet trimmen/escapen voor password_verify
    
    // Unhappy scenario: lege velden
    if (empty($email) || empty($wachtwoord)) {
        setFlashMessage('error', 'Inloggen mislukt: Vul a.u.b. alle velden in.');
    } else {
        // prepared statement gebruiken
        $query = "SELECT * FROM gebruikers WHERE email = ? LIMIT 1";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param('s', $email);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    
                    // Wachtwoord controleren via bcrypt hash
                    if (password_verify($wachtwoord, $user['wachtwoord'])) {
                        // Happy scenario: login succesvol
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['naam'];
                        $_SESSION['user_role'] = $user['rol'];
                        
                        setFlashMessage('success', 'Welkom terug, ' . $user['naam'] . '! U bent succesvol ingelogd.');
                        
                        // Redirect op basis van rol
                        if (in_array($user['rol'], ['admin', 'medewerker'])) {
                            header('Location: admin/dashboard.php');
                        } else {
                            header('Location: index.php');
                        }
                        exit;
                    } else {
                        // Unhappy scenario: verkeerd wachtwoord
                        setFlashMessage('error', 'Inloggen mislukt: E-mailadres of wachtwoord is onjuist.');
                    }
                } else {
                    // Unhappy scenario: e-mailadres niet gevonden
                    setFlashMessage('error', 'Inloggen mislukt: E-mailadres of wachtwoord is onjuist.');
                }
            } else {
                setFlashMessage('error', 'Databasefout: Kan inlogverzoek niet verwerken.');
            }
            $stmt->close();
        }
    }
}
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Inloggen</h2>
        <p class="auth-subtitle">Toegang tot uw Aurora Theater account</p>
        
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="login-email">E-mailadres</label>
                <input type="email" id="login-email" name="email" class="form-control" placeholder="naam@voorbeeld.nl" required>
            </div>
            
            <div class="form-group" style="margin-bottom: 25px;">
                <label for="login-password">Wachtwoord</label>
                <input type="password" id="login-password" name="wachtwoord" class="form-control" placeholder="••••••••" required>
            </div>
            
            <button type="submit" name="action" value="login" class="btn-primary" style="width: 100%;">
                <span>Inloggen</span>
            </button>
        </form>
        
        <div class="auth-footer">
            Nog geen account? <a href="register.php">Registreren</a>
        </div>
    </div>
</div>

<?php 
// Footer inladen
include 'includes/footer.php'; 
?>

<?php // Login improvements ?>
