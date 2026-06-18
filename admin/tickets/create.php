<?php
/**
 * Nieuw Ticket Toevoegen - Aurora Theater Admin
 * 
 * Maakt een handmatige ticketboeking aan voor een bezoeker.
 * Toegankelijk voor admins en medewerkers.
 */
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Beveiliging: Toegankelijk voor admin en medewerker
checkAccess(['admin', 'medewerker']);

$error = '';
$success = '';

// Verwerk het formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gebruiker_id = isset($_POST['gebruiker_id']) ? intval($_POST['gebruiker_id']) : 0;
    $voorstelling_id = isset($_POST['voorstelling_id']) ? intval($_POST['voorstelling_id']) : 0;
    $stoel_nummers = sanitize($_POST['stoel_nummers'] ?? '');
    $tickettype = sanitize($_POST['tickettype'] ?? 'Standaard');
    $prijs = isset($_POST['prijs']) ? floatval($_POST['prijs']) : 0.00;

    // Aantal plaatsen bepalen op basis van ingevoerde stoelen (komma-gescheiden)
    $seats_array = array_filter(array_map('trim', explode(',', $stoel_nummers)));
    $aantal_plaatsen = count($seats_array);

    // Validatie (Unhappy Scenario: "Ticket kon niet worden aangemaakt")
    if ($gebruiker_id <= 0 || $voorstelling_id <= 0 || empty($stoel_nummers) || $aantal_plaatsen <= 0 || $prijs <= 0) {
        $error = "Ticket kon niet worden aangemaakt: Controleer alle ingevoerde gegevens en vul alle verplichte velden in.";
    } else {
        // Controleer of de show bestaat en of er voldoende beschikbare plaatsen zijn
        $show_stmt = $conn->prepare("SELECT titel, beschikbare_plaatsen FROM voorstellingen WHERE id = ?");
        $show_stmt->bind_param("i", $voorstelling_id);
        $show_stmt->execute();
        $show = $show_stmt->get_result()->fetch_assoc();
        $show_stmt->close();

        // Controleer of de bezoeker bestaat
        $user_stmt = $conn->prepare("SELECT naam, email FROM gebruikers WHERE id = ?");
        $user_stmt->bind_param("i", $gebruiker_id);
        $user_stmt->execute();
        $user = $user_stmt->get_result()->fetch_assoc();
        $user_stmt->close();

        if (!$show) {
            $error = "Ticket kon niet worden aangemaakt: Geselecteerde voorstelling bestaat niet.";
        } elseif (!$user) {
            $error = "Ticket kon niet worden aangemaakt: Geselecteerde bezoeker bestaat niet.";
        } elseif ($show['beschikbare_plaatsen'] < $aantal_plaatsen) {
            $error = "Ticket kon niet worden aangemaakt: Niet genoeg beschikbare plaatsen (" . $show['beschikbare_plaatsen'] . " over).";
        } else {
            // Start database transactie voor consistentie
            $conn->begin_transaction();
            try {
                // 1. Voeg ticket toe in database
                $insert_stmt = $conn->prepare("INSERT INTO tickets (voorstelling_id, gebruiker_id, aantal_plaatsen, totale_prijs, stoel_nummers, tickettype, status) VALUES (?, ?, ?, ?, ?, ?, 'actief')");
                $insert_stmt->bind_param("iiiiss", $voorstelling_id, $gebruiker_id, $aantal_plaatsen, $prijs, $stoel_nummers, $tickettype);
                $insert_stmt->execute();
                $insert_stmt->close();

                // 2. Verminder beschikbare plaatsen van voorstelling
                $update_stmt = $conn->prepare("UPDATE voorstellingen SET beschikbare_plaatsen = beschikbare_plaatsen - ? WHERE id = ?");
                $update_stmt->bind_param("ii", $aantal_plaatsen, $voorstelling_id);
                $update_stmt->execute();
                $update_stmt->close();

                // 3. EXTRA: Voeg na succesvolle toevoeging automatisch een melding toe aan de tabel meldingen
                $system_name = 'Systeem';
                $system_email = 'info@auroratheater.nl';
                $log_subject = 'Nieuw ticket aangemaakt';
                $log_message = "Nieuw ticket aangemaakt voor " . $user['naam'] . " (" . $user['email'] . ") voor de voorstelling '" . $show['titel'] . "' (Stoelen: $stoel_nummers, Type: $tickettype, Prijs: €" . number_format($prijs, 2, ',', '.') . ").";
                $log_priority = 'gemiddeld';
                $log_date = date('Y-m-d');
                $log_status = 'nieuw';

                $log_stmt = $conn->prepare("INSERT INTO meldingen (naam, email, onderwerp, bericht, status, prioriteit, datum) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $log_stmt->bind_param("sssssss", $system_name, $system_email, $log_subject, $log_message, $log_status, $log_priority, $log_date);
                $log_stmt->execute();
                $log_stmt->close();

                $conn->commit();

                // Happy scenario: "Ticket succesvol aangemaakt"
                setFlashMessage('success', 'Ticket succesvol aangemaakt.');
                header('Location: ../tickets.php');
                exit;
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Ticket kon niet worden aangemaakt: " . $e->getMessage();
            }
        }
    }
}

// Haal alle actieve gebruikers/bezoekers op voor de dropdown
$users_result = $conn->query("SELECT id, naam, email, rol FROM gebruikers ORDER BY rol ASC, naam ASC");

