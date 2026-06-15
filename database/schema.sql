-- GarageFlow databaseschema
-- Doelplatform: MySQL / MariaDB (XAMPP)
-- Importeren via phpMyAdmin of: mysql -u root garageflow < schema.sql
--
-- Het schema is genormaliseerd tot 3NF: elke entiteit heeft een eigen tabel,
-- relaties lopen via foreign keys en er zijn geen herhalende of afgeleide kolommen.

CREATE DATABASE IF NOT EXISTS garageflow
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE garageflow;

-- Bij opnieuw inrichten eerst de tabellen in de juiste volgorde verwijderen.
DROP TABLE IF EXISTS werkorderregel;
DROP TABLE IF EXISTS werkorder;
DROP TABLE IF EXISTS afspraak;
DROP TABLE IF EXISTS voertuig;
DROP TABLE IF EXISTS klant;
DROP TABLE IF EXISTS medewerker;
DROP TABLE IF EXISTS dienst;
DROP TABLE IF EXISTS hefbrug;

-- Klanten die online een afspraak maken.
CREATE TABLE klant (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    voornaam        VARCHAR(60)  NOT NULL,
    achternaam      VARCHAR(80)  NOT NULL,
    email           VARCHAR(150) NOT NULL,
    telefoon        VARCHAR(20)  NULL,
    wachtwoord_hash VARCHAR(255) NOT NULL,
    aangemaakt_op   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_klant_email (email)
) ENGINE = InnoDB;

-- Medewerkers van de garage: serviceadviseur (balie/planning) of monteur.
CREATE TABLE medewerker (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    voornaam        VARCHAR(60)  NOT NULL,
    achternaam      VARCHAR(80)  NOT NULL,
    email           VARCHAR(150) NOT NULL,
    wachtwoord_hash VARCHAR(255) NOT NULL,
    rol             ENUM('serviceadviseur', 'monteur') NOT NULL,
    actief          TINYINT(1)   NOT NULL DEFAULT 1,
    aangemaakt_op   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_medewerker_email (email)
) ENGINE = InnoDB;

-- Voertuigen horen bij een klant. Kenteken is uniek.
CREATE TABLE voertuig (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    klant_id  INT UNSIGNED NOT NULL,
    kenteken  VARCHAR(10)  NOT NULL,
    merk      VARCHAR(40)  NOT NULL DEFAULT 'BMW',
    model     VARCHAR(60)  NOT NULL,
    bouwjaar  SMALLINT     NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_voertuig_kenteken (kenteken),
    KEY idx_voertuig_klant (klant_id),
    CONSTRAINT fk_voertuig_klant FOREIGN KEY (klant_id)
        REFERENCES klant (id) ON DELETE CASCADE
) ENGINE = InnoDB;

-- Diensten / onderhoudstypes die geboekt kunnen worden.
CREATE TABLE dienst (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    naam              VARCHAR(80)  NOT NULL,
    omschrijving      VARCHAR(255) NULL,
    standaard_duur_min SMALLINT    NOT NULL,
    prijs             DECIMAL(8,2) NOT NULL,
    PRIMARY KEY (id)
) ENGINE = InnoDB;

-- Werkplekken / hefbruggen waarop gepland wordt.
CREATE TABLE hefbrug (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    aanduiding VARCHAR(40) NOT NULL,
    type      ENUM('tweekolom', 'vierkolom') NOT NULL,
    PRIMARY KEY (id)
) ENGINE = InnoDB;

-- Een afspraak koppelt een voertuig + dienst aan een hefbrug op een tijdstip.
-- De unieke sleutel (hefbrug, datum, starttijd) voorkomt dubbele boekingen.
CREATE TABLE afspraak (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    voertuig_id   INT UNSIGNED NOT NULL,
    dienst_id     INT UNSIGNED NOT NULL,
    hefbrug_id    INT UNSIGNED NOT NULL,
    datum         DATE         NOT NULL,
    starttijd     TIME         NOT NULL,
    eindtijd      TIME         NOT NULL,
    status        ENUM('ingepland', 'in_uitvoering', 'gereed', 'geannuleerd')
                  NOT NULL DEFAULT 'ingepland',
    opmerking     VARCHAR(255) NULL,
    aangemaakt_op DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_afspraak_slot (hefbrug_id, datum, starttijd),
    KEY idx_afspraak_voertuig (voertuig_id),
    KEY idx_afspraak_dienst (dienst_id),
    KEY idx_afspraak_datum (datum),
    CONSTRAINT fk_afspraak_voertuig FOREIGN KEY (voertuig_id)
        REFERENCES voertuig (id) ON DELETE CASCADE,
    CONSTRAINT fk_afspraak_dienst FOREIGN KEY (dienst_id)
        REFERENCES dienst (id),
    CONSTRAINT fk_afspraak_hefbrug FOREIGN KEY (hefbrug_id)
        REFERENCES hefbrug (id)
) ENGINE = InnoDB;

-- Bij elke afspraak hoort één werkorder die de monteur afhandelt.
CREATE TABLE werkorder (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    afspraak_id   INT UNSIGNED NOT NULL,
    monteur_id    INT UNSIGNED NULL,
    status        ENUM('open', 'in_uitvoering', 'gereed') NOT NULL DEFAULT 'open',
    gestart_op    DATETIME     NULL,
    gereed_op     DATETIME     NULL,
    aangemaakt_op DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_werkorder_afspraak (afspraak_id),
    KEY idx_werkorder_monteur (monteur_id),
    CONSTRAINT fk_werkorder_afspraak FOREIGN KEY (afspraak_id)
        REFERENCES afspraak (id) ON DELETE CASCADE,
    CONSTRAINT fk_werkorder_monteur FOREIGN KEY (monteur_id)
        REFERENCES medewerker (id) ON DELETE SET NULL
) ENGINE = InnoDB;

-- Regels op een werkorder: gebruikte onderdelen en bestede arbeid.
CREATE TABLE werkorderregel (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    werkorder_id   INT UNSIGNED NOT NULL,
    soort          ENUM('onderdeel', 'arbeid') NOT NULL,
    omschrijving   VARCHAR(150) NOT NULL,
    aantal         DECIMAL(6,2) NOT NULL DEFAULT 1,
    prijs_per_stuk DECIMAL(8,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_regel_werkorder (werkorder_id),
    CONSTRAINT fk_regel_werkorder FOREIGN KEY (werkorder_id)
        REFERENCES werkorder (id) ON DELETE CASCADE
) ENGINE = InnoDB;
