<?php
// =============================================
// Login pagina
// =============================================
$paginaTitel = 'Inloggen';
require_once __DIR__ . '/../includes/functions.php';

// Al ingelogd? Redirect naar home
if (isIngelogd()) {
    header('Location: /index.php');
    exit;
}

$fout = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email     = trim($_POST['email'] ?? '');
    $wachtwoord = $_POST['wachtwoord'] ?? '';

    if (empty($email) || empty($wachtwoord)) {
        $fout = 'Vul alle velden in.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fout = 'Voer een geldig e-mailadres in.';
    } elseif (loginGebruiker($email, $wachtwoord)) {
        // Redirect op basis van rol
        $redirect = match ($_SESSION['rol']) {
            'admin'      => '/admin/dashboard.php',
            'medewerker' => '/employee/dashboard.php',
            default      => '/index.php',
        };
        header('Location: ' . $redirect);
        exit;
    } else {
        $fout = 'Onjuist e-mailadres of wachtwoord.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main style="min-height: calc(100vh - 68px); display:flex; align-items:center; padding: 48px 24px;">
    <div class="form-card fade-in" style="width:100%;">
        <div style="text-align:center; margin-bottom:28px;">
            <div style="font-size:40px; margin-bottom:12px;">🎭</div>
            <h1 class="form-title">Welkom terug</h1>
            <p class="form-subtitle">Log in op jouw TicketApp account</p>
        </div>

        <?php if ($fout): ?>
            <div class="flash-message flash-error" style="margin-bottom:20px;">
                <?= h($fout) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/auth/login.php" id="loginForm">
            <div class="form-group">
                <label class="form-label" for="email">E-mailadres</label>
                <input
                    type="email" name="email" id="email"
                    class="form-control"
                    placeholder="jouw@email.nl"
                    value="<?= h($_POST['email'] ?? '') ?>"
                    required autocomplete="email">
            </div>
            <div class="form-group">
                <label class="form-label" for="wachtwoord">Wachtwoord</label>
                <input
                    type="password" name="wachtwoord" id="wachtwoord"
                    class="form-control"
                    placeholder="••••••••"
                    required autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:8px;">
                Inloggen →
            </button>
        </form>

        <div class="form-footer">
            Nog geen account?
            <a href="/auth/register.php">Registreer hier gratis</a>
        </div>

        <!-- Testaccounts info -->
        <div style="margin-top:24px; padding:16px; background:var(--bg3); border-radius:10px; border:1px solid var(--border);">
            <p style="font-size:12px; color:var(--text-muted); margin-bottom:8px; font-weight:600; text-transform:uppercase; letter-spacing:1px;">Testaccounts</p>
            <p style="font-size:13px; color:var(--text-muted);">Admin: <span style="color:var(--gold);">admin@ticketapp.nl</span></p>
            <p style="font-size:13px; color:var(--text-muted);">Medewerker: <span style="color:var(--gold);">jan@ticketapp.nl</span></p>
            <p style="font-size:13px; color:var(--text-muted);">Bezoeker: <span style="color:var(--gold);">maria@ticketapp.nl</span></p>
            <p style="font-size:12px; color:var(--text-muted); margin-top:6px;">Wachtwoord: <code style="color:var(--gold);">password</code></p>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
