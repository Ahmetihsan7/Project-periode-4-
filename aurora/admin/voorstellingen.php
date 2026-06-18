<?php
/**
 * Voorstellingenbeheer - Aurora Theater Admin
 * 
 * Beheert de programmering (CRUD voorstellingen).
 * Toegankelijk voor admins en medewerkers.
 */
   // Unhupy scenario: overzicht voorstellinggen gecontroleerd, is gelukt database p4 ticket ook
   

// Laad db en functies om redirects te kunnen verwerken vóór HTML output
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Voorstellingen overzicht verbeterd

// Controleer toegang vóór redirect
checkAccess(['admin', 'medewerker']);

$action = sanitize($_GET['action'] ?? 'list');
$show_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Blokkeer POST verzoeken
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    setFlashMessage('error', 'Fout: Toevoegen en bewerken van voorstellingen is uitgeschakeld.');
    header('Location: voorstellingen.php');
    exit;
}

// Blokkeer delete acties
if ($action === 'delete' && $show_id > 0) {
    setFlashMessage('error', 'Fout: Verwijderen van voorstellingen is uitgeschakeld.');
    header('Location: voorstellingen.php');
    exit;
}

// Blokkeer add en edit weergaven
if ($action === 'add' || $action === 'edit') {
    setFlashMessage('error', 'Fout: Toevoegen en bewerken van voorstellingen is uitgeschakeld.');
    header('Location: voorstellingen.php');
    exit;
}

// Inclusief header (HTML start)
include '../includes/admin_header.php';

if ($action === 'add' || $action === 'edit'):
    $show_data = [
        'titel' => '', 'beschrijving' => '', 'afbeelding' => '', 
        'datum_tijd' => date('Y-m-d\TH:i', strtotime('+1 day 20:00')), 
        'zaal' => 'Grote Zaal A', 'prijs' => '', 'max_plaatsen' => 150, 'populair' => 0
    ];
    
    if ($action === 'edit' && $show_id > 0) {
        $query = "SELECT * FROM voorstellingen WHERE id = ?";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param('i', $show_id);
            $stmt->execute();
            $show_data = $stmt->get_result()->fetch_assoc();
            // Formatteer datetime voor input veld
            if ($show_data) {
                $show_data['datum_tijd'] = date('Y-m-d\TH:i', strtotime($show_data['datum_tijd']));
            }
            $stmt->close();
        }
        
        if (!$show_data) {
            setFlashMessage('error', 'Voorstelling niet gevonden.');
            header('Location: voorstellingen.php');
            exit;
        }
    }
?>

    <div class="admin-card">
        <h3><?php echo ($action === 'add') ? 'Nieuwe Voorstelling Plannen' : 'Voorstelling Bewerken'; ?></h3>
        
        <!-- multipart/form-data is verplicht voor bestandsuploads -->
        <form action="voorstellingen.php?action=<?php echo $action; ?>&id=<?php echo $show_id; ?>" method="POST" enctype="multipart/form-data" class="my-4">
            <input type="hidden" name="form_action" value="<?php echo ($action === 'add') ? 'create' : 'update'; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="titel">Titel van de voorstelling</label>
                    <input type="text" id="titel" name="titel" class="form-control" value="<?php echo sanitize($show_data['titel']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="zaal">Zaal</label>
                    <select id="zaal" name="zaal" class="form-control">
                        <option value="Grote Zaal A" <?php echo ($show_data['zaal'] === 'Grote Zaal A') ? 'selected' : ''; ?>>Grote Zaal A (150 plaatsen)</option>
                        <option value="Koninklijke Zaal" <?php echo ($show_data['zaal'] === 'Koninklijke Zaal') ? 'selected' : ''; ?>>Koninklijke Zaal (150 plaatsen)</option>
                        <option value="Intieme Zaal B" <?php echo ($show_data['zaal'] === 'Intieme Zaal B') ? 'selected' : ''; ?>>Intieme Zaal B (50 plaatsen)</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group form-group-full">
                <label for="beschrijving">Beschrijving / Omschrijving</label>
                <textarea id="beschrijving" name="beschrijving" class="form-control" required><?php echo sanitize($show_data['beschrijving']); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="datum_tijd">Datum & Tijdstip</label>
                    <input type="datetime-local" id="datum_tijd" name="datum_tijd" class="form-control" value="<?php echo $show_data['datum_tijd']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="prijs">Ticketprijs (€)</label>
                    <input type="number" step="0.01" min="0.05" id="prijs" name="prijs" class="form-control" placeholder="Bijv. 24.50" value="<?php echo $show_data['prijs']; ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="max_plaatsen">Maximum Capaciteit (Stoelen)</label>
                    <input type="number" min="10" max="150" id="max_plaatsen" name="max_plaatsen" class="form-control" value="<?php echo $show_data['max_plaatsen']; ?>" required>
                    <span style="font-size: 0.75rem; color: var(--admin-text-muted);">Max. 150 stoelen beschikbaar wegens interactieve zaalindeling.</span>
                </div>
                
                <div class="form-group" style="display: flex; align-items: center; gap: 10px; padding-top: 30px;">
                    <input type="checkbox" id="populair" name="populair" value="1" style="width: 20px; height: 20px; cursor: pointer;" <?php echo ($show_data['populair'] == 1) ? 'checked' : ''; ?>>
                    <label for="populair" style="margin-bottom: 0; cursor: pointer; font-weight: 600;">Markeer als 'Populaire Show' op homepage</label>
                </div>
            </div>
            
            <!-- Poster afbeelding upload -->
            <div class="form-group">
                <label for="afbeelding-input">Poster / Afbeelding</label>
                <input type="file" id="afbeelding-input" name="afbeelding" class="form-control" accept="image/*">
                
                <!-- Live JS Preview Container -->
                <div class="image-preview-box" style="<?php echo empty($show_data['afbeelding']) ? 'display: none;' : 'display: flex;'; ?>">
                    <img id="afbeelding-preview" src="<?php echo !empty($show_data['afbeelding']) ? '../' . $show_data['afbeelding'] : ''; ?>" alt="Poster preview">
                </div>
            </div>
            
            <div class="my-4" style="display: flex; gap: 10px;">
                <button type="submit" class="btn-primary">
                    <span><?php echo ($action === 'add') ? 'Voorstelling Opslaan' : 'Wijzigingen Opslaan'; ?></span>
                </button>
                <a href="voorstellingen.php" class="btn-secondary">Annuleren</a>
            </div>
        </form>
    </div>

