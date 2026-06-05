<?php
/**
 * Ticket Reservering - Aurora Theater
 * 
 * Bevat het boekingsformulier met een interactieve stoelkiezer.
 * Toegankelijk voor ingelogde klanten.
 */

// Header inladen (dit laadt automatisch db.php en functions.php in)
$page_title = "Tickets Boeken";
include 'includes/header.php';

// Controleer of de gebruiker is ingelogd. Zo niet, redirect naar login.
if (!isLoggedIn()) {
    setFlashMessage('error', 'U moet ingelogd zijn om tickets te kunnen reserveren.');
    header('Location: login.php');
    exit;
}

// Bepaal de geselecteerde voorstelling
$voorstelling_id = isset($_GET['voorstelling_id']) ? intval($_GET['voorstelling_id']) : 0;

// Laad alle beschikbare toekomstige voorstellingen voor de dropdown
$dropdown_query = "SELECT id, titel, prijs, beschikbare_plaatsen FROM voorstellingen WHERE datum_tijd >= NOW() ORDER BY datum_tijd ASC";
$dropdown_result = $conn->query($dropdown_query);

// Laad gegevens van de geselecteerde voorstelling
$show = null;
$taken_seats = [];
$service_fee = floatval(getSetting('ticket_toeslag', '1.50'));

if ($voorstelling_id > 0) {
    $show_query = "SELECT * FROM voorstellingen WHERE id = ? AND datum_tijd >= NOW()";
    if ($stmt = $conn->prepare($show_query)) {
        $stmt->bind_param('i', $voorstelling_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $show = $result->fetch_assoc();
            }
        }
        $stmt->close();
    }
    
    // Als de voorstelling bestaat, haal de al geboekte stoelen op
    if ($show) {
        $seats_query = "SELECT stoel_nummers FROM tickets WHERE voorstelling_id = ? AND status = 'actief'";
        if ($stmt = $conn->prepare($seats_query)) {
            $stmt->bind_param('i', $voorstelling_id);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    if (!empty($row['stoel_nummers'])) {
                        // Splits stoelnummers (bijv. "A1, A2" -> ['A1', 'A2'])
                        $split = array_map('trim', explode(',', $row['stoel_nummers']));
                        $taken_seats = array_merge($taken_seats, $split);
                    }
                }
            }
            $stmt->close();
        }
        // Verwijder eventuele lege waarden
        $taken_seats = array_filter($taken_seats);
    }
}

