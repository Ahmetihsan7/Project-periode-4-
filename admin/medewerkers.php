<?php
/**
 * Medewerkersbeheer - Aurora Theater Admin
 * 
 * Beheert de contractdetails voor medewerkers en admins.
 * Alleen toegankelijk voor admins.
 */

// Laad db en functies om redirects te kunnen verwerken vóór HTML output
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Controleer toegang vóór redirect
checkAccess(['admin', 'medewerker']);

// Veiligheid check: Alleen admins mogen hier komen
if (!hasRole('admin')) {
    setFlashMessage('error', 'Toegang geweigerd: Alleen beheerders mogen medewerkers beheren.');
    header('Location: dashboard.php');
    exit;
}

$action = sanitize($_GET['action'] ?? 'list');
$employee_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Blokkeer POST verzoeken
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    setFlashMessage('error', 'Fout: Aanmaken of bewerken van medewerkerscontracten is uitgeschakeld.');
    header('Location: medewerkers.php');
    exit;
}

// Blokkeer delete acties
if ($action === 'delete' && $employee_id > 0) {
    setFlashMessage('error', 'Fout: Verwijderen van medewerkerscontracten is uitgeschakeld.');
    header('Location: medewerkers.php');
    exit;
}

// Blokkeer add en edit weergaves
if ($action === 'add' || $action === 'edit') {
    setFlashMessage('error', 'Fout: Koppelen of bewerken van contracten is uitgeschakeld.');
    header('Location: medewerkers.php');
    exit;
}

// Inclusief header (HTML start)
include '../includes/admin_header.php';



if ($action === 'add' || $action === 'edit'):
    $emp_data = ['gebruiker_id' => 0, 'functie' => '', 'salaris' => '', 'aangenomen_op' => date('Y-m-d')];
    $unlinked_users = [];
    
    if ($action === 'edit' && $employee_id > 0) {
        // Haal gegevens op van de specifieke medewerker
        $query = "SELECT m.*, g.naam FROM medewerkers m JOIN gebruikers g ON m.gebruiker_id = g.id WHERE m.id = ?";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param('i', $employee_id);
            $stmt->execute();
            $emp_data = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
        
        if (!$emp_data) {
            setFlashMessage('error', 'Medewerker niet gevonden.');
            header('Location: medewerkers.php');
            exit;
        }
    } else {
        // Haal alle gebruikers op met de rol 'medewerker' of 'admin' die nog GEEN contract hebben
        $users_query = "SELECT id, naam, rol FROM gebruikers WHERE rol IN ('medewerker', 'admin') AND id NOT IN (SELECT gebruiker_id FROM medewerkers) ORDER BY naam ASC";
        $unlinked_users = $conn->query($users_query);
    }
?>

    <div class="admin-card">
        <h3><?php echo ($action === 'add') ? 'Contractkoppeling Toevoegen' : 'Contract Bewerken'; ?></h3>
        
        <form action="medewerkers.php?action=<?php echo $action; ?>&id=<?php echo $employee_id; ?>" method="POST" class="my-4">
            <input type="hidden" name="form_action" value="<?php echo ($action === 'add') ? 'create' : 'update'; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="gebruiker_id">Koppel aan Gebruikersaccount</label>
                    <?php if ($action === 'edit'): ?>
                        <!-- Bij bewerken is de koppeling vast -->
                        <input type="text" class="form-control" value="<?php echo sanitize($emp_data['naam']); ?>" disabled>
                        <input type="hidden" name="gebruiker_id" value="<?php echo $emp_data['gebruiker_id']; ?>">
                    <?php else: ?>
                        <!-- Bij toevoegen toon je de dropdown -->
                        <select id="gebruiker_id" name="gebruiker_id" class="form-control" required>
                            <option value="">-- Kies een account --</option>
                            <?php 
                            if ($unlinked_users && $unlinked_users->num_rows > 0):
                                while($u = $unlinked_users->fetch_assoc()):
                            ?>
                                    <option value="<?php echo $u['id']; ?>">
                                        <?php echo sanitize($u['naam']) . " (" . $u['rol'] . ")"; ?>
                                    </option>
                            <?php 
                                endwhile;
                            else:
                            ?>
                                <option value="" disabled>Geen losse medewerker/admin accounts gevonden</option>
                            <?php endif; ?>
                        </select>
                        <span style="font-size: 0.75rem; color: var(--admin-text-muted); display: block; margin-top: 5px;">
                            Staat de gewenste persoon er niet bij? Maak dan eerst een account aan met rol 'medewerker' in <a href="accounts.php" style="color: var(--admin-primary);">Accounts</a>.
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="functie">Functieomschrijving</label>
                    <input type="text" id="functie" name="functie" class="form-control" placeholder="Bijv. Kassa & Klantenservice" value="<?php echo sanitize($emp_data['functie']); ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="salaris">Bruto Maandsalaris (€)</label>
                    <input type="number" step="0.01" min="0" id="salaris" name="salaris" class="form-control" placeholder="Bruto bedrag" value="<?php echo $emp_data['salaris']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="aangenomen_op">Aangenomen Op</label>
                    <input type="date" id="aangenomen_op" name="aangenomen_op" class="form-control" value="<?php echo $emp_data['aangenomen_op']; ?>" required>
                </div>
            </div>
            
            <div class="my-4" style="display: flex; gap: 10px;">
                <button type="submit" class="btn-primary">
                    <span><?php echo ($action === 'add') ? 'Contract Opslaan' : 'Wijzigingen Opslaan'; ?></span>
                </button>
                <a href="medewerkers.php" class="btn-secondary">Annuleren</a>
            </div>
        </form>
    </div>

<?php else: ?>

    <!-- ==========================================================
       LIST VIEW (Tabel overzicht van medewerkers)
       ========================================================== -->
    <div class="table-panel">
        <div class="panel-header">
            <h3>Medewerkersovereenkomsten</h3>
            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="medewerkers/create.php" class="btn-primary" style="text-decoration: none; padding: 8px 16px; font-size: 0.85rem; border-radius: 20px; font-weight: 600;">+ Nieuwe Medewerker</a>
            </div>
        </div>
        
        <div class="table-filter-bar">
            <div class="search-box">
                <input type="text" id="table-search" placeholder="Zoek op naam, email of functie...">
            </div>
        </div>
        
        <?php
        // Haal alle contracten op
        $query = "SELECT m.*, g.naam, g.email, g.rol FROM medewerkers m JOIN gebruikers g ON m.gebruiker_id = g.id ORDER BY g.naam ASC";
        $result = $conn->query($query);
        ?>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Naam</th>
                        <th>E-mail</th>
                        <th>Functie</th>
                        <th>Salaris</th>
                        <th>Aangenomen Op</th>
                        <th>Gebruikersrol</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && $result->num_rows > 0):
                        while ($emp = $result->fetch_assoc()):
                    ?>
                            <tr>
                                <td>#<?php echo $emp['id']; ?></td>
                                <td><strong><?php echo sanitize($emp['naam']); ?></strong></td>
                                <td><?php echo sanitize($emp['email']); ?></td>
                                <td><?php echo sanitize($emp['functie']); ?></td>
                                <td><?php echo !empty($emp['salaris']) ? formatteerGeld($emp['salaris']) : 'N.v.t.'; ?></td>
                                <td><?php echo date('d-m-Y', strtotime($emp['aangenomen_op'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $emp['rol']; ?>">
                                        <?php echo $emp['rol']; ?>
                                    </span>
                                </td>
                            </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 30px 0; color: var(--admin-text-muted);">
                                Geen gekoppelde medewerkerscontracten gevonden.
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
