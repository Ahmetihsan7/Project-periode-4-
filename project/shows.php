<?php
$paginaTitel    = 'Alle Shows';
$metaDescription = 'Bekijk alle aankomende shows en evenementen. Koop eenvoudig jouw tickets online.';
require_once __DIR__ . '/includes/functions.php';

$db    = getDB();
$shows = $db->query('SELECT * FROM shows WHERE datum >= CURDATE() ORDER BY datum ASC')->fetchAll();
$emojis = ['🎭', '🎵', '😂', '🎻', '💃', '🎸'];

require_once __DIR__ . '/includes/header.php';
?>

<section style="padding: 64px 0 96px;">
    <div class="container">
        <div class="section-header fade-in">
            <span class="section-label">Agenda</span>
            <h1 class="section-title">Alle aankomende shows</h1>
            <p class="section-subtitle">Ontdek ons volledige programma en reserveer jouw plek.</p>
        </div>

        <?php if (empty($shows)): ?>
            <div style="text-align:center; padding:80px 0; color:var(--text-muted);">
                <div style="font-size:56px; margin-bottom:16px;">🎭</div>
                <h2 style="font-size:22px; margin-bottom:8px;">Binnenkort meer shows</h2>
                <p>Er zijn momenteel geen aankomende shows gepland. Kom snel terug!</p>
            </div>
        <?php else: ?>
            <div class="shows-grid">
                <?php foreach ($shows as $i => $show): ?>
                    <article class="show-card fade-in fade-in-delay-<?= ($i % 3) + 1 ?>">
                        <div class="show-card-img">
                            <?= $emojis[$i % count($emojis)] ?>
                        </div>
                        <div class="show-card-body">
                            <span class="show-card-tag">📅 <?= formatDatum($show['datum']) ?></span>
                            <h2 class="show-card-title" style="font-size:18px;"><?= h($show['titel']) ?></h2>
                            <div class="show-card-meta">
                                <div class="show-card-meta-item">🕐 <?= substr($show['tijd'], 0, 5) ?></div>
                                <div class="show-card-meta-item">📍 <?= h($show['locatie']) ?></div>
                                <div class="show-card-meta-item">👥 Max. <?= $show['capaciteit'] ?> personen</div>
                                <?php if ($show['beschrijving']): ?>
                                    <p style="margin-top:10px;font-size:13px;color:var(--text-muted);line-height:1.6;">
                                        <?= h(substr($show['beschrijving'], 0, 100)) ?>...
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="show-card-footer">
                            <span class="show-card-price"><?= formatPrijs((float)$show['prijs']) ?></span>
                            <a href="<?= isIngelogd() ? '/bezoeker/koop-ticket.php?show=' . $show['id'] : '/auth/login.php?redirect=shows' ?>"
                               class="btn btn-primary btn-sm">
                                🎟️ Kopen
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
