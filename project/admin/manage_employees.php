<?php
$paginaTitel = 'Medewerkers beheren';
require_once __DIR__ . '/../includes/functions.php';
vereisRol('admin');

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM gebruikers WHERE id = :id AND rol = 'medewerker'");
    $stmt->execute([':id' => (int)$_GET['delete']]);
    $_SESSION['flash'] = ['type' => 'success', 'bericht' => 'Medewerker verwijderd.'];
    header('Location: /admin/manage_employees.php');
    exit;
}

$medewerkers = getAlleMedewerkers();
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-section">
            <p class="sidebar-label">Beheer</p>
            <a href="/admin/dashboard.php" class="sidebar-link">📊 Dashboard</a>
            <a href="/admin/manage_shows.php" class="sidebar-link">🎭 Shows beheren</a>
            <a href="/admin/create_show.php" class="sidebar-link">➕ Nieuwe show</a>
            <a href="/admin/manage_employees.php" class="sidebar-link active">👥 Medewerkers</a>
            <a href="/admin/create_employee.php" class="sidebar-link">➕ Nieuwe medewerker</a>
        </div>
        <div class="sidebar-section">
            <p class="sidebar-label">Navigatie</p>
            <a href="/index.php" class="sidebar-link">🏠 Homepage</a>
            <a href="/auth/logout.php" class="sidebar-link" style="color:var(--danger);">🚪 Uitloggen</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;">
            <div>
                <h1 class="page-title">Medewerkers</h1>
                <p class="page-subtitle"><?= count($medewerkers) ?> medewerker(s)</p>
            </div>
            <a href="/admin/create_employee.php" class="btn btn-primary">➕ Nieuwe medewerker</a>
        </div>

        <div class="table-wrap fade-in">
            <table class="data-table">
                <thead>
                    <tr><th>#</th><th>Naam</th><th>E-mail</th><th>Aangemaakt op</th><th>Acties</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($medewerkers)): ?>
                        <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:40px;">
                            Geen medewerkers. <a href="/admin/create_employee.php">Voeg er een toe.</a>
                        </td></tr>
                    <?php else: foreach ($medewerkers as $m): ?>
                        <tr>
                            <td style="color:var(--text-muted);"><?= $m['id'] ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div class="user-avatar" style="width:32px;height:32px;font-size:13px;">
                                        <?= strtoupper(substr($m['naam'], 0, 1)) ?>
                                    </div>
                                    <strong><?= h($m['naam']) ?></strong>
                                </div>
                            </td>
                            <td><?= h($m['email']) ?></td>
                            <td><?= date('d-m-Y', strtotime($m['aangemaakt_op'])) ?></td>
                            <td>
                                <a href="/admin/manage_employees.php?delete=<?= $m['id'] ?>"
                                   class="btn btn-danger btn-sm"
                                   data-confirm="Weet je zeker dat je <?= h($m['naam']) ?> wilt verwijderen?">
                                    🗑️ Verwijder
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
