<?php
$paginaTitel = 'Mijn Tickets';
require_once __DIR__ . '/../includes/functions.php';
vereisLogin();

$tickets = getTicketsVanGebruiker((int)$_SESSION['gebruiker_id']);
require_once __DIR__ . '/../includes/header.php';
?>

<section style="padding: 64px 0 96px;">
    <div class="container">
        <div class="section-header fade-in" style="text-align:left; margin-bottom:40px;">
            <span class="section-label">Mijn account</span>
            <h1 class="section-title" style="text-align:left;">Mijn tickets</h1>
            <p class="section-subtitle" style="text-align:left; margin:0;">
                Welkom, <?= h($_SESSION['naam']) ?>! Hier vind je al jouw gekochte tickets.
            </p>
        </div>

        <?php if (empty($tickets)): ?>
            <div style="text-align:center; padding:80px 0; color:var(--text-muted);">
                <div style="font-size:56px; margin-bottom:16px;">🎟️</div>
                <h2 style="font-size:22px; margin-bottom:8px;">Nog geen tickets</h2>
                <p style="margin-bottom:24px;">Je hebt nog geen tickets gekocht. Bekijk de shows en koop jouw eerste ticket!</p>
                <a href="/shows.php" class="btn btn-primary">🎭 Bekijk shows</a>
            </div>
        <?php else: ?>
            <div style="display:grid; gap:20px;">
                <?php foreach ($tickets as $i => $t): ?>
                    <div class="fade-in fade-in-delay-<?= ($i % 3) + 1 ?>"
                         style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg);
                                display:grid; grid-template-columns:1fr auto; gap:24px;
                                padding:28px; align-items:center;
                                <?= $t['status'] === 'gebruikt' ? 'opacity:0.6;' : '' ?>">

                        <div style="display:grid; grid-template-columns:auto 1fr; gap:20px; align-items:center;">
                            <!-- Ticket pictogram -->
                            <div style="width:56px;height:56px;border-radius:14px;
                                        background:rgba(240,180,41,0.12);
                                        display:flex;align-items:center;justify-content:center;font-size:28px;">
                                🎭
                            </div>
                            <div>
                                <h2 style="font-size:18px; font-weight:700; margin-bottom:6px;"><?= h($t['titel']) ?></h2>
                                <div style="display:flex; gap:20px; flex-wrap:wrap;">
                                    <span style="font-size:13px; color:var(--text-muted);">📅 <?= formatDatum($t['datum']) ?></span>
                                    <span style="font-size:13px; color:var(--text-muted);">🕐 <?= substr($t['tijd'], 0, 5) ?></span>
                                    <span style="font-size:13px; color:var(--text-muted);">📍 <?= h($t['locatie']) ?></span>
                                    <span style="font-size:13px; color:var(--text-muted);">👥 <?= $t['aantal'] ?>x</span>
                                </div>
                            </div>
                        </div>

                        <div style="text-align:right;">
                            <div style="font-size:22px; font-weight:700; color:var(--gold); margin-bottom:8px;">
                                <?= formatPrijs((float)$t['totaalprijs']) ?>
                            </div>
                            <?php
                            $badge = match($t['status']) {
                                'actief'      => 'badge-success',
                                'gebruikt'    => 'badge-warning',
                                'geannuleerd' => 'badge-danger',
                                default       => ''
                            };
                            ?>
                            <span class="badge <?= $badge ?>"><?= ucfirst(h($t['status'])) ?></span>
                            <?php if ($t['status'] === 'actief'): ?>
                                <div style="margin-top:12px; font-size:11px; color:var(--text-muted); font-family:monospace;">
                                    QR: <?= substr($t['qr_code'], 0, 12) ?>...
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top:40px; text-align:center;">
                <a href="/shows.php" class="btn btn-secondary">🎭 Meer tickets kopen</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
