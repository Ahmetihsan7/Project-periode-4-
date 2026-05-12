<?php
$paginaTitel = 'Ticket scannen';
require_once __DIR__ . '/../includes/functions.php';
vereisRol('medewerker');

$resultaat = null;
$fout      = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qr = trim($_POST['qr_code'] ?? '');

    if (empty($qr)) {
        $fout = 'Voer een QR-code in.';
    } else {
        $ticket = valideerTicket($qr);
        if (!$ticket) {
            $fout = 'Ticket niet gevonden. Controleer de code.';
        } elseif ($ticket['status'] === 'gebruikt') {
            $fout = 'Dit ticket is al gebruikt op ' . date('d-m-Y H:i', strtotime($ticket['gekocht_op'])) . '.';
        } elseif ($ticket['status'] === 'geannuleerd') {
            $fout = 'Dit ticket is geannuleerd.';
        } else {
            // Markeer als gebruikt
            markeerTicketGebruikt($qr);
            $resultaat = $ticket;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-section">
            <p class="sidebar-label">Medewerker</p>
            <a href="/employee/dashboard.php" class="sidebar-link">📊 Dashboard</a>
            <a href="/employee/check_ticket.php" class="sidebar-link active">🔍 Ticket scannen</a>
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
            <h1 class="page-title">Ticket scannen</h1>
            <p class="page-subtitle">Voer de QR-code in om een ticket te valideren.</p>
        </div>

        <div style="max-width:520px;">
            <!-- Scan formulier -->
            <div class="form-card fade-in" style="max-width:100%; margin-bottom:24px;">
                <form method="POST" action="/employee/check_ticket.php">
                    <div class="form-group">
                        <label class="form-label" for="qr_code">QR-code</label>
                        <input type="text" name="qr_code" id="qr_code" class="form-control"
                            placeholder="Plak of scan de QR-code hier..."
                            autofocus autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                        🔍 Valideer ticket
                    </button>
                </form>
            </div>

            <!-- Foutmelding -->
            <?php if ($fout): ?>
                <div class="flash-message flash-error fade-in">
                    ❌ <?= h($fout) ?>
                </div>
            <?php endif; ?>

            <!-- Succesbericht -->
            <?php if ($resultaat): ?>
                <div class="form-card fade-in" style="max-width:100%; border-color:var(--success);">
                    <div style="text-align:center; margin-bottom:20px;">
                        <div style="font-size:48px; margin-bottom:8px;">✅</div>
                        <h2 style="color:var(--success); font-size:22px; font-weight:700;">Ticket geldig!</h2>
                        <p style="color:var(--text-muted); font-size:14px;">Toegang verleend</p>
                    </div>
                    <hr style="border:none;border-top:1px solid var(--border);margin:20px 0;">
                    <div style="display:grid; gap:12px;">
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:var(--text-muted);font-size:14px;">Bezoeker</span>
                            <strong><?= h($resultaat['bezoeker']) ?></strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:var(--text-muted);font-size:14px;">Show</span>
                            <strong><?= h($resultaat['titel']) ?></strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:var(--text-muted);font-size:14px;">Datum</span>
                            <strong><?= formatDatum($resultaat['datum']) ?></strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:var(--text-muted);font-size:14px;">Aantal</span>
                            <strong><?= $resultaat['aantal'] ?> persoon/personen</strong>
                        </div>
                    </div>
                    <a href="/employee/check_ticket.php" class="btn btn-secondary" style="width:100%;justify-content:center;margin-top:20px;">
                        Volgend ticket scannen
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
