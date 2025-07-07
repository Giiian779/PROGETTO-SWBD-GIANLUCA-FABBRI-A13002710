-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Lug 07, 2025 alle 11:10
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `centro_cinofilo`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `cani`
--

CREATE TABLE `cani` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `eta` int(11) NOT NULL,
  `razza` varchar(50) DEFAULT NULL,
  `proprietario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `cani`
--

INSERT INTO `cani` (`id`, `nome`, `eta`, `razza`, `proprietario_id`) VALUES
(1, 'Yuki', 10, 'Maltese', 4),
(2, 'Pinco', 3, 'Pastore tedesco', 4),
(3, 'Frank', 7, 'Segugio', 4),
(6, 'Erikus', 5, 'Meticcio', 12);

-- --------------------------------------------------------

--
-- Struttura della tabella `corsi`
--

CREATE TABLE `corsi` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descrizione` text DEFAULT NULL,
  `fascia_eta` varchar(255) DEFAULT NULL,
  `istruttore_id` int(11) DEFAULT NULL,
  `fascia_oraria` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `corsi`
--

INSERT INTO `corsi` (`id`, `nome`, `descrizione`, `fascia_eta`, `istruttore_id`, `fascia_oraria`) VALUES
(3, 'scuola mattutina per cani giovani', 'lascia qui il tuo cane quando vai a lavoro', 'cucciolo', 7, '8:00-18:00'),
(5, 'Corso per cani anziani', 'Corso per cani giovani', '8.-12', 10, '15:00-16:00'),
(6, 'Corso per cuccioli', 'Corso per cuccioli', '5 mesi- 3anni', 11, '19:00-20:00');

-- --------------------------------------------------------

--
-- Struttura della tabella `giorni_corso`
--

CREATE TABLE `giorni_corso` (
  `id` int(11) NOT NULL,
  `corso_id` int(11) NOT NULL,
  `giorno` enum('Lunedì','Martedì','Mercoledì','Giovedì','Venerdì','Sabato','Domenica') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `giorni_corso`
--

INSERT INTO `giorni_corso` (`id`, `corso_id`, `giorno`) VALUES
(1, 3, 'Lunedì'),
(2, 3, 'Martedì'),
(3, 3, 'Mercoledì'),
(4, 3, 'Giovedì'),
(5, 3, 'Venerdì'),
(8, 5, 'Lunedì'),
(9, 5, 'Martedì'),
(10, 6, 'Lunedì'),
(11, 6, 'Giovedì'),
(12, 6, 'Venerdì');

-- --------------------------------------------------------

--
-- Struttura della tabella `iscrizioni`
--

CREATE TABLE `iscrizioni` (
  `id` int(11) NOT NULL,
  `cane_id` int(11) NOT NULL,
  `corso_id` int(11) NOT NULL,
  `data_iscrizione` date DEFAULT curdate(),
  `stato` enum('in_attesa','accettata','rifiutata') DEFAULT 'in_attesa',
  `testo_notifica` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `iscrizioni`
--

INSERT INTO `iscrizioni` (`id`, `cane_id`, `corso_id`, `data_iscrizione`, `stato`, `testo_notifica`) VALUES
(7, 2, 3, '2025-07-01', 'accettata', NULL),
(10, 3, 3, '2025-07-06', 'accettata', NULL),
(11, 1, 3, '2025-07-06', 'accettata', NULL),
(12, 2, 5, '2025-07-06', 'accettata', NULL),
(13, 3, 6, '2025-07-06', 'accettata', NULL),
(14, 6, 3, '2025-07-06', 'accettata', NULL),
(15, 6, 5, '2025-07-06', 'accettata', NULL),
(16, 6, 6, '2025-07-06', 'rifiutata', 'Troppo vecchio');

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti`
--

CREATE TABLE `utenti` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `ruolo` enum('admin','istruttore','utente') NOT NULL DEFAULT 'utente',
  `data_registrazione` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `utenti`
--

INSERT INTO `utenti` (`id`, `nome`, `cognome`, `email`, `password`, `ruolo`, `data_registrazione`) VALUES
(1, 'Admin', 'Centro', 'admin@centro.it', '$2y$10$84ZWVBS9j1wFOVGegV5wU.XVwFzFijp9Jrrsv420hjrhNCGYKfy2q', 'admin', '2025-06-23 09:59:52'),
(4, 'Gianluca', 'Fabbri', 'gianluca.fabbri@utente.it', '$2y$10$pgtQRIwXOPxnWsrw3HkJcOJnL4EgOGH.jBdcZKZc.NqsZw0HmE8nS', 'utente', '2025-06-29 08:34:47'),
(7, 'giuseppe', 'miraglia', 'giuseppe.miraglia@istruttore.it', '$2y$10$aKrAhIKVwaChGbHbVY6tG.BjFFR.bzZgjpz2HkQcPbdqj1joqQJI2', 'istruttore', '2025-07-01 08:36:23'),
(10, 'Luigi', 'Brigiola', 'luigi.brigiola@istruttore.it', '$2y$10$1wv7lgljPTH/LirjDeWpaOlRIutHS/9cCzLEWrWp/Y0vz7H7HViRa', 'istruttore', '2025-07-06 10:06:32'),
(11, 'Peppe', 'Puzo', 'peppe.puzo@istruttore.it', '$2y$10$.tF7s8Lptsn7ZyLO0SE.KeUrWXEkY44ng1w7DWwH/kN4G2xk.GsKm', 'istruttore', '2025-07-06 10:07:22'),
(12, 'Giovanna', 'Petruolo', 'giopet@utente.it', '$2y$10$/YZoWX31loIRhKG5zMhu0e7mRAh1DaMZ7KWQ/fZ6LKVMP0GNGLHLq', 'utente', '2025-07-06 10:10:23');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `cani`
--
ALTER TABLE `cani`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proprietario_id` (`proprietario_id`);

--
-- Indici per le tabelle `corsi`
--
ALTER TABLE `corsi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `istruttore_id` (`istruttore_id`);

--
-- Indici per le tabelle `giorni_corso`
--
ALTER TABLE `giorni_corso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `corso_id` (`corso_id`);

--
-- Indici per le tabelle `iscrizioni`
--
ALTER TABLE `iscrizioni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cane_id` (`cane_id`),
  ADD KEY `corso_id` (`corso_id`);

--
-- Indici per le tabelle `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `cani`
--
ALTER TABLE `cani`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `corsi`
--
ALTER TABLE `corsi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `giorni_corso`
--
ALTER TABLE `giorni_corso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT per la tabella `iscrizioni`
--
ALTER TABLE `iscrizioni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `cani`
--
ALTER TABLE `cani`
  ADD CONSTRAINT `cani_ibfk_1` FOREIGN KEY (`proprietario_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `corsi`
--
ALTER TABLE `corsi`
  ADD CONSTRAINT `corsi_ibfk_1` FOREIGN KEY (`istruttore_id`) REFERENCES `utenti` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `giorni_corso`
--
ALTER TABLE `giorni_corso`
  ADD CONSTRAINT `giorni_corso_ibfk_1` FOREIGN KEY (`corso_id`) REFERENCES `corsi` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `iscrizioni`
--
ALTER TABLE `iscrizioni`
  ADD CONSTRAINT `iscrizioni_ibfk_1` FOREIGN KEY (`cane_id`) REFERENCES `cani` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `iscrizioni_ibfk_2` FOREIGN KEY (`corso_id`) REFERENCES `corsi` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
