<?php

/**
 * Vult de database met demo-gegevens. Wachtwoorden worden met password_hash
 * versleuteld, zodat er nergens een wachtwoord in platte tekst in de database
 * staat.
 *
 * Gebruik (vanuit de projectmap):  php database/seed.php
 */

require __DIR__ . '/../vendor/autoload.php';

use GarageFlow\Database;

$configPad = __DIR__ . '/../config/config.php';
$config = is_file($configPad)
    ? require $configPad
    : require __DIR__ . '/../config/config.example.php';

$db = Database::verbind($config['db']);

echo "Demo-gegevens plaatsen...\n";

// Bestaande gegevens opschonen zodat het script herhaalbaar is.
$db->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach (['werkorderregel', 'werkorder', 'afspraak', 'voertuig', 'klant', 'medewerker', 'dienst', 'hefbrug'] as $tabel) {
    $db->exec("TRUNCATE TABLE {$tabel}");
}
$db->exec('SET FOREIGN_KEY_CHECKS = 1');

$wachtwoordMedewerker = password_hash('garage123', PASSWORD_DEFAULT);
$wachtwoordKlant = password_hash('klant1234', PASSWORD_DEFAULT);

// Medewerkers
$db->prepare(
    'INSERT INTO medewerker (voornaam, achternaam, email, wachtwoord_hash, rol) VALUES
     (:vn1, :an1, :em1, :pw1, "serviceadviseur"),
     (:vn2, :an2, :em2, :pw2, "monteur"),
     (:vn3, :an3, :em3, :pw3, "monteur")'
)->execute([
    'vn1' => 'Sandra', 'an1' => 'de Wit', 'em1' => 'balie@garagesandvos.nl',
    'vn2' => 'Jeroen', 'an2' => 'Bakker', 'em2' => 'jeroen@garagesandvos.nl',
    'vn3' => 'Tim',    'an3' => 'Visser', 'em3' => 'tim@garagesandvos.nl',
    'pw1' => $wachtwoordMedewerker,
    'pw2' => $wachtwoordMedewerker,
    'pw3' => $wachtwoordMedewerker,
]);

// Diensten / onderhoudstypes
$diensten = [
    ['Kleine beurt', 'Olie verversen, filters en algemene controle', 60, 149.00],
    ['Grote beurt', 'Uitgebreide onderhoudsbeurt volgens schema', 120, 299.00],
    ['APK-keuring', 'Wettelijke periodieke keuring', 45, 59.00],
    ['Remmen vervangen', 'Remblokken en/of remschijven vervangen', 90, 220.00],
    ['Distributieriem vervangen', 'Vervangen van de distributieriem', 240, 650.00],
    ['Diagnose (uitlezen)', 'Foutcodes uitlezen en diagnose stellen', 30, 49.00],
    ['Airco-service', 'Airco bijvullen en controleren', 60, 89.00],
    ['Bandenwissel', 'Seizoenswissel van banden', 30, 39.00],
];
$dienstStmt = $db->prepare(
    'INSERT INTO dienst (naam, omschrijving, standaard_duur_min, prijs)
     VALUES (:naam, :oms, :duur, :prijs)'
);
foreach ($diensten as [$naam, $oms, $duur, $prijs]) {
    $dienstStmt->execute(['naam' => $naam, 'oms' => $oms, 'duur' => $duur, 'prijs' => $prijs]);
}

// Hefbruggen
$db->exec(
    "INSERT INTO hefbrug (aanduiding, type) VALUES
     ('Brug 1', 'tweekolom'),
     ('Brug 2', 'tweekolom'),
     ('Brug 3 (APK)', 'vierkolom')"
);

// Demo-klant met voertuig
$db->prepare(
    'INSERT INTO klant (voornaam, achternaam, email, telefoon, wachtwoord_hash)
     VALUES (:vn, :an, :em, :tel, :pw)'
)->execute([
    'vn' => 'Dean', 'an' => 'Smit', 'em' => 'dean@example.com',
    'tel' => '0612345678', 'pw' => $wachtwoordKlant,
]);
$klantId = (int) $db->lastInsertId();

$db->prepare(
    'INSERT INTO voertuig (klant_id, kenteken, merk, model, bouwjaar)
     VALUES (:klant, :kenteken, :merk, :model, :bouwjaar)'
)->execute([
    'klant' => $klantId, 'kenteken' => '12ABC3', 'merk' => 'BMW',
    'model' => '320i Touring', 'bouwjaar' => 2019,
]);
$voertuigId = (int) $db->lastInsertId();

// Eén demo-afspraak (kleine beurt, morgen om 09:00 op brug 1)
$db->prepare(
    "INSERT INTO afspraak (voertuig_id, dienst_id, hefbrug_id, datum, starttijd, eindtijd)
     VALUES (:voertuig, 1, 1, :datum, '09:00:00', '10:00:00')"
)->execute([
    'voertuig' => $voertuigId,
    'datum'    => date('Y-m-d', strtotime('+1 day')),
]);

echo "Klaar.\n";
echo "Medewerker: balie@garagesandvos.nl / garage123\n";
echo "Klant:      dean@example.com / klant1234\n";
