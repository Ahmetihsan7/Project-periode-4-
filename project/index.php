<?php
// =============================================
// Homepage — index.php
// =============================================
$paginaTitel    = 'Home';
$metaDescription = 'Koop tickets voor de beste shows en evenementen in Nederland. Snel, veilig en eenvoudig.';
require_once __DIR__ . '/includes/functions.php';

// Haal aankomende shows op (max 6)
try {
    $shows = getAankomendShows(6);
    $stats = isIngelogd() && heeftRol('admin') ? getDashboardStats() : null;
} catch (Exception $e) {
    $shows = [];
    $stats = null;
}

// Emoji mapping per show-nummer (voor visuele afwisseling)
$emojis = ['🎭', '🎵', '😂', '🎻', '💃', '🎸'];

require_once __DIR__ . '/includes/header.php';
?>

<!-- ===================== HERO ===================== -->
<section class="hero" id="home">
    <div class="hero-bg"></div>
    <div class="hero-content">

        <!-- Tekst -->
        <div class="hero-text fade-in">
            <div class="hero-badge">
                ✨ Nederland's beste ticketplatform
            </div>
            <h1 class="hero-title">
                Jouw volgende<br>
                <span class="highlight">onvergetelijke</span><br>
                ervaring wacht
            </h1>
            <p class="hero-subtitle">
                Ontdek honderden shows, concerten en evenementen.
                Koop veilig jouw tickets online en geniet van een avond die je nooit vergeet.
            </p>
            <div class="hero-actions">
                <a href="/shows.php" class="btn btn-primary btn-lg">
                    🎟️ Bekijk alle shows
                </a>
                <?php if (!isIngelogd()): ?>
                    <a href="/auth/register.php" class="btn btn-secondary btn-lg">
                        Account aanmaken
                    </a>
                <?php endif; ?>
            </div>

            <div class="hero-stats">
                <div class="hero-stat">
                    <strong data-teller="<?= count($shows) ?: 5 ?>" data-suffix="+">0+</strong>
                    <span>Aankomende shows</span>
                </div>
                <div class="hero-stat">
                    <strong data-teller="1200" data-suffix="+">0+</strong>
                    <span>Tevreden bezoekers</span>
                </div>
                <div class="hero-stat">
                    <strong data-teller="99" data-suffix="%">0%</strong>
                    <span>Veilig betalen</span>
                </div>
            </div>
        </div>

        <!-- Visueel kaartje -->
        <div class="hero-visual fade-in fade-in-delay-2">
            <div class="hero-card-stack">
                <div class="hero-card hero-card-back2"></div>
                <div class="hero-card hero-card-back1"></div>
                <div class="hero-card hero-card-main">
                    <div class="show-card-header">
                        <span class="show-card-tag">🔥 Uitverkoop bijna</span>
                        <span class="show-card-emoji">🎭</span>
                    </div>
                    <h3 class="show-card-title">De Grote Musical</h3>
                    <p class="show-card-meta">📅 15 juni 2026 &nbsp;·&nbsp; 20:00</p>
                    <p class="show-card-meta">📍 Stadsschouwburg Amsterdam</p>
                    <hr class="show-card-divider">
                    <div class="show-card-price-row">
                        <span class="show-card-price">€ 45,00</span>
                        <span class="show-card-seats">Nog 42 plaatsen</span>
                    </div>
                    <a href="/auth/register.php" class="btn btn-primary mt-4" style="width:100%;justify-content:center;">
                        🎟️ Ticket kopen
                    </a>
                </div>
                <div class="floating-badge top-right">✅ Veilig betaald</div>
                <div class="floating-badge bottom-left">🎉 5 tickets verkocht!</div>
            </div>
        </div>

    </div>
</section>

<!-- ===================== ADMIN STATS (alleen admin) ===================== -->
<?php if ($stats): ?>
<section class="section" id="dashboard-stats">
    <div class="container">
        <div class="section-header fade-in">
            <span class="section-label">Dashboard</span>
            <h2 class="section-title">Overzicht statistieken</h2>
        </div>
        <div class="stats-grid">
            <div class="stat-card fade-in fade-in-delay-1">
                <div class="stat-icon">🎭</div>
                <div class="stat-value" data-teller="<?= (int)$stats['totaal_shows'] ?>">0</div>
                <div class="stat-label">Totaal shows</div>
            </div>
            <div class="stat-card fade-in fade-in-delay-1">
                <div class="stat-icon">🎟️</div>
                <div class="stat-value" data-teller="<?= (int)$stats['totaal_tickets'] ?>">0</div>
                <div class="stat-label">Verkochte tickets</div>
            </div>
            <div class="stat-card fade-in fade-in-delay-2">
                <div class="stat-icon">👥</div>
                <div class="stat-value" data-teller="<?= (int)$stats['totaal_bezoekers'] ?>">0</div>
                <div class="stat-label">Bezoekers</div>
            </div>
            <div class="stat-card fade-in fade-in-delay-2">
                <div class="stat-icon">💰</div>
                <div class="stat-value" data-teller="<?= (int)$stats['totaal_omzet'] ?>" data-prefix="€ ">€ 0</div>
                <div class="stat-label">Totale omzet</div>
            </div>
            <div class="stat-card fade-in fade-in-delay-3">
                <div class="stat-icon">📅</div>
                <div class="stat-value" data-teller="<?= (int)$stats['aankomende_shows'] ?>">0</div>
                <div class="stat-label">Aankomende shows</div>
            </div>
        </div>
        <div style="text-align:right;">
            <a href="/admin/dashboard.php" class="btn btn-secondary">Ga naar admin dashboard →</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===================== AANKOMENDE SHOWS ===================== -->
