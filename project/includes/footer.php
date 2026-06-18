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
                <li><a href="/index.php">🏠 Home</a></li>
                <li><a href="/shows.php">🎭 Shows</a></li>
                <?php if (isset($_SESSION['gebruiker_id'])): ?>
                    <li><a href="/bezoeker/mijn-tickets.php">🎟️ Mijn tickets</a></li>
                    <li><a href="/bezoeker/profiel.php">👤 Mijn profiel</a></li>
                    <li><a href="/bezoeker/account-instellingen.php">⚙️ Instellingen</a></li>
                <?php else: ?>
                    <li><a href="/auth/login.php">🔑 Inloggen</a></li>
                    <li><a href="/auth/register.php">✨ Registreren</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Info kolom -->
        <div class="footer-col">
            <h3 class="footer-heading">Informatie</h3>
            <ul class="footer-links">
                <li><a href="#">📖 Over ons</a></li>
                <li><a href="#">❓ Veelgestelde vragen</a></li>
                <li><a href="#">🔒 Privacybeleid</a></li>
                <li><a href="#">📜 Algemene voorwaarden</a></li>
            </ul>
        </div>

        <!-- Contact kolom -->
        <div class="footer-col">
            <h3 class="footer-heading">Contact</h3>
            <ul class="footer-links">
                <li>📧 info@ticketapp.nl</li>
                <li>📞 020 123 4567</li>
                <li>📍 Amsterdam, Nederland</li>
                <li style="margin-top:12px; color:var(--text-muted); font-size:12px;">Ma–Vr: 09:00–17:00</li>
            </ul>
        </div>

    </div>

    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> TicketApp — Gemaakt met ❤️ voor de beste showervaring.</p>
    </div>
</footer>

<!-- JavaScript -->
<script src="/assets/JS/main.js"></script>
</body>
</html>
