<?php
// =============================================
// Admin — Statistieken overzicht
// =============================================
$paginaTitel = 'Statistieken';
require_once __DIR__ . '/../includes/functions.php';
vereisRol('admin');

$db = getDB();

// Tickets per show
$ticketsPerShow = $db->query(
    'SELECT s.titel, COUNT(t.id) AS aantal, COALESCE(SUM(t.totaalprijs),0) AS omzet
     FROM shows s
     LEFT JOIN tickets t ON s.id = t.show_id
     GROUP BY s.id, s.titel
     ORDER BY aantal DESC'
)->fetchAll();

// Registraties per maand
$registratiesPerMaand = $db->query(
    "SELECT DATE_FORMAT(aangemaakt_op, '%Y-%m') AS maand, COUNT(*) AS aantal
     FROM gebruikers
     WHERE rol = 'bezoeker'
     GROUP BY maand
     ORDER BY maand DESC
     LIMIT 6"
)->fetchAll();

// Totale omzet
$totaalOmzet = $db->query("SELECT COALESCE(SUM(totaalprijs),0) FROM tickets WHERE status='actief'")->fetchColumn();

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
            <a href="/admin/statistieken.php" class="sidebar-link active">📈 Statistieken</a>
        </div>
        <div class="sidebar-section">
            <p class="sidebar-label">Navigatie</p>
            <a href="/index.php" class="sidebar-link">🏠 Homepage</a>
            <a href="/auth/logout.php" class="sidebar-link" style="color:var(--danger);">🚪 Uitloggen</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">📈 Statistieken</h1>
            <p class="page-subtitle">Overzicht van verkopen en registraties</p>
        </div>

        <!-- Totale omzet banner -->
        <div class="stat-card fade-in" style="margin-bottom:32px; flex-direction:row; align-items:center; justify-content:space-between; padding:24px 32px;">
            <div>
                <div class="stat-label" style="margin-bottom:6px;">💰 Totale omzet (actieve tickets)</div>
                <div style="font-size:36px; font-weight:800; color:var(--gold);"><?= formatPrijs((float)$totaalOmzet) ?></div>
            </div>
            <div style="font-size:60px; opacity:0.2;">💰</div>
        </div>

        <!-- Tickets per show -->
        <h2 style="font-size:20px; font-weight:700; margin-bottom:16px;">🎭 Tickets per show</h2>
        <div class="table-wrap fade-in" style="margin-bottom:40px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Show</th>
                        <th>Tickets verkocht</th>
                        <th>Omzet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ticketsPerShow)): ?>
                        <tr><td colspan="3" style="text-align:center; color:var(--text-muted); padding:24px;">Geen data beschikbaar.</td></tr>
                    <?php else: ?>
                        <?php foreach ($ticketsPerShow as $rij): ?>
                            <tr>
                                <td><strong><?= h($rij['titel']) ?></strong></td>
                                <td><?= (int)$rij['aantal'] ?></td>
                                <td><?= formatPrijs((float)$rij['omzet']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Registraties per maand -->
        <h2 style="font-size:20px; font-weight:700; margin-bottom:16px;">📅 Nieuwe bezoekers per maand</h2>
        <div class="table-wrap fade-in">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Maand</th>
                        <th>Nieuwe registraties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registratiesPerMaand)): ?>
                        <tr><td colspan="2" style="text-align:center; color:var(--text-muted); padding:24px;">Geen data beschikbaar.</td></tr>
                    <?php else: ?>
                        <?php foreach ($registratiesPerMaand as $rij): ?>
                            <tr>
                                <td><?= h($rij['maand']) ?></td>
                                <td><?= (int)$rij['aantal'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
