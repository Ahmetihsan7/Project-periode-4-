<?php
$paginaTitel = 'Alle tickets';
require_once __DIR__ . '/../includes/functions.php';
vereisRol('medewerker');

$db = getDB();
$tickets = $db->query(
    'SELECT t.*, s.titel AS show_titel, s.datum, g.naam AS bezoeker_naam
     FROM tickets t
     JOIN shows s ON t.show_id = s.id
     JOIN gebruikers g ON t.gebruiker_id = g.id
     ORDER BY t.gekocht_op DESC'
)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-section">
            <p class="sidebar-label">Medewerker</p>
            <a href="/employee/dashboard.php" class="sidebar-link">📊 Dashboard</a>
            <a href="/employee/check_ticket.php" class="sidebar-link">🔍 Ticket scannen</a>
            <a href="/employee/manage_tickets.php" class="sidebar-link active">🎟️ Alle tickets</a>
        </div>
        <div class="sidebar-section">
            <p class="sidebar-label">Navigatie</p>
            <a href="/index.php" class="sidebar-link">🏠 Homepage</a>
            <a href="/auth/logout.php" class="sidebar-link" style="color:var(--danger);">🚪 Uitloggen</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Alle tickets</h1>
            <p class="page-subtitle"><?= count($tickets) ?> ticket(s) in totaal</p>
        </div>

        <div class="table-wrap fade-in">
            <table class="data-table">
                <thead>
                    <tr><th>#</th><th>Bezoeker</th><th>Show</th><th>Datum</th><th>Aantal</th><th>Prijs</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($tickets)): ?>
                        <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:40px;">Nog geen tickets verkocht.</td></tr>
                    <?php else: foreach ($tickets as $t): ?>
                        <tr>
                            <td style="color:var(--text-muted);"><?= $t['id'] ?></td>
                            <td><?= h($t['bezoeker_naam']) ?></td>
                            <td><?= h($t['show_titel']) ?></td>
                            <td><?= formatDatum($t['datum']) ?></td>
                            <td><?= $t['aantal'] ?>x</td>
                            <td><?= formatPrijs((float)$t['totaalprijs']) ?></td>
                            <td>
                                <?php
                                $badge = match($t['status']) {
                                    'actief'      => 'badge-success',
                                    'gebruikt'    => 'badge-warning',
                                    'geannuleerd' => 'badge-danger',
                                    default       => ''
                                };
                                ?>
                                <span class="badge <?= $badge ?>"><?= ucfirst(h($t['status'])) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
