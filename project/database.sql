-- =============================================
-- Database: ticketing_app
-- Ticketing systeem voor bezoekers, medewerkers en admins
-- =============================================

CREATE DATABASE IF NOT EXISTS ticketing_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ticketing_app;

-- -----------------------------------------------
-- Tabel: gebruikers (bezoekers, medewerkers, admins)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS gebruikers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naam VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    wachtwoord VARCHAR(255) NOT NULL,
    rol ENUM('bezoeker', 'medewerker', 'admin') NOT NULL DEFAULT 'bezoeker',
    aangemaakt_op DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------
-- Tabel: shows (voorstellingen)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS shows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titel VARCHAR(200) NOT NULL,
    beschrijving TEXT,
    datum DATE NOT NULL,
    tijd TIME NOT NULL,
    locatie VARCHAR(200) NOT NULL,
    capaciteit INT NOT NULL DEFAULT 100,
    prijs DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    afbeelding VARCHAR(255),
    aangemaakt_op DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------
-- Tabel: tickets
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gebruiker_id INT NOT NULL,
    show_id INT NOT NULL,
    aantal INT NOT NULL DEFAULT 1,
    totaalprijs DECIMAL(8,2) NOT NULL,
    status ENUM('actief', 'gebruikt', 'geannuleerd') NOT NULL DEFAULT 'actief',
    qr_code VARCHAR(255),
    gekocht_op DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gebruiker_id) REFERENCES gebruikers(id) ON DELETE CASCADE,
    FOREIGN KEY (show_id) REFERENCES shows(id) ON DELETE CASCADE
);

-- -----------------------------------------------
-- Indexen voor betere performance
-- -----------------------------------------------
CREATE INDEX idx_tickets_gebruiker ON tickets(gebruiker_id);
CREATE INDEX idx_tickets_show ON tickets(show_id);
CREATE INDEX idx_shows_datum ON shows(datum);

-- -----------------------------------------------
-- Voorbeelddata: admin gebruiker
-- Wachtwoord: Admin123! (gehasht met password_hash)
-- -----------------------------------------------
INSERT INTO gebruikers (naam, email, wachtwoord, rol) VALUES
('Admin', 'admin@ticketapp.nl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Jan Medewerker', 'jan@ticketapp.nl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'medewerker'),
('Maria Bezoeker', 'maria@ticketapp.nl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bezoeker');

-- -----------------------------------------------
-- Voorbeelddata: shows
-- -----------------------------------------------
INSERT INTO shows (titel, beschrijving, datum, tijd, locatie, capaciteit, prijs) VALUES
('De Grote Musical', 'Een adembenemende musicale voorstelling vol dans en zang.', '2026-06-15', '20:00:00', 'Stadsschouwburg Amsterdam', 300, 45.00),
('Jazz Night Live', 'Een avond vol improvisatie met de beste jazzmusici van Nederland.', '2026-06-20', '21:00:00', 'Paradiso Amsterdam', 150, 35.00),
('Komedieshow: Lachen Geblazen', 'Een hilarische avond met de beste stand-up comedians.', '2026-06-28', '20:30:00', 'Theater Carré Amsterdam', 200, 29.50),
('Klassiek Orkest Gala', 'Een prachtige avond met symfonische muziek van wereldklasse.', '2026-07-05', '19:30:00', 'Concertgebouw Amsterdam', 500, 55.00),
('Dansspektakel 2026', 'Moderne dans in zijn meest pure en krachtige vorm.', '2026-07-12', '20:00:00', 'DeLaMar Theater Amsterdam', 250, 39.50);