<section class="section section-alt" id="shows">
    <div class="container">
        <div class="section-header fade-in">
            <span class="section-label">Agenda</span>
            <h2 class="section-title">Aankomende shows</h2>
            <p class="section-subtitle">Mis geen enkele voorstelling. Koop nu jouw tickets voor de beste evenementen.</p>
        </div>

        <?php if (empty($shows)): ?>
            <div style="text-align:center; padding: 64px 0; color: var(--text-muted);">
                <div style="font-size: 48px; margin-bottom: 16px;">🎭</div>
                <p>Er zijn momenteel geen aankomende shows. Kom snel terug!</p>
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
                            <h3 class="show-card-title"><?= h($show['titel']) ?></h3>
                            <div class="show-card-meta">
                                <div class="show-card-meta-item">🕐 <?= substr($show['tijd'], 0, 5) ?></div>
                                <div class="show-card-meta-item">📍 <?= h($show['locatie']) ?></div>
                                <?php if ($show['beschrijving']): ?>
                                    <p style="margin-top:10px; font-size:13px; color:var(--text-muted); line-height:1.6;">
                                        <?= h(substr($show['beschrijving'], 0, 90)) ?>...
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="show-card-footer">
                            <span class="show-card-price"><?= formatPrijs((float)$show['prijs']) ?></span>
                            <a href="<?= isIngelogd() ? '/bezoeker/koop-ticket.php?show=' . $show['id'] : '/auth/login.php' ?>"
                               class="btn btn-primary btn-sm">
                                🎟️ Kopen
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-4" style="margin-top:48px;">
                <a href="/shows.php" class="btn btn-secondary">Bekijk alle shows →</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ===================== VOORDELEN ===================== -->
<section class="section" id="voordelen">
    <div class="container">
        <div class="section-header fade-in">
            <span class="section-label">Waarom TicketApp?</span>
            <h2 class="section-title">Alles voor jouw perfecte avond</h2>
            <p class="section-subtitle">Wij zorgen dat jij onbezorgd kunt genieten, van aanschaf tot aankomst.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card fade-in fade-in-delay-1">
                <div class="feature-icon">🔒</div>
                <h3 class="feature-title">100% Veilig betalen</h3>
                <p class="feature-desc">Jouw betaling is altijd beveiligd. Wij gebruiken de nieuwste encryptietechnieken om jouw gegevens te beschermen.</p>
            </div>
            <div class="feature-card fade-in fade-in-delay-1">
                <div class="feature-icon">⚡</div>
                <h3 class="feature-title">Direct toegang</h3>
                <p class="feature-desc">Na aankoop ontvang je direct een digitaal ticket met QR-code. Geen wachttijden, direct naar binnen.</p>
            </div>
            <div class="feature-card fade-in fade-in-delay-2">
                <div class="feature-icon">📱</div>
                <h3 class="feature-title">Altijd bij de hand</h3>
                <p class="feature-desc">Jouw tickets zijn altijd zichtbaar in jouw account. Eenvoudig toegankelijk via telefoon of computer.</p>
            </div>
            <div class="feature-card fade-in fade-in-delay-2">
                <div class="feature-icon">🎭</div>
                <h3 class="feature-title">Ruim aanbod</h3>
                <p class="feature-desc">Van musicals en jazz tot komedievoorstellingen en klassieke concerten — wij hebben het allemaal.</p>
            </div>
            <div class="feature-card fade-in fade-in-delay-3">
                <div class="feature-icon">🙋</div>
                <h3 class="feature-title">Persoonlijke service</h3>
                <p class="feature-desc">Onze medewerkers staan voor je klaar bij de ingang en helpen je graag verder als je vragen hebt.</p>
            </div>
            <div class="feature-card fade-in fade-in-delay-3">
                <div class="feature-icon">💳</div>
                <h3 class="feature-title">Alle betaalmethoden</h3>
                <p class="feature-desc">Betaal met iDEAL, creditcard of andere gangbare betaalmethoden. Snel en eenvoudig afrekenen.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===================== CTA BANNER ===================== -->
<?php if (!isIngelogd()): ?>
<section class="section section-alt" id="registreer">
    <div class="container">
        <div class="cta-banner fade-in">
            <h2 class="cta-title">Klaar voor jouw eerste show? 🎉</h2>
            <p class="cta-sub">Maak gratis een account aan en koop direct jouw tickets. In minder dan 2 minuten geregeld.</p>
            <div class="cta-actions">
                <a href="/auth/register.php" class="btn btn-primary btn-lg">Account aanmaken — Gratis</a>
                <a href="/auth/login.php" class="btn btn-secondary btn-lg">Inloggen</a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
