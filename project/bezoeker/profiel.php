<?php
// =============================================
// Bezoeker — Profielpagina
// =============================================
$paginaTitel = 'Mijn Profiel';
require_once __DIR__ . '/../includes/functions.php';
vereisLogin();

$gebruikerId = $_SESSION['gebruiker_id'];
$fouten = [];
$succes = '';

// Wijzig naam verwerken
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nieuweNaam = trim($_POST['naam'] ?? '');

    if (strlen($nieuweNaam) < 2) {
        $fouten['naam'] = 'Naam moet minimaal 2 tekens zijn.';
    } else {
        $db = getDB();
        $stmt = $db->prepare('UPDATE gebruikers SET naam = :naam WHERE id = :id');
        if ($stmt->execute([':naam' => $nieuweNaam, ':id' => $gebruikerId])) {
            $_SESSION['naam'] = $nieuweNaam;
            $succes = 'Profiel succesvol bijgewerkt!';
        }
    }
}

// Haal huidige gebruikersgegevens op
$db = getDB();
$stmt = $db->prepare('SELECT * FROM gebruikers WHERE id = :id');
$stmt->execute([':id' => $gebruikerId]);
$gebruiker = $stmt->fetch();

// Haal ticketstatistieken op
$tickets = getTicketsVanGebruiker($gebruikerId);
$actieveTickets  = array_filter($tickets, fn($t) => $t['status'] === 'actief');
$gebruikteTickets = array_filter($tickets, fn($t) => $t['status'] === 'gebruikt');

require_once __DIR__ . '/../includes/header.php';
?>

<main style="min-height: calc(100vh - 68px); padding: 48px 24px;">
    <div style="max-width: 720px; margin: 0 auto;">

        <!-- Profielkop -->
        <div class="fade-in" style="text-align:center; margin-bottom:40px;">
            <div style="
                width: 90px; height: 90px; border-radius: 50%;
                background: var(--gold-gradient);
                display: flex; align-items: center; justify-content: center;
                font-size: 36px; font-weight: 700; color: #000;
                margin: 0 auto 16px;
            ">
                <?= strtoupper(substr($gebruiker['naam'], 0, 1)) ?>
            </div>
            <h1 style="font-size:24px; font-weight:700; margin-bottom:4px;"><?= h($gebruiker['naam']) ?></h1>
            <p style="color:var(--text-muted);"><?= h($gebruiker['email']) ?></p>
            <span style="
                display:inline-block; margin-top:8px; padding:4px 14px;
                background:var(--bg3); border:1px solid var(--border);
                border-radius:20px; font-size:12px; color:var(--gold);
                text-transform:uppercase; letter-spacing:1px;
            "><?= h($gebruiker['rol']) ?></span>
        </div>

        <!-- Statistieken -->
        <div class="stats-grid fade-in" style="grid-template-columns: repeat(3, 1fr); margin-bottom:32px;">
            <div class="stat-card">
                <div class="stat-icon">🎟️</div>
                <div class="stat-value"><?= count($tickets) ?></div>
                <div class="stat-label">Totaal tickets</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-value"><?= count($actieveTickets) ?></div>
                <div class="stat-label">Actieve tickets</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-value"><?= count($gebruikteTickets) ?></div>
                <div class="stat-label">Gebruikte tickets</div>
            </div>
        </div>

        <!-- Profiel bewerken -->
        <div class="form-card fade-in" style="margin-bottom:24px;">
            <h2 style="font-size:18px; font-weight:700; margin-bottom:20px;">✏️ Profiel bewerken</h2>

            <?php if ($succes): ?>
                <div class="flash-message flash-success" style="margin-bottom:16px;">
                    <?= h($succes) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/bezoeker/profiel.php">
                <div class="form-group">
                    <label class="form-label" for="naam">Volledige naam</label>
                    <input
                        type="text" name="naam" id="naam"
                        class="form-control <?= isset($fouten['naam']) ? 'invalid' : '' ?>"
                        value="<?= h($gebruiker['naam']) ?>" required>
                    <?php if (isset($fouten['naam'])): ?>
                        <p class="form-error"><?= h($fouten['naam']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">E-mailadres</label>
                    <input
                        type="email" class="form-control"
                        value="<?= h($gebruiker['email']) ?>"
                        disabled style="opacity:0.6; cursor:not-allowed;">
                    <p style="font-size:12px; color:var(--text-muted); margin-top:4px;">E-mailadres kan niet worden gewijzigd.</p>
                </div>

                <button type="submit" class="btn btn-primary">
                    💾 Opslaan
                </button>
            </form>
        </div>

        <!-- Snelkoppelingen -->
        <div style="display:flex; gap:12px; flex-wrap:wrap;">
            <a href="/bezoeker/mijn-tickets.php" class="btn btn-secondary">🎟️ Mijn tickets bekijken</a>
            <a href="/shows.php" class="btn btn-secondary">🎭 Shows bekijken</a>
            <a href="/auth/logout.php" class="btn btn-danger btn-sm" style="margin-left:auto;">🚪 Uitloggen</a>
        </div>

    </div>
</main>

<style>
.form-control.invalid { border-color: var(--danger); }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