// ==========================================
// TICKET BOEKING PROCESS (Happy/Unhappy)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book_tickets') {
    $selected_show_id = intval($_POST['voorstelling_id'] ?? 0);
    $stoel_nummers = sanitize($_POST['stoel_nummers'] ?? '');
    $aantal_tickets = intval($_POST['tickets_count'] ?? 0);
    $user_id = $_SESSION['user_id'];
    
    // Valideer invoer (Unhappy scenario's)
    if ($selected_show_id <= 0 || empty($stoel_nummers) || $aantal_tickets <= 0) {
        setFlashMessage('error', 'Boeking mislukt: U dient ten minste één stoel te selecteren.');
    } else {
        // Haal show details op om de prijs te controleren
        $check_query = "SELECT prijs, beschikbare_plaatsen FROM voorstellingen WHERE id = ? FOR UPDATE";
        
        // Start transactie om race conditions te voorkomen (zeer professioneel)
        $conn->begin_transaction();
        
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param('i', $selected_show_id);
        $stmt->execute();
        $check_result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$check_result) {
            setFlashMessage('error', 'Boeking mislukt: Geselecteerde voorstelling bestaat niet.');
            $conn->rollback();
        } elseif ($check_result['beschikbare_plaatsen'] < $aantal_tickets) {
            setFlashMessage('error', 'Boeking mislukt: Er zijn niet genoeg beschikbare plaatsen (' . $check_result['beschikbare_plaatsen'] . ' over).');
            $conn->rollback();
        } else {
            // Controleer of de stoelen in de tussentijd al geboekt zijn
            $requested_seats = array_map('trim', explode(',', $stoel_nummers));
            $is_already_booked = false;
            
            $check_seats_query = "SELECT stoel_nummers FROM tickets WHERE voorstelling_id = ? AND status = 'actief'";
            $stmt = $conn->prepare($check_seats_query);
            $stmt->bind_param('i', $selected_show_id);
            $stmt->execute();
            $seats_res = $stmt->get_result();
            $stmt->close();
            
            while ($row = $seats_res->fetch_assoc()) {
                $booked = array_map('trim', explode(',', $row['stoel_nummers']));
                foreach ($requested_seats as $req) {
                    if (in_array($req, $booked)) {
                        $is_already_booked = true;
                        break 2;
                    }
                }
            }
            
            if ($is_already_booked) {
                setFlashMessage('error', 'Boeking mislukt: Een of meerdere van de gekozen stoelen zijn zojuist gereserveerd. Selecteer a.u.b. andere stoelen.');
                $conn->rollback();
            } else {
                // Bereken prijs
                $totale_prijs = ($aantal_tickets * floatval($check_result['prijs'])) + $service_fee;
                
                // 1. Voeg ticket toe in database
                $insert_query = "INSERT INTO tickets (voorstelling_id, gebruiker_id, aantal_plaatsen, totale_prijs, stoel_nummers, status) VALUES (?, ?, ?, ?, ?, 'actief')";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param('iiids', $selected_show_id, $user_id, $aantal_tickets, $totale_prijs, $stoel_nummers);
                $stmt->execute();
                $stmt->close();
                
                // 2. Verminder beschikbare plaatsen van voorstelling
                $update_query = "UPDATE voorstellingen SET beschikbare_plaatsen = beschikbare_plaatsen - ? WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param('ii', $aantal_tickets, $selected_show_id);
                $stmt->execute();
                $stmt->close();
                
                // Commit de database transactie
                $conn->commit();
                
                // Happy scenario
                setFlashMessage('success', 'Geweldig! Uw tickets voor stoelen (' . $stoel_nummers . ') zijn succesvol geboekt. Veel plezier!');
                header('Location: voorstellingen.php');
                exit;
            }
        }
    }
}
?>

