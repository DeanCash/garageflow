<?php

// Kopieer dit bestand naar config.php en pas de gegevens aan voor je eigen omgeving.
// config.php staat in .gitignore zodat lokale wachtwoorden niet in Git terechtkomen.

return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'naam' => 'garageflow',
        'gebruiker' => 'root',
        'wachtwoord' => '',
        'charset' => 'utf8mb4',
    ],
    // Basis-URL waaronder de public-map draait (in XAMPP meestal dit pad).
    'base_url' => '/garageflow/public',
];