<?php else: ?>

    <!-- ==========================================================
       LIST VIEW (Tabel overzicht van programmering)
       ========================================================== -->
    <div class="table-panel">
        <div class="panel-header">
            <h3>Geplande Voorstellingen</h3>
        </div>
        
        <div class="table-filter-bar">
            <div class="search-box">
                <input type="text" id="table-search" placeholder="Zoek op titel of zaal...">
            </div>
        </div>
        
        <?php
        // Haal alle shows op, inclusief afgelopen shows
        $query = "SELECT * FROM voorstellingen ORDER BY datum_tijd ASC";
        $result = $conn->query($query);
        ?>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Afbeelding</th>
                        <th>Titel</th>
                        <th>Zaal</th>
                        <th>Prijs</th>
                        <th>Beschikbaar / Totaal</th>
                        <th>Datum & Tijd</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && $result->num_rows > 0):
                        while ($show = $result->fetch_assoc()):
                            $is_past = strtotime($show['datum_tijd']) < time();
                    ?>
                            <tr style="<?php echo $is_past ? 'opacity: 0.55;' : ''; ?>">
                                <td>
                                    <?php 
                                    $img_path = sanitize($show['afbeelding']);
                                    if (!file_exists(__DIR__ . '/../' . $img_path) || empty($img_path)) {
                                        $img_path = 'assets/images/hero.png';
                                    }
                                    ?>
                                    <img src="../<?php echo $img_path; ?>" alt="Poster" style="width: 45px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid var(--admin-border);">
                                </td>
                                <td>
                                    <strong><?php echo sanitize($show['titel']); ?></strong>
                                    <?php if ($is_past): ?>
                                        <span style="color: var(--status-canceled); font-size: 0.75rem; display: block; font-weight: bold;">(AFGELOPEN)</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo sanitize($show['zaal']); ?></td>
                                <td><?php echo formatteerGeld($show['prijs']); ?></td>
                                <td>
                                    <?php echo $show['beschikbare_plaatsen']; ?> / <?php echo $show['max_plaatsen']; ?>
                                    <?php if ($show['beschikbare_plaatsen'] <= 0): ?>
                                        <span class="badge badge-geannuleerd" style="font-size: 0.65rem; margin-left: 5px;">VOL</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo formatteerDatum($show['datum_tijd'], true); ?></td>
                                <td>
                                    <?php if ($show['populair']): ?>
                                        <span class="badge badge-nieuw" style="font-size: 0.7rem; background-color: rgba(255,42,95,0.15);">POPULAIR</span>
                                    <?php else: ?>
                                        <span style="color: var(--admin-text-muted); font-size: 0.85rem;">Normaal</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 30px 0; color: var(--admin-text-muted);">
                                Geen voorstellingen ingepland in de database.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php endif; ?>

<?php
// Inclusief footer
include '../includes/admin_footer.php';
?>
