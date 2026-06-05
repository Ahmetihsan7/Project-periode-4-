<?php
/**
 * Admin Dashboard - Aurora Theater
 * 
 * Toont statistieken, recente boekingen en handige beheer-links.
 */

// Laad admin header (dit beveiligt ook de toegang)
include '../includes/admin_header.php';

// Initialiseer statistiek variabelen met 0 als fallback (voor lege database scenario)
$total_sold = 0;
$total_revenue = 0.00;
$total_shows = 0;
$total_users = 0;

// 1. Haal totaal verkochte tickets op
$query_tickets = "SELECT SUM(aantal_plaatsen) AS total_sold FROM tickets WHERE status = 'actief'";
if ($res = $conn->query($query_tickets)) {
    $row = $res->fetch_assoc();
    $total_sold = intval($row['total_sold'] ?? 0);
}

// 2. Haal totale omzet op
$query_revenue = "SELECT SUM(totale_prijs) AS total_revenue FROM tickets WHERE status = 'actief'";
if ($res = $conn->query($query_revenue)) {
    $row = $res->fetch_assoc();
    $total_revenue = floatval($row['total_revenue'] ?? 0.00);
}

// 3. Haal totaal aantal voorstellingen op
$query_shows = "SELECT COUNT(*) AS total_shows FROM voorstellingen";
if ($res = $conn->query($query_shows)) {
    $row = $res->fetch_assoc();
    $total_shows = intval($row['total_shows'] ?? 0);
}

// 4. Haal totaal aantal gebruikers op
$query_users = "SELECT COUNT(*) AS total_users FROM gebruikers";
if ($res = $conn->query($query_users)) {
    $row = $res->fetch_assoc();
    $total_users = intval($row['total_users'] ?? 0);
}

// 5. Haal 5 meest recente boekingen op
$query_recent = "SELECT t.*, g.naam AS klant_naam, v.titel AS voorstelling_titel 
                 FROM tickets t 
                 JOIN gebruikers g ON t.gebruiker_id = g.id 
                 JOIN voorstellingen v ON t.voorstelling_id = v.id 
                 ORDER BY t.geboekt_op DESC LIMIT 5";
$recent_bookings = $conn->query($query_recent);

// 6. Haal top voorstellingen op basis van bezettingsgraad
$query_top = "SELECT v.titel, v.datum_tijd, v.zaal, v.max_plaatsen, v.beschikbare_plaatsen,
              (v.max_plaatsen - v.beschikbare_plaatsen) AS sold_seats
              FROM voorstellingen v 
              ORDER BY sold_seats DESC LIMIT 5";
$top_shows = $conn->query($query_top);
?>

<!-- Statistieken Cards Rij -->
<div class="stats-grid">
    
    <div class="stat-card">
        <div class="stat-info">
            <h4>Tickets Verkocht</h4>
            <p class="stat-number"><?php echo $total_sold; ?></p>
        </div>
        <div class="stat-icon">🎟️</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h4>Totale Omzet</h4>
            <p class="stat-number"><?php echo formatteerGeld($total_revenue); ?></p>
        </div>
        <div class="stat-icon">💰</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h4>Voorstellingen</h4>
            <p class="stat-number"><?php echo $total_shows; ?></p>
        </div>
        <div class="stat-icon">🎭</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h4>Geregistreerde Accounts</h4>
            <p class="stat-number"><?php echo $total_users; ?></p>
        </div>
        <div class="stat-icon">👥</div>
    </div>

</div>

<!-- Recente Boekingen & Top Shows Grids -->
<div class="booking-grid" style="grid-template-columns: 2fr 1fr; margin-top: 30px;">
    
    <!-- Recente Boekingen Tabel -->
    <div class="table-panel" style="margin-bottom: 0;">
        <div class="panel-header">
            <h3>Recente Boekingen</h3>
            <a href="tickets.php" class="btn-primary btn-card" style="padding: 6px 12px; font-size: 0.8rem;">Alle boekingen</a>
        </div>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Klant</th>
                        <th>Voorstelling</th>
                        <th>Aantal</th>
                        <th>Totaalprijs</th>
                        <th>Datum boeking</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($recent_bookings && $recent_bookings->num_rows > 0):
                        while($booking = $recent_bookings->fetch_assoc()):
                    ?>
                            <tr>
                                <td><strong><?php echo sanitize($booking['klant_naam']); ?></strong></td>
                                <td><?php echo sanitize($booking['voorstelling_titel']); ?></td>
                                <td><?php echo $booking['aantal_plaatsen']; ?> stoelen</td>
                                <td><?php echo formatteerGeld($booking['totale_prijs']); ?></td>
                                <td><?php echo formatteerDatum($booking['geboekt_op'], true); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo ($booking['status'] === 'actief') ? 'actief' : 'geannuleerd'; ?>">
                                        <?php echo $booking['status']; ?>
                                    </span>
                                </td>
                            </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 30px 0; color: var(--admin-text-muted);">
                                Geen recente boekingen gevonden.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Shows / Bezetting -->
    <div class="table-panel" style="margin-bottom: 0;">
        <div class="panel-header">
            <h3>Top Voorstellingen</h3>
        </div>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Show</th>
                        <th>Verkocht</th>
                        <th>Bezetting</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($top_shows && $top_shows->num_rows > 0):
                        while($show = $top_shows->fetch_assoc()):
                            // Bereken percentage
                            $percent = $show['max_plaatsen'] > 0 ? round(($show['sold_seats'] / $show['max_plaatsen']) * 100) : 0;
                    ?>
                            <tr>
                                <td>
                                    <strong><?php echo sanitize($show['titel']); ?></strong>
                                    <span style="display: block; font-size: 0.75rem; color: var(--admin-text-muted);">
                                        <?php echo date('d-m-Y', strtotime($show['datum_tijd'])); ?>
                                    </span>
                                </td>
                                <td><?php echo $show['sold_seats']; ?> / <?php echo $show['max_plaatsen']; ?></td>
                                <td>
                                    <div style="width: 100%; height: 6px; background-color: var(--admin-border); border-radius: 3px; overflow: hidden; margin-top: 5px;">
                                        <div style="width: <?php echo $percent; ?>%; height: 100%; background-color: var(--admin-primary);"></div>
                                    </div>
                                    <span style="font-size: 0.75rem; color: var(--admin-text-muted); display: block; margin-top: 2px;">
                                        <?php echo $percent; ?>% bezet
                                    </span>
                                </td>
                            </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="3" class="text-center" style="padding: 30px 0; color: var(--admin-text-muted);">
                                Geen gegevens beschikbaar.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
// Laad admin footer
include '../includes/admin_footer.php';
?>