<main class="py-5">
    <div class="container">
        
        <div class="text-center">
            <h1 class="section-title">Tickets Reserveren</h1>
            <p class="section-subtitle">Kies uw voorstelling en selecteer uw gewenste stoelen</p>
        </div>

        <!-- Stap 1: Selecteer Voorstelling -->
        <div class="booking-grid">
            
            <!-- Linkerpaneel: Selectie & Stoelkaart -->
            <div class="booking-panel">
                <form action="tickets.php" method="GET" class="my-4" id="select-show-form">
                    <div class="form-group">
                        <label for="voorstelling-select">Kies een voorstelling</label>
                        <select name="voorstelling_id" id="voorstelling-select" class="form-control" onchange="this.form.submit()">
                            <option value="">-- Selecteer een voorstelling --</option>
                            <?php 
                            if ($dropdown_result && $dropdown_result->num_rows > 0):
                                while ($row = $dropdown_result->fetch_assoc()): 
                                    $selected = ($voorstelling_id === intval($row['id'])) ? 'selected' : '';
                                    $places = $row['beschikbare_plaatsen'];
                                    $option_text = sanitize($row['titel']) . " (" . formatteerGeld($row['prijs']) . ") - " . $places . " stoelen vrij";
                                    
                                    // Uitverkochte shows markeren
                                    if ($places <= 0) {
                                        $option_text .= " [UITVERKOCHT]";
                                    }
                            ?>
                                    <option value="<?php echo $row['id']; ?>" <?php echo $selected; ?> <?php echo ($places <= 0) ? 'disabled' : ''; ?>>
                                        <?php echo $option_text; ?>
                                    </option>
                            <?php 
                                endwhile; 
                            else:
                            ?>
                                <option value="" disabled>Geen voorstellingen beschikbaar</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </form>

                <?php if ($show): ?>
                    <!-- Interactieve Stoelen Layout -->
                    <div class="seat-selection-area">
                        <h3 class="text-center">Kies uw stoelen</h3>
                        <div class="theater-screen">PODIUM</div>
                        
                        <!-- Legenda -->
                        <div class="seat-legend">
                            <div class="legend-item">
                                <div class="seat available"></div>
                                <span>Beschikbaar</span>
                            </div>
                            <div class="legend-item">
                                <div class="seat selected"></div>
                                <span>Geselecteerd</span>
                            </div>
                            <div class="legend-item">
                                <div class="seat taken"></div>
                                <span>Bezet</span>
                            </div>
                        </div>
                        
                        <!-- Stoelen Grid (10 breed, Rows A-E) -->
                        <div class="seat-map-wrapper">
                            <div class="seat-map" id="interactive-seat-map" 
                                 data-price="<?php echo $show['prijs']; ?>" 
                                 data-fee="<?php echo $service_fee; ?>">
                                <?php
                                $rows = ['A', 'B', 'C', 'D', 'E'];
                                $cols = 10;
                                
                                foreach ($rows as $row):
                                    for ($col = 1; $col <= $cols; $col++):
                                        $seat_id = $row . $col;
                                        $is_taken = in_array($seat_id, $taken_seats);
                                        
                                        if ($is_taken):
                                ?>
                                            <div class="seat taken" data-seat="<?php echo $seat_id; ?>"><?php echo $seat_id; ?></div>
                                        <?php else: ?>
                                            <div class="seat available" data-seat="<?php echo $seat_id; ?>"><?php echo $seat_id; ?></div>
                                <?php
                                        endif;
                                    endfor;
                                endforeach;
                                ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4" style="color: var(--text-muted);">
                        <p>Selecteer hierboven een voorstelling om de stoelindeling te bekijken.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Rechterpaneel: Reserveringssamenvatting -->
            <div class="summary-panel">
                <h3>Samenvatting</h3>
                
                <?php if ($show): ?>
                    <div class="summary-list">
                        <div class="summary-item">
                            <span style="color: var(--text-muted);">Voorstelling</span>
                            <strong><?php echo sanitize($show['titel']); ?></strong>
                        </div>
                        <div class="summary-item">
                            <span style="color: var(--text-muted);">Zaal</span>
                            <strong><?php echo sanitize($show['zaal']); ?></strong>
                        </div>
                        <div class="summary-item">
                            <span style="color: var(--text-muted);">Datum & Tijd</span>
                            <strong><?php echo formatteerDatum($show['datum_tijd']); ?></strong>
                        </div>
                        <div class="summary-item">
                            <span style="color: var(--text-muted);">Prijs per ticket</span>
                            <strong><?php echo formatteerGeld($show['prijs']); ?></strong>
                        </div>
                        <div class="summary-item">
                            <span style="color: var(--text-muted);">Stoelen</span>
                            <strong id="summary-seats" style="color: var(--primary);">Geen stoelen geselecteerd</strong>
                        </div>
                        <div class="summary-item">
                            <span style="color: var(--text-muted);">Aantal tickets</span>
                            <strong id="summary-count">0x</strong>
                        </div>
                        <div class="summary-item">
                            <span style="color: var(--text-muted);">Subtotaal</span>
                            <strong id="summary-subtotal">€ 0,00</strong>
                        </div>
                        <div class="summary-item">
                            <span style="color: var(--text-muted);">Servicekosten</span>
                            <strong id="summary-fee">€ 0,00</strong>
                        </div>
                        
                        <div class="summary-item total">
                            <span>Totaal</span>
                            <span id="summary-total">€ 0,00</span>
                        </div>
                    </div>
                    
                    <!-- Boeking verzendformulier -->
                    <form action="tickets.php" method="POST">
                        <input type="hidden" name="voorstelling_id" value="<?php echo $show['id']; ?>">
                        <input type="hidden" name="stoel_nummers" id="selected-seats-input" value="">
                        <input type="hidden" name="tickets_count" id="tickets-count-input" value="0">
                        
                        <button type="submit" name="action" value="book_tickets" id="submit-booking-btn" class="btn-primary disabled" style="width: 100%;" disabled>
                            <span>Reserveren Bevestigen</span>
                        </button>
                    </form>
                <?php else: ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Er is nog geen voorstelling geselecteerd.</p>
                <?php endif; ?>
            </div>
            
        </div>
        
    </div>
</main>

<?php 
// Footer inladen
include 'includes/footer.php'; 
?>
