<?php
/**
 * Ticket Wijzigen - Aurora Theater Admin
 * 
 * Wijzigt een bestaande ticketboeking voor een bezoeker.
 * Toegankelijk voor admins en medewerkers.
 */
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Beveiliging: Toegankelijk voor admin en medewerker
checkAccess(['admin', 'medewerker']);

$error = '';
$success = '';
$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$ticket = null;

// Haal het bestaande ticket op via PDO
if ($ticket_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ?");
        $stmt->execute([$ticket_id]);
        $ticket = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Fout bij ophalen van gegevens: " . $e->getMessage();
    }
}

// Verwerk het formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $ticket) {
    $gebruiker_id = isset($_POST['gebruiker_id']) ? intval($_POST['gebruiker_id']) : 0;
    $voorstelling_id = isset($_POST['voorstelling_id']) ? intval($_POST['voorstelling_id']) : 0;
    $stoel_nummers = sanitize($_POST['stoel_nummers'] ?? '');
    $tickettype = sanitize($_POST['tickettype'] ?? 'Standaard');
    $prijs = isset($_POST['prijs']) ? floatval($_POST['prijs']) : 0.00;

    // Aantal plaatsen bepalen op basis van ingevoerde stoelen (komma-gescheiden)
    $seats_array = array_filter(array_map('trim', explode(',', $stoel_nummers)));
    $aantal_plaatsen = count($seats_array);

    // Validatie (Unhappy Scenario)
    if ($gebruiker_id <= 0 || $voorstelling_id <= 0 || empty($stoel_nummers) || $aantal_plaatsen <= 0 || $prijs <= 0) {
        $error = "Controleer de ingevoerde gegevens: Vul alle verplichte velden correct in.";
    } else {
        try {
            // 1. Controle op bestaande voorstelling
            $show_stmt = $pdo->prepare("SELECT titel, beschikbare_plaatsen FROM voorstellingen WHERE id = ?");
            $show_stmt->execute([$voorstelling_id]);
            $show = $show_stmt->fetch();

            // 2. Controle of bezoeker bestaat
            $user_stmt = $pdo->prepare("SELECT naam, email FROM gebruikers WHERE id = ?");
            $user_stmt->execute([$gebruiker_id]);
            $user = $user_stmt->fetch();

            if (!$show) {
                $error = "Controleer de ingevoerde gegevens: Geselecteerde voorstelling bestaat niet.";
            } elseif (!$user) {
                $error = "Controleer de ingevoerde gegevens: Geselecteerde bezoeker bestaat niet.";
            } else {
                // 3. Controle op beschikbaar stoelnummer (exclusief dit ticket zelf)
                $seat_stmt = $pdo->prepare("SELECT id, stoel_nummers FROM tickets WHERE voorstelling_id = ? AND status = 'actief' AND id != ?");
                $seat_stmt->execute([$voorstelling_id, $ticket_id]);
                $other_tickets = $seat_stmt->fetchAll();

                $already_booked_seats = [];
                foreach ($other_tickets as $ot) {
                    if (!empty($ot['stoel_nummers'])) {
                        $seats = array_map('trim', explode(',', $ot['stoel_nummers']));
                        $already_booked_seats = array_merge($already_booked_seats, $seats);
                    }
                }

                $overlapping_seats = array_intersect($seats_array, $already_booked_seats);

                if (!empty($overlapping_seats)) {
                    $error = "Controleer de ingevoerde gegevens: De volgende stoelen zijn al gereserveerd: " . implode(', ', $overlapping_seats);
                } else {
                    // Start transactie
                    $pdo->beginTransaction();

                    // A. Geef originele plaatsen terug aan de originele voorstelling
                    $add_stmt = $pdo->prepare("UPDATE voorstellingen SET beschikbare_plaatsen = beschikbare_plaatsen + ? WHERE id = ?");
                    $add_stmt->execute([$ticket['aantal_plaatsen'], $ticket['voorstelling_id']]);

                    // B. Controleer of de nieuwe voorstelling genoeg plek heeft (inclusief de zojuist teruggegeven plaatsen indien dezelfde voorstelling)
                    $check_stmt = $pdo->prepare("SELECT beschikbare_plaatsen FROM voorstellingen WHERE id = ?");
                    $check_stmt->execute([$voorstelling_id]);
                    $current_avail = $check_stmt->fetchColumn();

                    if ($current_avail < $aantal_plaatsen) {
                        throw new Exception("Niet genoeg beschikbare plaatsen op de geselecteerde voorstelling (beschikbaar: " . $current_avail . ").");
                    }

                    // C. Trek de nieuwe plaatsen af van de nieuwe voorstelling
                    $deduct_stmt = $pdo->prepare("UPDATE voorstellingen SET beschikbare_plaatsen = beschikbare_plaatsen - ? WHERE id = ?");
                    $deduct_stmt->execute([$aantal_plaatsen, $voorstelling_id]);

                    // D. Update de ticketgegevens
                    $update_stmt = $pdo->prepare("UPDATE tickets SET voorstelling_id = ?, gebruiker_id = ?, aantal_plaatsen = ?, totale_prijs = ?, stoel_nummers = ?, tickettype = ? WHERE id = ?");
                    $update_stmt->execute([$voorstelling_id, $gebruiker_id, $aantal_plaatsen, $prijs, $stoel_nummers, $tickettype, $ticket_id]);

                    $pdo->commit();

                    // Happy scenario: "Gegevens succesvol gewijzigd"
                    setFlashMessage('success', 'Gegevens succesvol gewijzigd');
                    header('Location: ../tickets.php');
                    exit;
                }
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Controleer de ingevoerde gegevens: " . $e->getMessage();
        }
    }
}

