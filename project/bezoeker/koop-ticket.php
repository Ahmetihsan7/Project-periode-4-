<?php
$paginaTitel = 'Ticket kopen';
require_once __DIR__ . '/../includes/functions.php';
vereisLogin();

$showId = (int)($_GET['show'] ?? 0);
$show   = $showId ? getShowById($showId) : false;

if (!$show) {
    header('Location: /shows.php');
    exit;
}

$fout = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aantal = (int)($_POST['aantal'] ?? 1);

    if ($aantal < 1 || $aantal > 10) {
        $fout = 'Kies tussen 1 en 10 tickets.';
    } elseif (koopTicket((int)$_SESSION['gebruiker_id'], $showId, $aantal)) {
        $_SESSION['flash'] = [
            'type'    => 'success',
            'bericht' => "Gefeliciteerd! Je hebt $aantal ticket(s) voor '{$show['titel']}' gekocht."
        ];
        header('Location: /bezoeker/mijn-tickets.php');
        exit;
    } else {
        $fout = 'Er ging iets mis. Probeer het opnieuw.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<section style="min-height:calc(100vh - 68px); display:flex; align-items:center; padding:64px 24px;">
    <div style="max-width:600px; margin:0 auto; width:100%;">

        <!-- Show preview -->
        <div class="fade-in" style="background:var(--surface); border:1px solid var(--border);
                                     border-radius:var(--radius-lg); padding:32px; margin-bottom:24px;">
            <div style="display:flex; gap:20px; align-items:flex-start;">
                <div style="width:64px;height:64px;border-radius:14px;background:rgba(240,180,41,0.12);
                            display:flex;align-items:center;justify-content:center;font-size:32px;flex-shrink:0;">
                    🎭
                </div>
                <div>
                    <h1 style="font-size:22px; font-weight:700; margin-bottom:8px;"><?= h($show['titel']) ?></h1>
                    <p style="font-size:14px; color:var(--text-muted);">📅 <?= formatDatum($show['datum']) ?> &nbsp;·&nbsp; 🕐 <?= substr($show['tijd'], 0, 5) ?></p>
                    <p style="font-size:14px; color:var(--text-muted);">📍 <?= h($show['locatie']) ?></p>
                    <?php if ($show['beschrijving']): ?>
                        <p style="font-size:13px; color:var(--text-muted); margin-top:10px; line-height:1.7;">
                            <?= h($show['beschrijving']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Koop formulier -->
        <div class="form-card fade-in" style="max-width:100%;">
            <h2 class="form-title" style="font-size:22px; margin-bottom:6px;">Ticket kopen</h2>
            <p class="form-subtitle">Prijs per ticket: <strong style="color:var(--gold);"><?= formatPrijs((float)$show['prijs']) ?></strong></p>

            <?php if ($fout): ?>
                <div class="flash-message flash-error" style="margin-bottom:20px;"><?= h($fout) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label" for="aantal">Aantal tickets</label>
                    <select name="aantal" id="aantal" class="form-control">
                        <?php for ($n = 1; $n <= 10; $n++): ?>
                            <option value="<?= $n ?>"><?= $n ?> ticket<?= $n > 1 ? 's' : '' ?>
                                — <?= formatPrijs($show['prijs'] * $n) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div style="background:var(--bg3); border-radius:var(--radius); padding:16px; margin-bottom:20px;">
                    <p style="font-size:13px; color:var(--text-muted); margin-bottom:4px;">✅ Veilig betalen</p>
                    <p style="font-size:13px; color:var(--text-muted);">📧 Ticket direct in jouw account</p>
                    <p style="font-size:13px; color:var(--text-muted);">🎟️ QR-code voor toegang bij de deur</p>
                </div>

                <div style="display:flex; gap:12px;">
                    <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center;">
                        🎟️ Nu kopen
                    </button>
                    <a href="/shows.php" class="btn btn-secondary">Terug</a>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
