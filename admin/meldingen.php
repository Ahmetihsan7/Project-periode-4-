<?php
/**
 * Meldingen & Berichtenbeheer - Aurora Theater Admin
 * 
 * Beheert de contactformulier inzendingen.
 * Toegankelijk voor admins en medewerkers.
 */

// Laad db en functies om redirects te kunnen verwerken vóór HTML output
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Controleer toegang vóór redirect
checkAccess(['admin', 'medewerker']);

$action = sanitize($_GET['action'] ?? 'list');
$message_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Blokkeer delete acties
if ($action === 'delete' && $message_id > 0) {
    setFlashMessage('error', 'Fout: Verwijderen van berichten is uitgeschakeld.');
    header('Location: meldingen.php');
    exit;
}

// Blokkeer markread acties
if ($action === 'markread' && $message_id > 0) {
    setFlashMessage('error', 'Fout: Markeren als gelezen is uitgeschakeld.');
    header('Location: meldingen.php');
    exit;
}

// Inclusief header (HTML start)
include '../includes/admin_header.php';

// ==========================================
// 3. DETAIL VIEW (Bericht bekijken)
// ==========================================
if ($action === 'view' && $message_id > 0):
    $msg_data = null;
    $query = "SELECT * FROM meldingen WHERE id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('i', $message_id);
        $stmt->execute();
        $msg_data = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
    
    if (!$msg_data) {
        setFlashMessage('error', 'Bericht niet gevonden.');
        header('Location: meldingen.php');
        exit;
    }
    
    // Automatisch markeren als gelezen bij het bekijken - DISABLED (Read-only)
    /*
    if ($msg_data['status'] === 'nieuw') {
        $conn->query("UPDATE meldingen SET status = 'gelezen' WHERE id = " . $message_id);
        $msg_data['status'] = 'gelezen'; // Update in local array voor correcte weergave
    }
    */
?>

    <div class="admin-card" style="max-width: 700px;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--admin-border); padding-bottom: 15px; margin-bottom: 20px;">
            <h3>Contactbericht #<?php echo $msg_data['id']; ?></h3>
            <span class="badge badge-<?php echo $msg_data['status']; ?>"><?php echo ucfirst($msg_data['status']); ?></span>
        </div>
        
        <div style="margin-bottom: 25px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; font-size: 0.95rem;">
            <div>
                <span style="color: var(--admin-text-muted); display: block; font-size: 0.8rem;">Afzender</span>
                <strong><?php echo sanitize($msg_data['naam']); ?></strong>
            </div>
            <div>
                <span style="color: var(--admin-text-muted); display: block; font-size: 0.8rem;">E-mailadres</span>
                <strong><a href="mailto:<?php echo sanitize($msg_data['email']); ?>" style="color: var(--admin-primary);"><?php echo sanitize($msg_data['email']); ?></a></strong>
            </div>
            <div>
                <span style="color: var(--admin-text-muted); display: block; font-size: 0.8rem;">Onderwerp</span>
                <strong><?php echo sanitize($msg_data['onderwerp']); ?></strong>
            </div>
            <div>
                <span style="color: var(--admin-text-muted); display: block; font-size: 0.8rem;">Verzonden op</span>
                <strong><?php echo date('d-m-Y \o\m H:i', strtotime($msg_data['gemaakt_op'])); ?></strong>
            </div>
        </div>
        
        <div style="background-color: var(--admin-bg-dark); border: 1px solid var(--admin-border); padding: 25px; border-radius: 8px; margin-bottom: 30px; font-size: 1rem; line-height: 1.7; white-space: pre-line;">
            <?php echo sanitize($msg_data['bericht']); ?>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <a href="mailto:<?php echo sanitize($msg_data['email']); ?>?subject=Re: <?php echo rawurlencode($msg_data['onderwerp']); ?>" class="btn-primary">
                <span>✉️ Beantwoorden</span>
            </a>
            <a href="meldingen.php" class="btn-secondary">Terug naar overzicht</a>
        </div>
    </div>

<?php else: ?>

    <!-- ==========================================================
       LIST VIEW (Tabel overzicht van meldingen)
       ========================================================== -->
    <div class="table-panel">
        <div class="panel-header">
            <h3>Binnengekomen Berichten</h3>
            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="meldingen/create.php" class="btn-primary" style="text-decoration: none; padding: 8px 16px; font-size: 0.85rem; border-radius: 20px; font-weight: 600;">+ Nieuwe Melding</a>
            </div>
        </div>
        
        <div class="table-filter-bar">
            <div class="search-box">
                <input type="text" id="table-search" placeholder="Zoek op naam, email of onderwerp...">
            </div>
        </div>
        
        <?php
        // Haal alle contact meldingen op
        $query = "SELECT * FROM meldingen ORDER BY status ASC, gemaakt_op DESC";
        $result = $conn->query($query);
        ?>
        
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Naam</th>
                        <th>E-mail</th>
                        <th>Onderwerp</th>
                        <th>Datum ontvangen</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && $result->num_rows > 0):
                        while ($msg = $result->fetch_assoc()):
                            $is_new = $msg['status'] === 'nieuw';
                    ?>
                            <tr style="<?php echo $is_new ? 'background-color: rgba(255, 42, 95, 0.02); font-weight: 500;' : ''; ?>">
                                <td>
                                    <span class="badge badge-<?php echo $msg['status']; ?>">
                                        <?php echo $msg['status']; ?>
                                    </span>
                                </td>
                                <td><strong><?php echo sanitize($msg['naam']); ?></strong></td>
                                <td><?php echo sanitize($msg['email']); ?></td>
                                <td>
                                    <?php echo sanitize($msg['onderwerp']); ?>
                                    <?php if (!empty($msg['prioriteit'])): 
                                        $prioClass = '';
                                        if ($msg['prioriteit'] === 'hoog') $prioClass = 'background-color: rgba(255, 61, 0, 0.2); color: #ff3d00; border: 1px solid rgba(255, 61, 0, 0.4);';
                                        elseif ($msg['prioriteit'] === 'gemiddeld') $prioClass = 'background-color: rgba(255, 193, 7, 0.15); color: #ffc107; border: 1px solid rgba(255, 193, 7, 0.3);';
                                        else $prioClass = 'background-color: rgba(0, 230, 118, 0.15); color: #00e676; border: 1px solid rgba(0, 230, 118, 0.3);';
                                    ?>
                                        <span class="badge" style="font-size: 0.65rem; margin-left: 5px; padding: 2px 6px; border-radius: 4px; <?php echo $prioClass; ?>">
                                            <?php echo strtoupper($msg['prioriteit']); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d-m-Y H:i', strtotime($msg['gemaakt_op'])); ?></td>
                                <td class="action-buttons">
                                    <a href="meldingen.php?action=view&id=<?php echo $msg['id']; ?>" class="btn-action" title="Bekijken / Lezen">👁️</a>
                                </td>
                            </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 30px 0; color: var(--admin-text-muted);">
                                Geen berichten of meldingen gevonden in de database.
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