// Haal gebruikers op voor dropdown
try {
    $users_stmt = $pdo->query("SELECT id, naam, email, rol FROM gebruikers ORDER BY rol ASC, naam ASC");
    $users = $users_stmt->fetchAll();
    
    $shows_stmt = $pdo->query("SELECT id, titel, zaal, datum_tijd, prijs, beschikbare_plaatsen FROM voorstellingen ORDER BY datum_tijd ASC");
    $shows = $shows_stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Fout bij ophalen databasegegevens: " . $e->getMessage();
}

// Inclusief header (HTML start)
include '../../includes/admin_header.php';
?>

<div class="admin-card">
    <h3>Ticket Boeking Wijzigen</h3>
    <p style="color: var(--admin-text-muted); margin-bottom: 25px;">
        Pas de boeking details en stoelreservering aan.
    </p>

    <!-- Foutmeldingen weergeven -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error" id="form-error">
            <span class="alert-icon">✗</span>
            <span class="alert-text"><?php echo sanitize($error); ?></span>
            <button class="alert-close" onclick="document.getElementById('form-error').style.display='none'">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (!$ticket): ?>
        <div class="alert alert-error">Gegevens niet gevonden.</div>
        <a href="../tickets.php" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border-radius: 30px; font-weight: bold; font-size: 0.95rem; margin-top: 15px;">Terug naar overzicht</a>
    <?php else: ?>
        <!-- Responsive Formulier -->
        <form action="edit.php?id=<?php echo $ticket_id; ?>" method="POST" class="my-4">
            <div class="form-row">
                <div class="form-group">
                    <label for="gebruiker_id">Bezoeker *</label>
                    <select id="gebruiker_id" name="gebruiker_id" class="form-control" required>
                        <?php foreach ($users as $u): ?>
                            <option value="<?php echo $u['id']; ?>" <?php echo ($ticket['gebruiker_id'] === $u['id']) ? 'selected' : ''; ?>>
                                <?php echo sanitize($u['naam']) . " (" . sanitize($u['email']) . ") - " . ucfirst($u['rol']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="voorstelling_id">Voorstelling *</label>
                    <select id="voorstelling_id" name="voorstelling_id" class="form-control" required onchange="updateDefaultPrice(this)">
                        <?php foreach ($shows as $s): ?>
                            <?php
                            // Bereken de beschikbare capaciteit rekening houdend met de huidige boeking
                            $avail = $s['beschikbare_plaatsen'];
                            if ($s['id'] === $ticket['voorstelling_id']) {
                                $avail += $ticket['aantal_plaatsen'];
                            }
                            ?>
                            <option value="<?php echo $s['id']; ?>" 
                                    data-price="<?php echo $s['prijs']; ?>"
                                    <?php echo ($avail <= 0) ? 'disabled' : ''; ?>
                                    <?php echo ($ticket['voorstelling_id'] === $s['id']) ? 'selected' : ''; ?>>
                                <?php echo sanitize($s['titel']) . " (" . date('d-m-Y H:i', strtotime($s['datum_tijd'])) . ") - " . $avail . " vrij"; ?>
                                <?php echo ($avail <= 0) ? ' [UITVERKOCHT]' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="stoel_nummers">Stoelnummer(s) *</label>
                    <input type="text" id="stoel_nummers" name="stoel_nummers" class="form-control" placeholder="Bijv. A5 of A5, A6" value="<?php echo sanitize($ticket['stoel_nummers']); ?>" required>
                    <span style="font-size: 0.75rem; color: var(--admin-text-muted); display: block; margin-top: 5px;">
                        Voer stoelen komma-gescheiden in (bijv. A5 of B1, B2). Het aantal stoelen bepaalt het ticket aantal.
                    </span>
                </div>
                
                <div class="form-group">
                    <label for="tickettype">Tickettype *</label>
                    <select id="tickettype" name="tickettype" class="form-control" required>
                        <option value="Standaard" <?php echo ($ticket['tickettype'] === 'Standaard') ? 'selected' : ''; ?>>Standaard Ticket</option>
                        <option value="VIP" <?php echo ($ticket['tickettype'] === 'VIP') ? 'selected' : ''; ?>>VIP Ticket</option>
                        <option value="Kind" <?php echo ($ticket['tickettype'] === 'Kind') ? 'selected' : ''; ?>>Kind / Senioren</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="prijs">Prijs (€) *</label>
                    <input type="number" step="0.01" min="0.01" id="prijs" name="prijs" class="form-control" placeholder="Totaalprijs" value="<?php echo floatval($ticket['totale_prijs']); ?>" required>
                </div>
                <div class="form-group" style="display: flex; align-items: center; padding-top: 25px; color: var(--admin-text-muted); font-size: 0.85rem;">
                    ℹ️ Tip: Selecteer een voorstelling om de standaard ticketprijs automatisch te herberekenen.
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn-primary">
                    <span>Opslaan</span>
                </button>
                <a href="../tickets.php" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border-radius: 30px; font-weight: bold; font-size: 0.95rem;">Annuleren</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
function updateDefaultPrice(select) {
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption && selectedOption.dataset.price) {
        const priceField = document.getElementById('prijs');
        const stoelField = document.getElementById('stoel_nummers');
        
        // Bereken aantal stoelen
        const seatsVal = stoelField.value.trim();
        const numSeats = seatsVal ? seatsVal.split(',').filter(x => x.trim()).length : 1;
        
        const price = parseFloat(selectedOption.dataset.price);
        priceField.value = (price * numSeats).toFixed(2);
    }
}

document.getElementById('stoel_nummers').addEventListener('input', function() {
    const select = document.getElementById('voorstelling_id');
    updateDefaultPrice(select);
});
</script>

<?php
// Inclusief footer
include '../../includes/admin_footer.php';
?>
