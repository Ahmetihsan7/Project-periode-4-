<?php
// =============================================
// Registreer pagina
// =============================================
$paginaTitel = 'Registreren';
require_once __DIR__ . '/../includes/functions.php';

if (isIngelogd()) { header('Location: /index.php'); exit; }

$fouten = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam       = trim($_POST['naam'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $wachtwoord = $_POST['wachtwoord'] ?? '';
    $bevestig   = $_POST['bevestig'] ?? '';

    if (strlen($naam) < 2)                         $fouten['naam']       = 'Naam moet minimaal 2 tekens zijn.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $fouten['email']      = 'Voer een geldig e-mailadres in.';
    if (strlen($wachtwoord) < 8)                   $fouten['wachtwoord'] = 'Wachtwoord moet minimaal 8 tekens zijn.';
    if ($wachtwoord !== $bevestig)                 $fouten['bevestig']   = 'Wachtwoorden komen niet overeen.';

    if (empty($fouten)) {
        try {
            if (registreerGebruiker($naam, $email, $wachtwoord)) {
                $_SESSION['flash'] = ['type' => 'success', 'bericht' => 'Account aangemaakt! Je kunt nu inloggen.'];
                header('Location: /auth/login.php');
                exit;
            }
        } catch (Exception $e) {
            $fouten['email'] = 'Dit e-mailadres is al in gebruik.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main style="min-height: calc(100vh - 68px); display:flex; align-items:center; padding: 48px 24px;">
    <div class="form-card fade-in" style="width:100%;">
        <div style="text-align:center; margin-bottom:28px;">
            <div style="font-size:40px; margin-bottom:12px;">🎉</div>
            <h1 class="form-title">Account aanmaken</h1>
            <p class="form-subtitle">Gratis registreren en direct tickets kopen</p>
        </div>

        <form method="POST" action="/auth/register.php" id="registerForm">
            <div class="form-group">
                <label class="form-label" for="naam">Volledige naam</label>
                <input type="text" name="naam" id="naam" class="form-control <?= isset($fouten['naam']) ? 'invalid' : '' ?>"
                    placeholder="Jan de Vries" value="<?= h($_POST['naam'] ?? '') ?>" required>
                <?php if (isset($fouten['naam'])): ?>
                    <p class="form-error"><?= h($fouten['naam']) ?></p>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label" for="email">E-mailadres</label>
                <input type="email" name="email" id="email" class="form-control <?= isset($fouten['email']) ? 'invalid' : '' ?>"
                    placeholder="jouw@email.nl" value="<?= h($_POST['email'] ?? '') ?>" required>
                <?php if (isset($fouten['email'])): ?>
                    <p class="form-error"><?= h($fouten['email']) ?></p>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label" for="wachtwoord">Wachtwoord</label>
                <input type="password" name="wachtwoord" id="wachtwoord" class="form-control <?= isset($fouten['wachtwoord']) ? 'invalid' : '' ?>"
                    placeholder="Minimaal 8 tekens" required minlength="8">
                <!-- Wachtwoord sterkte indicator -->
                <div id="sterkteBar" style="height:4px; border-radius:4px; margin-top:8px; background:var(--bg3); overflow:hidden;">
                    <div id="sterkteFill" style="height:100%; width:0%; border-radius:4px; transition:width 0.3s, background 0.3s;"></div>
                </div>
                <p id="sterkteText" style="font-size:12px; color:var(--text-muted); margin-top:4px;"></p>
                <?php if (isset($fouten['wachtwoord'])): ?>
                    <p class="form-error"><?= h($fouten['wachtwoord']) ?></p>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label" for="bevestig">Wachtwoord bevestigen</label>
                <input type="password" name="bevestig" id="bevestig" class="form-control <?= isset($fouten['bevestig']) ? 'invalid' : '' ?>"
                    placeholder="Herhaal wachtwoord" required>
                <?php if (isset($fouten['bevestig'])): ?>
                    <p class="form-error"><?= h($fouten['bevestig']) ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:8px;">
                Account aanmaken →
            </button>
        </form>

        <div class="form-footer">
            Al een account? <a href="/auth/login.php">Inloggen</a>
        </div>
    </div>
</main>

<!-- Inline CSS voor invalid veld -->
<style>
.form-control.invalid { border-color: var(--danger); }
</style>

<script>
// Wachtwoord sterkte checker
document.getElementById('wachtwoord').addEventListener('input', function() {
    const val = this.value;
    const fill = document.getElementById('sterkteFill');
    const text = document.getElementById('sterkteText');

    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val))  score++;
    if (/[0-9]/.test(val))  score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const niveaus = [
        { label: '', kleur: 'transparent', breedte: '0%' },
        { label: 'Erg zwak', kleur: '#e74c3c', breedte: '20%' },
        { label: 'Zwak', kleur: '#e67e22', breedte: '40%' },
        { label: 'Redelijk', kleur: '#f1c40f', breedte: '60%' },
        { label: 'Sterk', kleur: '#2ecc71', breedte: '80%' },
        { label: 'Zeer sterk', kleur: '#27ae60', breedte: '100%' },
    ];

    const niveau = niveaus[score] || niveaus[0];
    fill.style.width = val.length > 0 ? niveau.breedte : '0%';
    fill.style.background = niveau.kleur;
    text.textContent = val.length > 0 ? 'Sterkte: ' + niveau.label : '';
    text.style.color = niveau.kleur;
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
