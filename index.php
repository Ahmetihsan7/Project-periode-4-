<?php
/**
 * Homepage - Aurora Theater
 * 
 * Bevat hero banner, populaire voorstellingen en contact formulier.
 */

// Paginatitel instellen
$page_title = "Welkom bij het premium theater";

// Header inladen (dit laadt automatisch db.php en functions.php)
include 'includes/header.php';

// Formulier afhandeling (Contact & Nieuwsbrief)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    
    // Happy & Unhappy Scenario 1: Contactformulier verzenden
    if ($action === 'contact_submit') {
        $naam = sanitize($_POST['naam'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $onderwerp = sanitize($_POST['onderwerp'] ?? '');
        $bericht = sanitize($_POST['bericht'] ?? '');
        
        // Unhappy scenario: lege velden
        if (empty($naam) || empty($email) || empty($onderwerp) || empty($bericht)) {
            setFlashMessage('error', 'Mislukt: Alle velden in het contactformulier zijn verplicht.');
        } 
        // Valideer e-mail
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlashMessage('error', 'Mislukt: Vul a.u.b. een geldig e-mailadres in.');
        }
        else {
            // Sla op in database met prepared statements
            $query = "INSERT INTO meldingen (naam, email, onderwerp, bericht, status) VALUES (?, ?, ?, ?, 'nieuw')";
            if ($stmt = $conn->prepare($query)) {
                $stmt->bind_param('ssss', $naam, $email, $onderwerp, $bericht);
                if ($stmt->execute()) {
                    // Happy scenario
                    setFlashMessage('success', 'Uw bericht is succesvol verzonden. We nemen zo snel mogelijk contact met u op!');
                    // Redirect om dubbele form submission te voorkomen
                    header('Location: index.php#contact');
                    exit;
                } else {
                    setFlashMessage('error', 'Databasefout: Bericht kon niet worden opgeslagen.');
                }
                $stmt->close();
            }
        }
    }
    
    // Happy & Unhappy Scenario 2: Nieuwsbrief aanmelden
    elseif ($action === 'newsletter_subscribe') {
        $email = sanitize($_POST['newsletter_email'] ?? '');
        
        if (empty($email)) {
            setFlashMessage('error', 'Mislukt: E-mailadres mag niet leeg zijn.');
        } 
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlashMessage('error', 'Mislukt: Vul a.u.b. een geldig e-mailadres in.');
        }
        else {
            // Happy scenario (simulatie)
            setFlashMessage('success', 'Succes! U bent succesvol aangemeld voor de Aurora nieuwsbrief.');
            header('Location: index.php');
            exit;
        }
    }
}
?>

<!-- Hero Sectie -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h2>
                Beleef de magie van<br>
                <span>Aurora Theater</span>
            </h2>
            <p>
                Geniet van de meest meeslepende shows, adembenemende opera en hilarische comedy. Reserveer eenvoudig uw premium stoelen online.
            </p>
            <div class="hero-buttons">
                <a href="voorstellingen.php" class="btn-primary">Bekijk Voorstellingen</a>
                <a href="tickets.php" class="btn-secondary">Tickets Reserveren</a>
            </div>
        </div>
    </div>
</section>

<!-- Populaire Voorstellingen Sectie -->
<section class="py-5">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">Populaire Voorstellingen</h2>
            <p class="section-subtitle">De meest geboekte shows van dit moment</p>
        </div>

        <?php
        // Haal populaire shows op die in de toekomst liggen
        $shows_query = "SELECT * FROM voorstellingen WHERE populair = 1 AND datum_tijd >= NOW() ORDER BY datum_tijd ASC LIMIT 3";
        $result = $conn->query($shows_query);
        
        // Foutafhandeling en Lege Database Scenario (Functie controleert of er rijen zijn)
        if ($result && $result->num_rows > 0):
        ?>
            <div class="shows-grid">
                <?php while($show = $result->fetch_assoc()): ?>
                    <article class="show-card">
                        <div class="card-img-wrapper">
                            <!-- Als afbeeldingbestand niet bestaat, toon een dummy poster -->
                            <?php 
                            $img_path = sanitize($show['afbeelding']);
                            if (!file_exists(__DIR__ . '/' . $img_path) || empty($img_path)) {
                                $img_path = 'assets/images/hero.png'; // Fallback
                            }
                            ?>
                            <img src="<?php echo $img_path; ?>" alt="<?php echo sanitize($show['titel']); ?>" class="card-img">
                            <span class="badge-populair">POPULAIR</span>
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
            <!-- Happy scenario voor lege database: geen PHP errors, maar een nette melding -->
            <div class="empty-state">
                <div class="empty-state-icon">🎬</div>
                <h3>Geen populaire voorstellingen</h3>
                <p>Er zijn momenteel geen populaire voorstellingen ingepland. Bekijk onze volledige programmering.</p>
                <a href="voorstellingen.php" class="btn-primary">Alle voorstellingen</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Contact & Info Sectie -->
<section class="py-5 contact-section" id="contact">
    <div class="container contact-grid">
        
        <!-- Contact details -->
        <div class="contact-info-panel">
            <h3>Plan uw bezoek</h3>
            <p>Hebt u vragen over uw boeking, groepsreserveringen of gevonden voorwerpen? Neem gerust contact met ons op via het contactformulier of via onze direct gegevens.</p>
            
            <div class="contact-info-list">
                <div class="contact-info-item">
                    <span class="icon">📍</span>
                    <div>
                        <strong>Locatie</strong><br>
                        <?php echo sanitize(getSetting('adres_straat', 'Theaterplein 1')); ?>, <?php echo sanitize(getSetting('adres_stad', 'Amsterdam')); ?>
                    </div>
                </div>
                <div class="contact-info-item">
                    <span class="icon">📞</span>
                    <div>
                        <strong>Telefoonnummer</strong><br>
                        <?php echo sanitize(getSetting('contact_telefoon', '020-1234567')); ?>
                    </div>
                </div>
                <div class="contact-info-item">
                    <span class="icon">✉️</span>
                    <div>
                        <strong>E-mail</strong><br>
                        <?php echo sanitize(getSetting('contact_email', 'info@auroratheater.nl')); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact formulier -->
        <div class="contact-form-panel">
            <h3>Stuur ons een bericht</h3>
            <form action="index.php" method="POST" class="my-4">
                <div class="form-group">
                    <label for="naam">Volledige Naam</label>
                    <input type="text" id="naam" name="naam" class="form-control" placeholder="Uw naam" required>
                </div>
                <div class="form-group">
                    <label for="email">E-mailadres</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="naam@voorbeeld.nl" required>
                </div>
                <div class="form-group">
                    <label for="onderwerp">Onderwerp</label>
                    <input type="text" id="onderwerp" name="onderwerp" class="form-control" placeholder="Waar gaat uw vraag over?" required>
                </div>
                <div class="form-group">
                    <label for="bericht">Uw Bericht</label>
                    <textarea id="bericht" name="bericht" class="form-control" placeholder="Schrijf hier uw bericht..." required></textarea>
                </div>
                <button type="submit" name="action" value="contact_submit" class="btn-primary" style="width: 100%;">
                    <span>Versturen</span>
                </button>
            </form>
        </div>

    </div>
</section>

<?php 
// Footer inladen
include 'includes/footer.php'; 
?>
<!-- Homepage update -->
