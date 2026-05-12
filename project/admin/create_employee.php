<?php
$paginaTitel = 'Nieuwe medewerker';
require_once __DIR__ . '/../includes/functions.php';
vereisRol('admin');

$fouten = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam       = trim($_POST['naam'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $wachtwoord = $_POST['wachtwoord'] ?? '';

    if (strlen($naam) < 2)                          $fouten['naam']       = 'Naam moet minimaal 2 tekens zijn.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $fouten['email']      = 'Voer een geldig e-mailadres in.';
    if (strlen($wachtwoord) < 6)                    $fouten['wachtwoord'] = 'Wachtwoord moet minimaal 6 tekens zijn.';

    if (empty($fouten)) {
        try {
            maakMedewerker($naam, $email, $wachtwoord);
            $_SESSION['flash'] = ['type' => 'success', 'bericht' => "Medewerker '$naam' aangemaakt!"];
            header('Location: /admin/manage_employees.php');
            exit;
        } catch (Exception $e) {
            $fouten['email'] = 'Dit e-mailadres is al in gebruik.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-section">
            <p class="sidebar-label">Beheer</p>
            <a href="/admin/dashboard.php" class="sidebar-link">📊 Dashboard</a>
            <a href="/admin/manage_shows.php" class="sidebar-link">🎭 Shows beheren</a>
            <a href="/admin/create_show.php" class="sidebar-link">➕ Nieuwe show</a>
            <a href="/admin/manage_employees.php" class="sidebar-link">👥 Medewerkers</a>
            <a href="/admin/create_employee.php" class="sidebar-link active">➕ Nieuwe medewerker</a>
        </div>
        <div class="sidebar-section">
            <p class="sidebar-label">Navigatie</p>
            <a href="/index.php" class="sidebar-link">🏠 Homepage</a>
            <a href="/auth/logout.php" class="sidebar-link" style="color:var(--danger);">🚪 Uitloggen</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Nieuwe medewerker</h1>
            <p class="page-subtitle">Voeg een medewerker toe die tickets kan scannen.</p>
        </div>

        <div style="max-width:520px;">
            <div class="form-card fade-in" style="max-width:100%;">
                <form method="POST" action="/admin/create_employee.php">
                    <div class="form-group">
                        <label class="form-label" for="naam">Volledige naam *</label>
                        <input type="text" name="naam" id="naam" class="form-control"
                            value="<?= h($_POST['naam'] ?? '') ?>" placeholder="Naam medewerker" required>
                        <?php if (isset($fouten['naam'])): ?><p class="form-error"><?= h($fouten['naam']) ?></p><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">E-mailadres *</label>
                        <input type="email" name="email" id="email" class="form-control"
                            value="<?= h($_POST['email'] ?? '') ?>" placeholder="medewerker@bedrijf.nl" required>
                        <?php if (isset($fouten['email'])): ?><p class="form-error"><?= h($fouten['email']) ?></p><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="wachtwoord">Wachtwoord *</label>
                        <input type="password" name="wachtwoord" id="wachtwoord" class="form-control"
                            placeholder="Minimaal 6 tekens" required>
                        <?php if (isset($fouten['wachtwoord'])): ?><p class="form-error"><?= h($fouten['wachtwoord']) ?></p><?php endif; ?>
                    </div>
                    <div style="display:flex;gap:12px;margin-top:8px;">
                        <button type="submit" class="btn btn-primary">✅ Medewerker aanmaken</button>
                        <a href="/admin/manage_employees.php" class="btn btn-secondary">Annuleren</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
