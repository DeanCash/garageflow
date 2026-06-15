# GarageFlow

Webgebaseerd afspraken- en werkordersysteem voor **Garage Sandvos**, een
onafhankelijke BMW-specialist. Klanten plannen online onderhoud of reparatie in;
de werkplaats beheert de dagplanning en handelt de werkorders af.

Gebouwd met **plain PHP (8.1+) + PDO** en een **MySQL/MariaDB**-database. Het
systeem bestaat uit twee subsystemen: het **klantportaal** en het
**werkplaatsbeheer**.

## Functionaliteit

**Klantportaal**
- Account aanmaken en inloggen
- Voertuig toevoegen (met kentekenvalidatie)
- Online een afspraak maken; het systeem voorkomt dubbele boekingen op dezelfde
  hefbrug en hetzelfde tijdslot
- Eigen afspraken inzien en annuleren

**Werkplaatsbeheer**
- Inloggen als serviceadviseur of monteur
- Dagplanning per hefbrug bekijken
- Werkorder aanmaken bij een afspraak
- Monteur toewijzen, status bijwerken (open → in uitvoering → gereed)
- Onderdelen en arbeid registreren met automatische totaalberekening (incl. 21% btw)

## Installatie (XAMPP)

1. Start **Apache** en **MySQL** in het XAMPP-configuratiescherm.
2. Maak de database aan en importeer het schema. Via phpMyAdmin importeer je
   `database/schema.sql`, of via de command line:
   ```
   mysql -u root < database/schema.sql
   ```
3. Installeer de dependencies (PHPUnit) met Composer:
   ```
   composer install
   ```
4. Kopieer `config/config.example.php` naar `config/config.php` en pas zo nodig de
   databasegegevens aan (standaard werkt root zonder wachtwoord op XAMPP).
5. Vul de database met demo-gegevens:
   ```
   php database/seed.php
   ```
6. Plaats de map in `htdocs` (of stel een virtual host in) en open in de browser:
   ```
   http://localhost/garageflow/public/index.php
   ```

## Demo-accounts (na het seeden)

| Rol | E-mailadres | Wachtwoord |
|-----|-------------|------------|
| Serviceadviseur | balie@garagesandvos.nl | garage123 |
| Monteur | jeroen@garagesandvos.nl | garage123 |
| Klant | dean@example.com | klant1234 |

## Tests

```
composer install
vendor/bin/phpunit
```

- **Unit-tests** (`tests/Unit`) controleren de domeinlogica (kentekenvalidatie,
  tijdslotberekening en -overlap, bedragberekening) zonder database.
- **Integratietest** (`tests/Integratie`) test het opslaan van een afspraak en de
  overlapdetectie tegen een MySQL-testdatabase `garageflow_test`. Maak die database
  eenmalig aan; zonder testdatabase wordt deze test netjes overgeslagen.

## Beveiliging

- Wachtwoorden worden gehasht met `password_hash` (bcrypt).
- Alle databasetoegang verloopt via **PDO prepared statements** (tegen SQL-injectie).
- Formulieren die gegevens wijzigen zijn beveiligd met een **CSRF-token**.
- Uitvoer wordt ge-escaped met `htmlspecialchars` (tegen XSS).
- Een kenteken is onder de AVG een persoonsgegeven; het wordt gevalideerd en
  genormaliseerd voordat het wordt opgeslagen.

## Projectstructuur

```
config/      configuratie (databasegegevens)
database/    schema.sql en seed.php
public/      webroot met front controller (index.php) en assets
src/
  Domein/        pure logica (Kenteken, Tijdslot, Bedrag) - unit-getest
  Repositories/  PDO-datatoegang per entiteit
  Controllers/   afhandeling van de routes
  Support/       Auth, Csrf, Router, View, Validatie
  helpers.php    kleine globale hulpfuncties
views/       PHP-templates (layout, klant/, beheer/)
tests/       Unit- en integratietests
```
