<?php
// =============================================
// Admin Dashboard
// =============================================
$paginaTitel = 'Admin Dashboard';
require_once __DIR__ . '/../includes/functions.php';
vereisRol('admin');

$stats = getDashboardStats();
$recenteShows = getAankomendShows(5);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-section">
            <p class="sidebar-label">Beheer</p>
            <a href="/admin/dashboard.php" class="sidebar-link active">📊 Dashboard</a>
            <a href="/admin/manage_shows.php" class="sidebar-link">🎭 Shows beheren</a>
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

    <!-- Hoofdinhoud -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welkom terug, <?= h($_SESSION['naam']) ?>!</p>
        </div>

        <!-- Statistieken -->
        <div class="stats-grid">
            <div class="stat-card fade-in">
                <div class="stat-icon">🎭</div>
                <div class="stat-value" data-teller="<?= (int)$stats['totaal_shows'] ?>">0</div>
                <div class="stat-label">Totaal shows</div>
            </div>
            <div class="stat-card fade-in fade-in-delay-1">
                <div class="stat-icon">🎟️</div>
                <div class="stat-value" data-teller="<?= (int)$stats['totaal_tickets'] ?>">0</div>
                <div class="stat-label">Verkochte tickets</div>
            </div>
            <div class="stat-card fade-in fade-in-delay-1">
                <div class="stat-icon">👥</div>
                <div class="stat-value" data-teller="<?= (int)$stats['totaal_bezoekers'] ?>">0</div>
                <div class="stat-label">Bezoekers</div>
            </div>
            <div class="stat-card fade-in fade-in-delay-2">
                <div class="stat-icon">💰</div>
                <div class="stat-value" data-teller="<?= (int)$stats['totaal_omzet'] ?>" data-prefix="€ ">€ 0</div>
                <div class="stat-label">Totale omzet</div>
            </div>
            <div class="stat-card fade-in fade-in-delay-2">
                <div class="stat-icon">📅</div>
                <div class="stat-value" data-teller="<?= (int)$stats['aankomende_shows'] ?>">0</div>
                <div class="stat-label">Aankomende shows</div>
            </div>
        </div>

        <!-- Recente shows -->
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
            <h2 style="font-size:20px; font-weight:700;">Aankomende shows</h2>
            <a href="/admin/create_show.php" class="btn btn-primary btn-sm">➕ Nieuwe show</a>
        </div>

        <div class="table-wrap fade-in">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Titel</th>
                        <th>Datum</th>
                        <th>Locatie</th>
                        <th>Prijs</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recenteShows)): ?>
                        <tr><td colspan="5" style="text-align:center; color:var(--text-muted); padding:32px;">Geen shows gevonden.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recenteShows as $show): ?>
                            <tr>
                                <td><strong><?= h($show['titel']) ?></strong></td>
                                <td><?= formatDatum($show['datum']) ?></td>
                                <td><?= h($show['locatie']) ?></td>
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
        <div style="text-align:right; margin-top:16px;">
            <a href="/admin/manage_shows.php" class="btn btn-secondary btn-sm">Alle shows bekijken →</a>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
