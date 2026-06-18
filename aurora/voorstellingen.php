<?php
/**
 * Voorstellingen programmering - Aurora Theater
 * 
 * Toont alle geplande voorstellingen met zoek- en filteropties.
 * Indien een ID is meegegeven wordt de detailpagina getoond.
 */

// Header inladen (db.php & functions.php worden geladen)
include 'includes/header.php';

// Controleer of er een specifieke voorstelling geselecteerd is voor details
$detail_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$show_detail = null;

if ($detail_id > 0) {
    // Haal details op voor geselecteerde voorstelling
    $detail_query = "SELECT * FROM voorstellingen WHERE id = ?";
    if ($stmt = $conn->prepare($detail_query)) {
        $stmt->bind_param('i', $detail_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $show_detail = $result->fetch_assoc();
            }
        }
        $stmt->close();
    }
}
?>

<main class="py-5">
    <div class="container">
        
        <?php if ($show_detail): ?>
            <!-- ==========================================================
               DETAIL VIEW VAN EEN VOORSTELLING
               ========================================================== -->
            <div class="detail-container my-4">
                <a href="voorstellingen.php" class="btn-secondary" style="margin-bottom: 25px; display: inline-flex; align-items: center; gap: 8px;">
                    ← Terug naar programma
                </a>
                
                <div class="booking-grid">
                    <!-- Linkerkolom: Poster en info -->
                    <div class="show-detail-card" style="background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--border-radius-lg); overflow: hidden; display: grid; grid-template-columns: 350px 1fr; min-height: 450px;">
                        
                        <div class="detail-img-wrapper" style="position: relative; overflow: hidden;">
                            <?php 
                            $img_path = sanitize($show_detail['afbeelding']);
                            if (!file_exists(__DIR__ . '/' . $img_path) || empty($img_path)) {
                                $img_path = 'assets/images/hero.png';
                            }
                            ?>
                            <img src="<?php echo $img_path; ?>" alt="<?php echo sanitize($show_detail['titel']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php if ($show_detail['populair']): ?>
                                <span class="badge-populair">POPULAIR</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="detail-content-wrapper" style="padding: 40px; display: flex; flex-direction: column;">
                            <span style="color: var(--primary); font-weight: 600; font-size: 0.9rem; text-transform: uppercase; margin-bottom: 10px;">
                                <?php echo formatteerDatum($show_detail['datum_tijd']); ?>
                            </span>
                            
                            <h2 style="font-family: var(--font-serif); font-size: 2.5rem; margin-bottom: 20px; line-height: 1.2;">
                                <?php echo sanitize($show_detail['titel']); ?>
                            </h2>
                            
                            <p style="color: var(--text-muted); line-height: 1.7; margin-bottom: 30px; flex-grow: 1;">
                                <?php echo nl2br(sanitize($show_detail['beschrijving'])); ?>
                            </p>
                            
                            <div class="detail-specs" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; border-top: 1px solid var(--border-color); padding-top: 25px; margin-bottom: 25px;">
                                <div>
                                    <span style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-bottom: 5px;">Theaterzaal</span>
                                    <strong><?php echo sanitize($show_detail['zaal']); ?></strong>
                                </div>
                                <div>
                                    <span style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-bottom: 5px;">Prijs</span>
                                    <strong><?php echo formatteerGeld($show_detail['prijs']); ?></strong>
                                </div>
                                <div>
                                    <span style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-bottom: 5px;">Beschikbaarheid</span>
                                    <?php if ($show_detail['beschikbare_plaatsen'] <= 0): ?>
                                        <span style="color: var(--error); font-weight: bold;">Uitverkocht</span>
                                    <?php elseif ($show_detail['beschikbare_plaatsen'] < 20): ?>
                                        <span style="color: var(--warning); font-weight: bold;"><?php echo $show_detail['beschikbare_plaatsen']; ?> stoelen</span>
                                    <?php else: ?>
                                        <span style="color: var(--success); font-weight: bold;"><?php echo $show_detail['beschikbare_plaatsen']; ?> stoelen</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 15px;">
                                <?php if ($show_detail['beschikbare_plaatsen'] > 0): ?>
                                    <a href="tickets.php?voorstelling_id=<?php echo $show_detail['id']; ?>" class="btn-primary" style="flex-grow: 1;">
                                        Boek Tickets
                                    </a>
                                <?php else: ?>
                                    <button class="btn-secondary" style="flex-grow: 1; cursor: not-allowed; opacity: 0.6;" disabled>
                                        Uitverkocht
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- ==========================================================
               PROGRAMMERING OVERZICHT (MET ZOEK- & FILTERBALK)
               ========================================================== -->
            <div class="text-center">
                <h1 class="section-title">Programma</h1>
                <p class="section-subtitle">Ontdek onze nieuwste en populaire voorstellingen</p>
            </div>
            
            <!-- Filterbalk -->
            <div class="catalog-filter">
                <?php
                // Filter parameters
                $search_query = sanitize($_GET['q'] ?? '');
                $date_filter = sanitize($_GET['datum'] ?? '');
                ?>
                <form action="voorstellingen.php" method="GET" class="filter-form">
                    <div class="form-group">
                        <label for="search-input">Zoek voorstelling</label>
                        <input type="text" id="search-input" name="q" class="form-control" placeholder="Titel of omschrijving..." value="<?php echo $search_query; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="date-input">Datum vanaf</label>
                        <input type="date" id="date-input" name="datum" class="form-control" value="<?php echo $date_filter; ?>">
                    </div>
                    
                    <div class="filter-buttons">
                        <button type="submit" class="btn-primary"><span>Filter</span></button>
                        <?php if (!empty($search_query) || !empty($date_filter)): ?>
                            <a href="voorstellingen.php" class="btn-secondary">Wissen</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- SQL query opbouwen met filters -->
            <?php
            $query_string = "SELECT * FROM voorstellingen WHERE datum_tijd >= NOW()";
            $params = [];
            $types = "";
            
            if (!empty($search_query)) {
                $query_string .= " AND (titel LIKE ? OR beschrijving LIKE ?)";
                $search_param = "%" . $search_query . "%";
                $params[] = $search_param;
                $params[] = $search_param;
                $types .= "ss";
            }
            
            if (!empty($date_filter)) {
                $query_string .= " AND DATE(datum_tijd) >= ?";
                $params[] = $date_filter;
                $types .= "s";
            }
            
            $query_string .= " ORDER BY datum_tijd ASC";
            
            if ($stmt = $conn->prepare($query_string)) {
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $result = $conn->query($query_string);
            }
            
            // Foutafhandeling en Lege Database/Filter Scenario
            if ($result && $result->num_rows > 0):
            ?>
                <div class="shows-grid">
                    <?php while($show = $result->fetch_assoc()): ?>
                        <article class="show-card">
                            <div class="card-img-wrapper">
                                <?php 
                                $img_path = sanitize($show['afbeelding']);
                                if (!file_exists(__DIR__ . '/' . $img_path) || empty($img_path)) {
                                    $img_path = 'assets/images/hero.png';
                                }
                                ?>
                                <img src="<?php echo $img_path; ?>" alt="<?php echo sanitize($show['titel']); ?>" class="card-img">
                                <?php if ($show['populair']): ?>
                                    <span class="badge-populair">POPULAIR</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-content">
                                <div class="card-date"><?php echo formatteerDatum($show['datum_tijd']); ?></div>
                                <h3 class="card-title"><?php echo sanitize($show['titel']); ?></h3>
                                <p class="card-desc"><?php echo sanitize($show['beschrijving']); ?></p>
                                <div class="card-footer">
                                    <span class="card-price"><?php echo formatteerGeld($show['prijs']); ?></span>
                                    <a href="voorstellingen.php?id=<?php echo $show['id']; ?>" class="btn-primary btn-card">Meer info</a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">🎟️</div>
        <?php if (!empty($search_query) || !empty($date_filter)): ?>
            <!-- Geen resultaten door actief filter -->
            <h3>Geen voorstellingen gevonden</h3>
            <p>Er zijn op dit moment geen voorstellingen beschikbaar die voldoen aan uw zoekcriteria.</p>
            <a href="voorstellingen.php" class="btn-primary">Bekijk alle voorstellingen</a>
        <?php else: ?>
            <!-- Unhappy Scenario: database bevat geen voorstellingen -->
            <h3>Er zijn momenteel geen voorstellingen beschikbaar</h3>
            <p>Er zijn momenteel geen voorstellingen ingepland. Kom later terug voor de nieuwe programmering.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>
        <?php endif; ?>
        
    </div>
</main>

<?php 
// Detail view specifieke responsive styles in-line toevoegen voor grids
if ($show_detail):
?>
<style>
@media (max-width: 768px) {
    .show-detail-card {
        grid-template-columns: 1fr !important;
    }
    .detail-img-wrapper {
        height: 300px;
    }
}
</style>
<?php
endif;

// Footer inladen
include 'includes/footer.php'; 
?>
