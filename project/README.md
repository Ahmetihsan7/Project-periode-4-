# 🎭 TicketApp — Project P4

Een webapplicatie voor het beheren en kopen van tickets voor shows en evenementen.

## 📋 Beschrijving

TicketApp is een PHP-webapplicatie waarmee:
- **Bezoekers** shows kunnen bekijken en tickets kunnen kopen
- **Medewerkers** tickets kunnen scannen en valideren
- **Admins** shows en medewerkers kunnen beheren

## 🏗️ Projectstructuur

```
project/
├── admin/          # Admin-paneel pagina's
├── auth/           # Login, registratie en uitloggen
├── bezoeker/       # Bezoeker dashboard en tickets
├── employee/       # Medewerker dashboard
├── includes/       # Gedeelde functies, header en footer
├── assets/         # CSS, afbeeldingen en JS
├── config/         # Databaseconfiguratie
├── index.php       # Homepage
├── shows.php       # Overzicht van alle shows
└── database.sql    # Database structuur
```

## 👥 Rollen

| Rol         | Rechten                                      |
|-------------|----------------------------------------------|
| Bezoeker    | Shows bekijken, tickets kopen, eigen tickets |
| Medewerker  | Tickets scannen en valideren                 |
| Admin       | Alles beheren (shows, medewerkers, tickets)  |

## 🚀 Installatie

1. Clone de repository
2. Importeer `database.sql` in je MySQL-database
3. Pas `config/database.php` aan met jouw gegevens
4. Start een lokale PHP-server (bijv. via XAMPP of Laragon)
5. Open `http://localhost/` in je browser

## 🔐 Testaccounts

| Rol         | E-mail                  | Wachtwoord |
|-------------|-------------------------|------------|
| Admin       | admin@ticketapp.nl      | password   |
| Medewerker  | jan@ticketapp.nl        | password   |
| Bezoeker    | maria@ticketapp.nl      | password   |

## 🛠️ Technologieën

- **PHP 8+** — Server-side logica
- **MySQL** — Database
- **HTML/CSS** — Frontend styling
- **PDO** — Veilige databaseverbindingen (prepared statements)

## ✅ Functionaliteiten

- [x] Gebruikersregistratie en login
- [x] Rolgebaseerde toegangscontrole (admin / medewerker / bezoeker)
- [x] Shows bekijken en tickets kopen
- [x] QR-code ticketvalidatie voor medewerkers
- [x] Admin dashboard met statistieken
- [x] Medewerker ticketscanner
- [x] Bezoeker profielpagina
- [x] Account aanmaken met validatie
