<?php
/**
 * Footer - Aurora Theater
 * 
 * Renders de footer en sluit de HTML tags. Inclusief JS inladen.
 */
?>
    <footer class="site-footer">
        <div class="footer-content container">
            
            <!-- Kolom 1: Over Aurora -->
            <div class="footer-col brand-col">
                <h3 class="footer-logo"><span class="logo-accent">A</span>urora</h3>
                <p class="brand-desc">
                    Beleef de magie van het live podium en het witte doek. Bij Aurora Theater geniet u van exclusieve voorstellingen in een luxe omgeving.
                </p>
                <div class="socials">
                    <a href="#" aria-label="Facebook"><span>FB</span></a>
                    <a href="#" aria-label="Instagram"><span>IG</span></a>
                    <a href="#" aria-label="Twitter"><span>TW</span></a>
                    <a href="#" aria-label="YouTube"><span>YT</span></a>
                </div>
            </div>

            <!-- Kolom 2: Navigatie links -->
            <div class="footer-col links-col">
                <h4>Navigatie</h4>
                <ul>
                    <li><a href="<?php echo getRootUrl(); ?>index.php">Home</a></li>
                    <li><a href="<?php echo getRootUrl(); ?>voorstellingen.php">Voorstellingen</a></li>
                    <li><a href="<?php echo getRootUrl(); ?>tickets.php">Tickets</a></li>
                    <li><a href="<?php echo getRootUrl(); ?>login.php">Inloggen</a></li>
                    <li><a href="<?php echo getRootUrl(); ?>register.php">Registreren</a></li>
                </ul>
            </div>

            <!-- Kolom 3: Contact Gegevens -->
            <div class="footer-col contact-col" id="footer-contact">
                <h4>Contact</h4>
                <p>
                    <strong>Adres:</strong><br>
                    <?php echo sanitize(getSetting('adres_straat', 'Theaterplein 1')); ?><br>
                    <?php echo sanitize(getSetting('adres_stad', 'Amsterdam')); ?>
                </p>
                <p>
                    <strong>Telefoon:</strong><br>
                    <?php echo sanitize(getSetting('contact_telefoon', '020-1234567')); ?>
                </p>
                <p>
                    <strong>E-mail:</strong><br>
                    <a href="mailto:<?php echo sanitize(getSetting('contact_email', 'info@auroratheater.nl')); ?>">
                        <?php echo sanitize(getSetting('contact_email', 'info@auroratheater.nl')); ?>
                    </a>
                </p>
            </div>

            <!-- Kolom 4: Nieuwsbrief -->
            <div class="footer-col newsletter-col">
                <h4>Nieuwsbrief</h4>
                <p>Meld u aan voor onze nieuwsbrief om op de hoogte te blijven van nieuwe voorstellingen en acties.</p>
                
                <form action="<?php echo getRootUrl(); ?>index.php" method="POST" class="newsletter-form">
                    <input type="email" name="newsletter_email" placeholder="Uw e-mailadres" required aria-label="E-mailadres voor nieuwsbrief">
                    <button type="submit" name="action" value="newsletter_subscribe" class="btn-primary btn-newsletter">
                        <span>Aanmelden</span>
                    </button>
                </form>
            </div>

        </div>

        <div class="footer-bottom container">
            <p>&copy; <?php echo date('Y'); ?> Aurora Theater. Alle rechten voorbehouden. Ontworpen voor schoolproject.</p>
        </div>
    </footer>

    <!-- Hoofd JS bestand -->
    <script src="<?php echo getRootUrl(); ?>assets/js/app.js"></script>
</body>
</html>