// Haal alle toekomstige voorstellingen met vrije plaatsen op
$shows_result = $conn->query("SELECT id, titel, zaal, datum_tijd, prijs, beschikbare_plaatsen FROM voorstellingen ORDER BY datum_tijd ASC");

// Inclusief header (HTML start)
include '../../includes/admin_header.php';
?>

<div class="admin-card">
    <h3>Nieuw Ticket Handmatig Toevoegen</h3>
    <p style="color: var(--admin-text-muted); margin-bottom: 25px;">
        Registreer een handmatige ticketboeking voor een bezoeker. Dit vermindert automatisch het aantal beschikbare plaatsen van de voorstelling.
    </p>

    <!-- Foutmeldingen weergeven -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error" id="form-error">
            <span class="alert-icon">✗</span>
            <span class="alert-text"><?php echo sanitize($error); ?></span>
            <button class="alert-close" onclick="document.getElementById('form-error').style.display='none'">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Responsive Formulier -->
    <form action="create.php" method="POST" class="my-4">
        <div class="form-row">
            <div class="form-group">
                <label for="gebruiker_id">Bezoeker *</label>
                <select id="gebruiker_id" name="gebruiker_id" class="form-control" required>
                    <option value="">-- Kies een bezoeker --</option>
                    <?php if ($users_result && $users_result->num_rows > 0): ?>
                        <?php while ($u = $users_result->fetch_assoc()): ?>
                            <option value="<?php echo $u['id']; ?>" <?php echo (isset($_POST['gebruiker_id']) && intval($_POST['gebruiker_id']) === $u['id']) ? 'selected' : ''; ?>>
                                <?php echo sanitize($u['naam']) . " (" . sanitize($u['email']) . ") - " . ucfirst($u['rol']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="voorstelling_id">Voorstelling *</label>
                <select id="voorstelling_id" name="voorstelling_id" class="form-control" required onchange="updateDefaultPrice(this)">
                    <option value="">-- Kies een voorstelling --</option>
                    <?php if ($shows_result && $shows_result->num_rows > 0): ?>
                        <?php while ($s = $shows_result->fetch_assoc()): ?>
                            <option value="<?php echo $s['id']; ?>" 
                                    data-price="<?php echo $s['prijs']; ?>"
                                    <?php echo ($s['beschikbare_plaatsen'] <= 0) ? 'disabled' : ''; ?>
                                    <?php echo (isset($_POST['voorstelling_id']) && intval($_POST['voorstelling_id']) === $s['id']) ? 'selected' : ''; ?>>
                                <?php echo sanitize($s['titel']) . " (" . date('d-m-Y H:i', strtotime($s['datum_tijd'])) . ") - " . $s['beschikbare_plaatsen'] . " vrij"; ?>
                                <?php echo ($s['beschikbare_plaatsen'] <= 0) ? ' [UITVERKOCHT]' : ''; ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="stoel_nummers">Stoelnummer(s) *</label>
                <input type="text" id="stoel_nummers" name="stoel_nummers" class="form-control" placeholder="Bijv. A5 of A5, A6" value="<?php echo isset($_POST['stoel_nummers']) ? sanitize($_POST['stoel_nummers']) : ''; ?>" required>
                <span style="font-size: 0.75rem; color: var(--admin-text-muted); display: block; margin-top: 5px;">
                    Voer stoelen komma-gescheiden in (bijv. A5 of B1, B2). Het aantal stoelen bepaalt het ticket aantal.
                </span>
            </div>
            
            <div class="form-group">
                <label for="tickettype">Tickettype *</label>
                <select id="tickettype" name="tickettype" class="form-control" required>
                    <option value="Standaard" <?php echo (isset($_POST['tickettype']) && $_POST['tickettype'] === 'Standaard') ? 'selected' : ''; ?>>Standaard Ticket</option>
                    <option value="VIP" <?php echo (isset($_POST['tickettype']) && $_POST['tickettype'] === 'VIP') ? 'selected' : ''; ?>>VIP Ticket</option>
                    <option value="Kind" <?php echo (isset($_POST['tickettype']) && $_POST['tickettype'] === 'Kind') ? 'selected' : ''; ?>>Kind / Senioren</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="prijs">Prijs (€) *</label>
                <input type="number" step="0.01" min="0.01" id="prijs" name="prijs" class="form-control" placeholder="Totaalprijs" value="<?php echo isset($_POST['prijs']) ? floatval($_POST['prijs']) : ''; ?>" required>
            </div>
            <div class="form-group" style="display: flex; align-items: center; padding-top: 25px; color: var(--admin-text-muted); font-size: 0.85rem;">
                ℹ️ Tip: Selecteer een voorstelling om de standaard ticketprijs automatisch in te vullen.
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn-primary">
                <span>Ticket Aanmaken</span>
            </button>
            <a href="../tickets.php" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border-radius: 30px; font-weight: bold; font-size: 0.95rem;">Annuleren</a>
        </div>
    </form>
</div>

<script>
// Automatisch ticketprijs invullen bij selecteren van voorstelling
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

// Koppel event om de prijs ook te herberekenen bij verandering van stoelen
document.getElementById('stoel_nummers').addEventListener('input', function() {
    const select = document.getElementById('voorstelling_id');
    updateDefaultPrice(select);
});
</script>

<?php
// Inclusief footer
include '../../includes/admin_footer.php';
?>
