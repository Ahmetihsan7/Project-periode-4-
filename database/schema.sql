-- Database aanmaak (indien niet aanwezig)
CREATE DATABASE IF NOT EXISTS `aurora_theater` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `aurora_theater`;

-- Verwijder tabellen in de juiste volgorde vanwege foreign keys
DROP TABLE IF EXISTS `instellingen`;
DROP TABLE IF EXISTS `meldingen`;
DROP TABLE IF EXISTS `tickets`;
DROP TABLE IF EXISTS `medewerkers`;
DROP TABLE IF EXISTS `voorstellingen`;
DROP TABLE IF EXISTS `gebruikers`;

-- Tabel: gebruikers (accounts voor klanten, medewerkers en admins)
CREATE TABLE `gebruikers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `naam` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `wachtwoord` VARCHAR(255) NOT NULL,
  `rol` ENUM('klant', 'medewerker', 'admin') DEFAULT 'klant',
  `gemaakt_op` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel: medewerkers (aanvullende info voor medewerkers en admins)
CREATE TABLE `medewerkers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `gebruiker_id` INT NOT NULL,
  `functie` VARCHAR(100) NOT NULL,
  `salaris` DECIMAL(10,2) DEFAULT NULL,
  `aangenomen_op` DATE NOT NULL,
  `voornaam` VARCHAR(50) DEFAULT NULL,
  `achternaam` VARCHAR(50) DEFAULT NULL,
  `telefoon` VARCHAR(20) DEFAULT NULL,
  FOREIGN KEY (`gebruiker_id`) REFERENCES `gebruikers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel: voorstellingen (de shows)
CREATE TABLE `voorstellingen` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titel` VARCHAR(255) NOT NULL,
  `beschrijving` TEXT NOT NULL,
  `afbeelding` VARCHAR(255) NOT NULL, -- Path to show image
  `datum_tijd` DATETIME NOT NULL,
  `zaal` VARCHAR(50) NOT NULL,
  `prijs` DECIMAL(5,2) NOT NULL,
  `beschikbare_plaatsen` INT NOT NULL,
  `max_plaatsen` INT NOT NULL,
  `populair` TINYINT(1) DEFAULT 0,
  `gemaakt_op` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel: tickets (boekingsoverzicht)
CREATE TABLE `tickets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `voorstelling_id` INT NOT NULL,
  `gebruiker_id` INT NOT NULL,
  `aantal_plaatsen` INT NOT NULL,
  `totale_prijs` DECIMAL(7,2) NOT NULL,
  `stoel_nummers` VARCHAR(255) DEFAULT NULL, -- CSV of stoelen bijv. "A1, A2"
  `tickettype` VARCHAR(50) DEFAULT 'Standaard',
  `status` ENUM('actief', 'geannuleerd') DEFAULT 'actief',
  `geboekt_op` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`voorstelling_id`) REFERENCES `voorstellingen`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`gebruiker_id`) REFERENCES `gebruikers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel: meldingen (contact aanvragen)
CREATE TABLE `meldingen` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `naam` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `onderwerp` VARCHAR(255) NOT NULL,
  `bericht` TEXT NOT NULL,
  `status` ENUM('nieuw', 'gelezen') DEFAULT 'nieuw',
  `prioriteit` VARCHAR(50) DEFAULT 'gemiddeld',
  `datum` DATE DEFAULT NULL,
  `gemaakt_op` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel: instellingen (key-value settings voor flexibiliteit)
