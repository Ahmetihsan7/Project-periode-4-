<?php
$paginaTitel = 'Medewerker Dashboard';
require_once __DIR__ . '/../includes/functions.php';
vereisRol('medewerker');

$db = getDB();
$aantalGebruikt  = $db->query("SELECT COUNT(*) FROM tickets WHERE status='gebruikt'")->fetchColumn();
$aantalActief    = $db->query("SELECT COUNT(*) FROM tickets WHERE status='actief'")->fetchColumn();
$aankomendShows  = getAankomendShows(5);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-section">
            <p class="sidebar-label">Medewerker</p>
            <a href="/employee/dashboard.php" class="sidebar-link active">📊 Dashboard</a>
            <a href="/employee/check_ticket.php" class="sidebar-link">🔍 Ticket scannen</a>
            <a href="/employee/manage_tickets.php" class="sidebar-link">🎟️ Alle tickets</a>
        </div>
        <div class="sidebar-section">
            <p class="sidebar-label">Navigatie</p>
            <a href="/index.php" class="sidebar-link">🏠 Homepage</a>
            <a href="/auth/logout.php" class="sidebar-link" style="color:var(--danger);">🚪 Uitloggen</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Welkom, <?= h($_SESSION['naam']) ?>!</h1>
            <p class="page-subtitle">Medewerker overzicht — scan tickets en bekijk de agenda.</p>
        </div>

        <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom:40px;">
            <div class="stat-card fade-in">
                <div class="stat-icon">✅</div>
                <div class="stat-value" data-teller="<?= (int)$aantalGebruikt ?>"><?= $aantalGebruikt ?></div>
                <div class="stat-label">Tickets gebruikt</div>
            </div>
            <div class="stat-card fade-in fade-in-delay-1">
                <div class="stat-icon">🎟️</div>
                <div class="stat-value" data-teller="<?= (int)$aantalActief ?>"><?= $aantalActief ?></div>
                <div class="stat-label">Actieve tickets</div>
            </div>
            <div class="stat-card fade-in fade-in-delay-2">
                <div class="stat-icon">📅</div>
                <div class="stat-value" data-teller="<?= count($aankomendShows) ?>"><?= count($aankomendShows) ?></div>
                <div class="stat-label">Aankomende shows</div>
            </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <h2 style="font-size:20px;font-weight:700;">Aankomende shows</h2>
            <a href="/employee/check_ticket.php" class="btn btn-primary btn-sm">🔍 Ticket scannen</a>
        </div>

        <div class="table-wrap fade-in">
            <table class="data-table">
                <thead>
                    <tr><th>Show</th><th>Datum</th><th>Tijd</th><th>Locatie</th><th>Prijs</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($aankomendShows)): ?>
                        <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:32px;">Geen shows.</td></tr>
                    <?php else: foreach ($aankomendShows as $show): ?>
                        <tr>
                            <td><strong><?= h($show['titel']) ?></strong></td>
                            <td><?= formatDatum($show['datum']) ?></td>
                            <td><?= substr($show['tijd'], 0, 5) ?></td>
                            <td><?= h($show['locatie']) ?></td>
                            <td><?= formatPrijs((float)$show['prijs']) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
