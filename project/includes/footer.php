<?php
// =============================================
// Footer — geladen op elke pagina
// =============================================
?>
<!-- ===================== FOOTER ===================== -->
<footer class="footer">
    <div class="footer-inner">

        <!-- Brand kolom -->
        <div class="footer-col">
            <div class="footer-logo">
                <span class="logo-icon">🎭</span>
                <span>TicketApp</span>
            </div>
            <p class="footer-tagline">De beste shows, direct in jouw handen. Koop jouw tickets snel en veilig online.</p>
        </div>

        <!-- Navigatie kolom -->
        <div class="footer-col">
            <h3 class="footer-heading">Navigatie</h3>
            <ul class="footer-links">
                <li><a href="/index.php">Home</a></li>
                <li><a href="/shows.php">Shows</a></li>
                <li><a href="/auth/login.php">Inloggen</a></li>
                <li><a href="/auth/register.php">Registreren</a></li>
            </ul>
        </div>

        <!-- Info kolom -->
        <div class="footer-col">
            <h3 class="footer-heading">Informatie</h3>
            <ul class="footer-links">
                <li><a href="#">Over ons</a></li>
                <li><a href="#">Veelgestelde vragen</a></li>
                <li><a href="#">Privacybeleid</a></li>
                <li><a href="#">Contacteer ons</a></li>
            </ul>
        </div>

        <!-- Contact kolom -->
        <div class="footer-col">
            <h3 class="footer-heading">Contact</h3>
            <ul class="footer-links">
                <li>📧 info@ticketapp.nl</li>
                <li>📞 020 123 4567</li>
                <li>📍 Amsterdam, Nederland</li>
            </ul>
        </div>

    </div>

    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> TicketApp. Alle rechten voorbehouden.</p>
    </div>
</footer>

<!-- JavaScript -->
<script src="/assets/JS/main.js"></script>
</body>
</html>