CREATE TABLE `instellingen` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sleutel` VARCHAR(50) UNIQUE NOT NULL,
  `waarde` TEXT NOT NULL,
  `beschrijving` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==========================================
-- TEST DATA SEEDING
-- ==========================================

-- Seed Gebruikers (wachtwoorden zijn: Admin123!, Medewerker123!, Klant123!)
INSERT INTO `gebruikers` (`id`, `naam`, `email`, `wachtwoord`, `rol`) VALUES
(1, 'Sara de Beus (Admin)', 'admin@aurora.nl', '$2y$10$2PQuAOC.y4FFH6Phf7HYFunLN6WIMNy2GpBD4r8g4NiAFkg1XcnSe', 'admin'),
(2, 'Jan van der Meer (Medewerker)', 'medewerker@aurora.nl', '$2y$10$P1TJK1pbH7PnN9wr.xe6AuEvdIFegvncbcKUKTpokxeY3CO6z5jnq', 'medewerker'),
(3, 'Mark de Vries (Klant)', 'klant@aurora.nl', '$2y$10$cYHF.hPEX9YJiLVGqOVr4ef1F0e8.3hg4F/finGXnsWmDrTdLsHcC', 'klant');

-- Seed Medewerkers details
INSERT INTO `medewerkers` (`gebruiker_id`, `functie`, `salaris`, `aangenomen_op`) VALUES
(1, 'Hoofd IT & Theater Manager', 4250.00, '2023-01-15'),
(2, 'Kassa & Publieksbegeleiding', 2450.00, '2024-03-10');

-- Seed Voorstellingen
INSERT INTO `voorstellingen` (`id`, `titel`, `beschrijving`, `afbeelding`, `datum_tijd`, `zaal`, `prijs`, `beschikbare_plaatsen`, `max_plaatsen`, `populair`) VALUES
(1, 'Romeo en Julia', 'Het klassieke liefdesverhaal van Shakespeare, opnieuw tot leven gebracht door het Nationaal Toneel in een modern jasje met adembenemend decor en meeslepende live muziek.', 'assets/images/romeo.png', '2026-09-15 20:00:00', 'Grote Zaal A', 24.50, 144, 150, 1),
(2, 'The Phantom of the Opera', 'De wereldberoemde musical van Andrew Lloyd Webber. Laat u betoveren door de legendarische muziek, de schitterende kostuums en het tragische verhaal van het fantoom.', 'assets/images/phantom.png', '2026-10-05 19:30:00', 'Koninklijke Zaal', 39.99, 150, 150, 1),
(3, 'Stand-up Comedy Night', 'Een avond vol lachen met de beste cabaretiers van Nederlandse bodem. Drie acts, één MC, en gegarandeerd buikpijn van het lachen in onze gezellige intieme zaal.', 'assets/images/comedy.png', '2026-08-20 21:00:00', 'Intieme Zaal B', 18.00, 48, 50, 0),
(4, 'Symfonie van het Noorden', 'Het Noord Nederlands Orkest speelt meesterwerken van Beethoven en Mozart onder leiding van dirigent Valery Gergiev. Een magische avond voor klassieke muziekliefhebbers.', 'assets/images/symphony.png', '2026-11-12 19:00:00', 'Grote Zaal A', 29.50, 150, 150, 0);

-- Seed Tickets
INSERT INTO `tickets` (`voorstelling_id`, `gebruiker_id`, `aantal_plaatsen`, `totale_prijs`, `stoel_nummers`, `status`) VALUES
(1, 3, 2, 49.00, 'A5, A6', 'actief'),
(3, 3, 2, 36.00, 'B1, B2', 'actief');

-- Seed Meldingen (Contact)
INSERT INTO `meldingen` (`naam`, `email`, `onderwerp`, `bericht`, `status`) VALUES
( 'Lisa Bakker', 'lisa.bakker@example.com', 'Groepsreservering vraag', 'Beste Aurora, wij willen graag met een groep van 20 personen naar Romeo en Julia komen. Is er een groepskorting beschikbaar?', 'nieuw'),
( 'Thomas Smit', 'thomas@example.com', 'Gevonden voorwerp', 'Ik ben gisteravond mijn sjaal verloren in Grote Zaal A op rij 4. Is deze toevallig gevonden?', 'gelezen');

-- Seed Instellingen
INSERT INTO `instellingen` (`sleutel`, `waarde`, `beschrijving`) VALUES
('theater_naam', 'Aurora Theater', 'De naam van het theater getoond op de website'),
('contact_email', 'info@auroratheater.nl', 'Algemeen contact e-mailadres'),
('contact_telefoon', '020-1234567', 'Algemeen telefoonnummer'),
('adres_straat', 'Theaterplein 1', 'Adres straat en huisnummer'),
('adres_stad', 'Amsterdam', 'Stad van het theater'),
('ticket_toeslag', '1.50', 'Servicekosten per ticket boeking');
