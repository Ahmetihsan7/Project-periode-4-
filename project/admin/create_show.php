<?php
// =============================================
// Admin — Nieuwe show aanmaken
// =============================================
$paginaTitel = 'Nieuwe show';
require_once __DIR__ . '/../includes/functions.php';
vereisRol('admin');

$fouten = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        ':titel'       => trim($_POST['titel'] ?? ''),
        ':beschrijving'=> trim($_POST['beschrijving'] ?? ''),
        ':datum'       => $_POST['datum'] ?? '',
        ':tijd'        => $_POST['tijd'] ?? '',
        ':locatie'     => trim($_POST['locatie'] ?? ''),
        ':capaciteit'  => (int)($_POST['capaciteit'] ?? 0),
        ':prijs'       => (float)($_POST['prijs'] ?? 0),
    ];

    if (strlen($data[':titel']) < 2)    $fouten['titel']      = 'Titel moet minimaal 2 tekens zijn.';
    if (empty($data[':datum']))         $fouten['datum']      = 'Kies een datum.';
    if (empty($data[':tijd']))          $fouten['tijd']       = 'Kies een tijd.';
    if (strlen($data[':locatie']) < 2)  $fouten['locatie']    = 'Locatie is verplicht.';
    if ($data[':capaciteit'] < 1)       $fouten['capaciteit'] = 'Capaciteit moet minimaal 1 zijn.';
    if ($data[':prijs'] < 0)            $fouten['prijs']      = 'Prijs kan niet negatief zijn.';

    if (empty($fouten) && maakShow($data)) {
        $_SESSION['flash'] = ['type' => 'success', 'bericht' => 'Show "' . h($data[':titel']) . '" succesvol aangemaakt!'];
        header('Location: /admin/manage_shows.php');
        exit;
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
            <a href="/admin/create_show.php" class="sidebar-link active">➕ Nieuwe show</a>
            <a href="/admin/manage_employees.php" class="sidebar-link">👥 Medewerkers</a>
            <a href="/admin/create_employee.php" class="sidebar-link">➕ Nieuwe medewerker</a>
        </div>
        <div class="sidebar-section">
            <p class="sidebar-label">Navigatie</p>
            <a href="/index.php" class="sidebar-link">🏠 Homepage</a>
            <a href="/auth/logout.php" class="sidebar-link" style="color:var(--danger);">🚪 Uitloggen</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Nieuwe show aanmaken</h1>
            <p class="page-subtitle">Vul de gegevens in om een nieuwe voorstelling toe te voegen.</p>
        </div>

        <div style="max-width: 640px;">
            <div class="form-card fade-in" style="max-width:100%;">
                <form method="POST" action="/admin/create_show.php">

                    <div class="form-group">
                        <label class="form-label" for="titel">Titel *</label>
                        <input type="text" name="titel" id="titel" class="form-control"
                            value="<?= h($_POST['titel'] ?? '') ?>" placeholder="Naam van de show" required>
                        <?php if (isset($fouten['titel'])): ?><p class="form-error"><?= h($fouten['titel']) ?></p><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="beschrijving">Beschrijving</label>
                        <textarea name="beschrijving" id="beschrijving" class="form-control"
                            rows="4" placeholder="Omschrijving van de show..."
                            style="resize:vertical;"><?= h($_POST['beschrijving'] ?? '') ?></textarea>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label class="form-label" for="datum">Datum *</label>
                            <input type="date" name="datum" id="datum" class="form-control"
                                value="<?= h($_POST['datum'] ?? '') ?>"
                                min="<?= date('Y-m-d') ?>" required>
                            <?php if (isset($fouten['datum'])): ?><p class="form-error"><?= h($fouten['datum']) ?></p><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="tijd">Tijd *</label>
                            <input type="time" name="tijd" id="tijd" class="form-control"
                                value="<?= h($_POST['tijd'] ?? '20:00') ?>" required>
                            <?php if (isset($fouten['tijd'])): ?><p class="form-error"><?= h($fouten['tijd']) ?></p><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="locatie">Locatie *</label>
                        <input type="text" name="locatie" id="locatie" class="form-control"
                            value="<?= h($_POST['locatie'] ?? '') ?>" placeholder="Naam van de zaal / het theater" required>
                        <?php if (isset($fouten['locatie'])): ?><p class="form-error"><?= h($fouten['locatie']) ?></p><?php endif; ?>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label class="form-label" for="capaciteit">Capaciteit *</label>
                            <input type="number" name="capaciteit" id="capaciteit" class="form-control"
                                value="<?= h($_POST['capaciteit'] ?? '100') ?>" min="1" required>
                            <?php if (isset($fouten['capaciteit'])): ?><p class="form-error"><?= h($fouten['capaciteit']) ?></p><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="prijs">Prijs (€) *</label>
                            <input type="number" name="prijs" id="prijs" class="form-control"
                                value="<?= h($_POST['prijs'] ?? '25.00') ?>" min="0" step="0.01" required>
                            <?php if (isset($fouten['prijs'])): ?><p class="form-error"><?= h($fouten['prijs']) ?></p><?php endif; ?>
                        </div>
                    </div>

                    <div style="display:flex; gap:12px; margin-top:8px;">
                        <button type="submit" class="btn btn-primary">✅ Show aanmaken</button>
                        <a href="/admin/manage_shows.php" class="btn btn-secondary">Annuleren</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
