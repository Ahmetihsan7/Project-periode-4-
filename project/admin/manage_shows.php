<?php
// =============================================
// Admin — Shows beheren
// =============================================
$paginaTitel = 'Shows beheren';
require_once __DIR__ . '/../includes/functions.php';
vereisRol('admin');

// Verwijder show via GET
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    verwijderShow((int)$_GET['delete']);
    $_SESSION['flash'] = ['type' => 'success', 'bericht' => 'Show succesvol verwijderd.'];
    header('Location: /admin/manage_shows.php');
    exit;
}

$shows = getAlleShows();
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-section">
            <p class="sidebar-label">Beheer</p>
            <a href="/admin/dashboard.php" class="sidebar-link">📊 Dashboard</a>
            <a href="/admin/manage_shows.php" class="sidebar-link active">🎭 Shows beheren</a>
            <a href="/admin/create_show.php" class="sidebar-link">➕ Nieuwe show</a>
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
        <div class="page-header" style="display:flex; align-items:center; justify-content:space-between;">
            <div>
                <h1 class="page-title">Shows beheren</h1>
                <p class="page-subtitle"><?= count($shows) ?> show(s) in totaal</p>
            </div>
            <a href="/admin/create_show.php" class="btn btn-primary">➕ Nieuwe show</a>
        </div>

        <div class="table-wrap fade-in">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Titel</th>
                        <th>Datum & Tijd</th>
                        <th>Locatie</th>
                        <th>Capaciteit</th>
                        <th>Prijs</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($shows)): ?>
                        <tr><td colspan="7" style="text-align:center; color:var(--text-muted); padding:40px;">
                            Geen shows gevonden. <a href="/admin/create_show.php">Maak de eerste show aan.</a>
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($shows as $show): ?>
                            <tr>
                                <td style="color:var(--text-muted);"><?= $show['id'] ?></td>
                                <td><strong><?= h($show['titel']) ?></strong></td>
                                <td><?= formatDatum($show['datum']) ?> · <?= substr($show['tijd'], 0, 5) ?></td>
                                <td><?= h($show['locatie']) ?></td>
                                <td><?= $show['capaciteit'] ?> pers.</td>
                                <td><?= formatPrijs((float)$show['prijs']) ?></td>
                                <td>
                                    <a href="/admin/manage_shows.php?delete=<?= $show['id'] ?>"
                                       class="btn btn-danger btn-sm"
                                       data-confirm="Weet je zeker dat je '<?= h($show['titel']) ?>' wilt verwijderen?">
                                        🗑️ Verwijder
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
