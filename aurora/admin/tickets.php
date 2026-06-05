<?php
// Happy scenario ticket overview gecontroleerd

/**
 * Ticketbeheer - Aurora Theater Admin
 * 
 * Beheert de ticketboekingen (CRUD tickets).
 * Toegankelijk voor admins en medewerkers.
 */

// Laad db en functies om redirects te kunnen verwerken vóór HTML output
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Tickets overzicht verbeterd

// Controleer toegang vóór redirect
checkAccess(['admin', 'medewerker']);

$action = sanitize($_GET['action'] ?? 'list');
$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Blokkeer POST verzoeken
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    setFlashMessage('error', 'Fout: Handmatige boeking is uitgeschakeld.');
    header('Location: tickets.php');
    exit;
}

// Blokkeer cancel/delete acties
if (($action === 'cancel' || $action === 'delete') && $ticket_id > 0) {
    setFlashMessage('error', 'Fout: Boekingen annuleren of verwijderen is uitgeschakeld.');
    header('Location: tickets.php');
    exit;
}

// Blokkeer add weergave
if ($action === 'add') {
    setFlashMessage('error', 'Fout: Handmatige boeking aanmaken is uitgeschakeld.');
    header('Location: tickets.php');
    exit;
}

// Inclusief header (HTML start)
include '../includes/admin_header.php';

if ($action === 'add'):
    // Laad alle klanten voor de dropdown
    $customers = $conn->query("SELECT id, naam, email FROM gebruikers ORDER BY naam ASC");
    // Laad alle toekomstige shows
    $shows = $conn->query("SELECT id, titel, prijs, beschikbare_plaatsen FROM voorstellingen WHERE datum_tijd >= NOW() AND beschikbare_plaatsen > 0 ORDER BY datum_tijd ASC");
?>

    <div class="admin-card">
        <h3>Handmatige Boeking Aanmaken</h3>
        
        <form action="tickets.php?action=add" method="POST" class="my-4">
            <input type="hidden" name="form_action" value="create">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="gebruiker_id">Klant</label>
                    <select id="gebruiker_id" name="gebruiker_id" class="form-control" required>
                        <option value="">-- Selecteer klant --</option>
                        <?php while ($c = $customers->fetch_assoc()): ?>
                            <option value="<?php echo $c['id']; ?>">
                                <?php echo sanitize($c['naam']) . " (" . sanitize($c['email']) . ")"; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="voorstelling_id">Voorstelling</label>
                    <select id="voorstelling_id" name="voorstelling_id" class="form-control" required>
                        <option value="">-- Selecteer voorstelling --</option>
                        <?php while ($s = $shows->fetch_assoc()): ?>
                            <option value="<?php echo $s['id']; ?>">
                                <?php echo sanitize($s['titel']) . " (" . formatteerGeld($s['prijs']) . ") - " . $s['beschikbare_plaatsen'] . " vrij"; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="stoel_nummers">Stoelnummers (Komma-gescheiden)</label>
                <input type="text" id="stoel_nummers" name="stoel_nummers" class="form-control" placeholder="Bijv. A1, A2, A3" required>
                <span style="font-size: 0.75rem; color: var(--admin-text-muted);">
                    Vul stoelen in tussen A1 en E10 (bijv. A5 of B1, B2). Servicekosten worden automatisch berekend.
                </span>
            </div>
            
            <div class="my-4" style="display: flex; gap: 10px;">
                <button type="submit" class="btn-primary">
                    <span>Boeking Opslaan</span>
                </button>
                <a href="tickets.php" class="btn-secondary">Annuleren</a>
            </div>
        </form>
    </div>

<?php else: ?>

    <!-- ==========================================================
       LIST VIEW (Tabel overzicht van tickets)
       ========================================================== -->
    <div class="table-panel">
        <div class="panel-header">
            <h3>Boekingsoverzicht (Tickets)</h3>
        </div>
        
        <div class="table-filter-bar">
            <div class="search-box">
                <input type="text" id="table-search" placeholder="Zoek op klant of show...">
            </div>
        </div>

        <?php
        // Haal alle tickets op met klantnaam en showtitel
        $query = "SELECT t.*, g.naam AS klant_naam, g.email AS klant_email, v.titel AS show_titel, v.datum_tijd 
                 FROM tickets t 
                 JOIN gebruikers g ON t.gebruiker_id = g.id 
                 JOIN voorstellingen v ON t.voorstelling_id = v.id 
                 ORDER BY t.geboekt_op DESC";

        $db_error = false;
        $result = $conn->query($query);

        // Unhappy Scenario: database offline of query mislukt
        if ($result === false) {
            $db_error = true;
            $error_message = 'De ticketpagina kan momenteel niet worden geladen';
            // Fout loggen naar systeem
            error_log('[Aurora Theater] Tickets query mislukt op ' . date('Y-m-d H:i:s') . ' - MySQLi fout: ' . $conn->error);
        }
        ?>

        <?php if ($db_error): ?>
            <div class="alert alert-error" style="margin: 20px 0;">
                <span class="alert-icon">✗</span>
                <span class="alert-text">De ticketpagina kan momenteel niet worden geladen. Probeer het later opnieuw of neem contact op met de systeembeheerder.</span>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Klant Details</th>
                            <th>Voorstelling</th>
                            <th>Stoelen</th>
                            <th>Aantal</th>
                            <th>Totaalprijs</th>
                            <th>Boekingsdatum</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result && $result->num_rows > 0):
                            while ($ticket = $result->fetch_assoc()):
                        ?>
                                <tr>
                                    <td>#<?php echo $ticket['id']; ?></td>
                                    <td>
                                        <strong><?php echo sanitize($ticket['klant_naam']); ?></strong>
                                        <span style="display: block; font-size: 0.75rem; color: var(--admin-text-muted);"><?php echo sanitize($ticket['klant_email']); ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo sanitize($ticket['show_titel']); ?></strong>
                                        <span style="display: block; font-size: 0.75rem; color: var(--admin-text-muted);"><?php echo formatteerDatum($ticket['datum_tijd'], true); ?></span>
                                    </td>
                                    <td><code style="background-color: var(--admin-border); padding: 3px 6px; border-radius: 4px; color: var(--admin-primary); font-weight: bold;"><?php echo sanitize($ticket['stoel_nummers']); ?></code></td>
                                    <td><?php echo $ticket['aantal_plaatsen']; ?>x</td>
                                    <td><strong><?php echo formatteerGeld($ticket['totale_prijs']); ?></strong></td>
                                    <td><?php echo date('d-m-Y H:i', strtotime($ticket['geboekt_op'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo ($ticket['status'] === 'actief') ? 'actief' : 'geannuleerd'; ?>">
                                            <?php echo $ticket['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="8" class="text-center" style="padding: 30px 0; color: var(--admin-text-muted);">
                                    Geen ticketboekingen gevonden in de database.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; // einde $db_error check ?>

    </div>

<?php endif; ?>

<?php
// Inclusief footer
include '../includes/admin_footer.php';
?>