<?php
/**
 * Gebruikersbeheer (Accounts) - Aurora Theater Admin
 * 
 * READ-ONLY: Toont een overzicht van alle gebruikersaccounts.
 * Alleen toegankelijk voor admins.
 */

// Inclusief header (en auth-controle)
include '../includes/admin_header.php';

// Extra veiligheid: Alleen admins mogen hier komen
if (!hasRole('admin')) {
    setFlashMessage('error', 'Toegang geweigerd: Alleen beheerders mogen accounts bekijken.');
    header('Location: dashboard.php');
    exit;
}
?>

<!-- Overzichtstabel van alle gebruikers (READ ONLY) -->
<div class="table-panel">
    <div class="panel-header">
        <h3>Overzicht Geregistreerde Accounts</h3>
        <span class="badge badge-gelezen" style="padding: 6px 14px; font-size: 0.8rem;">👁️ Alleen lezen</span>
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
                        </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 30px 0; color: var(--admin-text-muted);">
                            Geen accounts gevonden in de database.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
