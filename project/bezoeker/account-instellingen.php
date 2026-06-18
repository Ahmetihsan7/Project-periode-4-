<?php
// =============================================
// Bezoeker — Account instellingen
// =============================================
$paginaTitel = 'Account Instellingen';
require_once __DIR__ . '/../includes/functions.php';
vereisLogin();

$gebruikerId = $_SESSION['gebruiker_id'];
$fouten      = [];
$succes      = '';

// Wachtwoord wijzigen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actie']) && $_POST['actie'] === 'wachtwoord') {
    $huidig   = $_POST['huidig_wachtwoord']   ?? '';
    $nieuw    = $_POST['nieuw_wachtwoord']     ?? '';
    $bevestig = $_POST['bevestig_wachtwoord']  ?? '';

    // Haal huidige hash op
    $db   = getDB();
    $stmt = $db->prepare('SELECT wachtwoord FROM gebruikers WHERE id = :id');
    $stmt->execute([':id' => $gebruikerId]);
    $rij  = $stmt->fetch();

    if (!password_verify($huidig, $rij['wachtwoord'])) {
        $fouten['huidig'] = 'Huidig wachtwoord is onjuist.';
    } elseif (strlen($nieuw) < 8) {
        $fouten['nieuw'] = 'Nieuw wachtwoord moet minimaal 8 tekens zijn.';
    } elseif ($nieuw !== $bevestig) {
        $fouten['bevestig'] = 'Wachtwoorden komen niet overeen.';
    } else {
        $hash = password_hash($nieuw, PASSWORD_DEFAULT);
        $stmt = $db->prepare('UPDATE gebruikers SET wachtwoord = :ww WHERE id = :id');
        $stmt->execute([':ww' => $hash, ':id' => $gebruikerId]);
        $succes = 'Wachtwoord succesvol gewijzigd!';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main style="min-height: calc(100vh - 68px); padding: 48px 24px;">
    <div style="max-width: 680px; margin: 0 auto;">

        <div class="page-header fade-in">
            <h1 class="page-title">⚙️ Account instellingen</h1>
            <p class="page-subtitle">Beheer jouw accountgegevens en beveiliging</p>
        </div>

        <?php if ($succes): ?>
            <div class="flash-message flash-success" style="margin-bottom:24px;">
                <?= h($succes) ?>
            </div>
        <?php endif; ?>

        <!-- Wachtwoord wijzigen -->
        <div class="form-card fade-in" style="margin-bottom:24px;">
            <h2 style="font-size:18px; font-weight:700; margin-bottom:20px;">🔒 Wachtwoord wijzigen</h2>

            <form method="POST" action="/bezoeker/account-instellingen.php">
                <input type="hidden" name="actie" value="wachtwoord">

                <div class="form-group">
                    <label class="form-label" for="huidig_wachtwoord">Huidig wachtwoord</label>
                    <input type="password" name="huidig_wachtwoord" id="huidig_wachtwoord"
                        class="form-control <?= isset($fouten['huidig']) ? 'invalid' : '' ?>"
                        placeholder="••••••••" required>
                    <?php if (isset($fouten['huidig'])): ?>
                        <p class="form-error"><?= h($fouten['huidig']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nieuw_wachtwoord">Nieuw wachtwoord</label>
                    <input type="password" name="nieuw_wachtwoord" id="nieuw_wachtwoord"
                        class="form-control <?= isset($fouten['nieuw']) ? 'invalid' : '' ?>"
                        placeholder="Minimaal 8 tekens" required minlength="8">
                    <?php if (isset($fouten['nieuw'])): ?>
                        <p class="form-error"><?= h($fouten['nieuw']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="bevestig_wachtwoord">Bevestig nieuw wachtwoord</label>
                    <input type="password" name="bevestig_wachtwoord" id="bevestig_wachtwoord"
                        class="form-control <?= isset($fouten['bevestig']) ? 'invalid' : '' ?>"
                        placeholder="Herhaal nieuw wachtwoord" required>
                    <?php if (isset($fouten['bevestig'])): ?>
                        <p class="form-error"><?= h($fouten['bevestig']) ?></p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary">🔒 Wachtwoord wijzigen</button>
            </form>
        </div>

        <!-- Accountgegevens -->
        <div class="form-card fade-in">
            <h2 style="font-size:18px; font-weight:700; margin-bottom:16px;">ℹ️ Accountinformatie</h2>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <div style="display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid var(--border);">
                    <span style="color:var(--text-muted);">Naam</span>
                    <span style="font-weight:600;"><?= h($_SESSION['naam']) ?></span>
                </div>
                <div style="display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid var(--border);">
                    <span style="color:var(--text-muted);">E-mailadres</span>
                    <span style="font-weight:600;"><?= h($_SESSION['email']) ?></span>
                </div>
                <div style="display:flex; justify-content:space-between; padding:12px 0;">
                    <span style="color:var(--text-muted);">Rol</span>
                    <span style="font-weight:600; color:var(--gold); text-transform:uppercase;"><?= h($_SESSION['rol']) ?></span>
                </div>
            </div>
        </div>

        <div style="margin-top:24px; display:flex; gap:12px;">
            <a href="/bezoeker/profiel.php" class="btn btn-secondary">← Terug naar profiel</a>
        </div>
    </div>
</main>

<style>
.form-control.invalid { border-color: var(--danger); }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
