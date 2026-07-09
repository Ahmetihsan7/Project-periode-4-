<?php
/**
 * Gebruikersbeheer (Accounts) - Aurora Theater Admin
 * 
 * Beheert de gebruikersaccounts.
 * Alleen toegankelijk voor admins.
 */

// Laad db en functies om redirects te kunnen verwerken vóór HTML output
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Extra veiligheid: Alleen admins mogen hier komen
checkAccess(['admin']);

$action = sanitize($_GET['action'] ?? 'list');
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verwerk DELETE actie
if ($action === 'delete' && $user_id > 0) {
    try {
        // Gebruik PDO prepared statement om account te verwijderen
        $stmt = $pdo->prepare("DELETE FROM gebruikers WHERE id = ?");
        $stmt->execute([$user_id]);
        
        // Controleer of er daadwerkelijk een rij is verwijderd
        if ($stmt->rowCount() > 0) {
            setFlashMessage('success', 'Gegevens succesvol verwijderd');
        } else {
            setFlashMessage('error', 'Gegevens konden niet worden verwijderd');
        }
    } catch (PDOException $e) {
        // Foutafhandeling bij database errors (bijv. foreign keys)
        setFlashMessage('error', 'Gegevens konden niet worden verwijderd');
    }
    
    header('Location: accounts.php');
    exit;
}

// Inclusief header (HTML start)
include '../includes/admin_header.php';
?>

<!-- Overzichtstabel van alle gebruikers -->
<div class="table-panel">
    <div class="panel-header">
        <h3>Overzicht Geregistreerde Accounts</h3>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="accounts/create.php" class="btn-primary" style="text-decoration: none; padding: 8px 16px; font-size: 0.85rem; border-radius: 20px; font-weight: 600;">+ Nieuw Account</a>
        </div>
    </div>

    <!-- Live zoekbalk (JavaScript client-side filter) -->
    <div class="table-filter-bar">
        <div class="search-box">
            <input type="text" id="table-search" placeholder="Zoek op naam of email...">
        </div>
    </div>

    <?php
    // Haal alle accounts op uit de database
    $query = "SELECT * FROM gebruikers ORDER BY rol ASC, naam ASC";
    $result = $conn->query($query);
    ?>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Naam</th>
                    <th>E-mail</th>
                    <th>Rol</th>
                    <th>Geregistreerd op</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0):
                    while ($user = $result->fetch_assoc()):
                ?>
                        <tr>
                            <td>#<?php echo $user['id']; ?></td>
                            <td><strong><?php echo sanitize($user['naam']); ?></strong></td>
                            <td><?php echo sanitize($user['email']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $user['rol']; ?>">
                                    <?php echo $user['rol']; ?>
                                </span>
                            </td>
                            <td><?php echo date('d-m-Y H:i', strtotime($user['gemaakt_op'])); ?></td>
                            <td class="action-buttons">
                                <a href="accounts/edit.php?id=<?php echo $user['id']; ?>" class="btn-action" style="width: auto; padding: 0 10px; gap: 5px;" title="Wijzigen">✏️ Wijzigen</a>
                                <a href="accounts.php?action=delete&id=<?php echo $user['id']; ?>" class="btn-action btn-delete" style="width: auto; padding: 0 10px; gap: 5px;" title="Verwijderen" onclick="return confirm('Weet u zeker dat u dit record wilt verwijderen?');">🗑️ Verwijderen</a>
                            </td>
                        </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 30px 0; color: var(--admin-text-muted);">
                            Geen gegevens gevonden.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
