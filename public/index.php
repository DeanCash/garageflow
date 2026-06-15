<?php

/**
 * Front controller. Alle verzoeken lopen via dit bestand. De gevraagde route
 * komt binnen via ?route=... en wordt door de Router aan een controller-actie
 * gekoppeld.
 */

require __DIR__ . '/../vendor/autoload.php';

use GarageFlow\Controllers\BeheerController;
use GarageFlow\Controllers\HomeController;
use GarageFlow\Controllers\KlantController;
use GarageFlow\Database;
use GarageFlow\Support\Router;

$configPad = __DIR__ . '/../config/config.php';
$config = is_file($configPad)
    ? require $configPad
    : require __DIR__ . '/../config/config.example.php';

define('BASE_URL', $config['base_url']);

$db = Database::verbind($config['db']);

$home = new HomeController();
$klant = new KlantController($db);
$beheer = new BeheerController($db);

$router = new Router();

$router->get('home', fn () => $home->index());

// --- Klantportaal ---
$router->get('klant/registreren', fn () => $klant->toonRegistreren());
$router->post('klant/registreren', fn () => $klant->registreren());
$router->get('klant/inloggen', fn () => $klant->toonInloggen());
$router->post('klant/inloggen', fn () => $klant->inloggen());
$router->get('klant/uitloggen', fn () => $klant->uitloggen());
$router->get('klant/dashboard', fn () => $klant->dashboard());
$router->get('klant/voertuig', fn () => $klant->toonVoertuigForm());
$router->post('klant/voertuig', fn () => $klant->voegVoertuigToe());
$router->get('klant/afspraak', fn () => $klant->toonAfspraakForm());
$router->post('klant/afspraak', fn () => $klant->maakAfspraak());
$router->post('klant/afspraak/annuleren', fn () => $klant->annuleerAfspraak());

// --- Werkplaatsbeheer ---
$router->get('beheer/inloggen', fn () => $beheer->toonInloggen());
$router->post('beheer/inloggen', fn () => $beheer->inloggen());
$router->get('beheer/uitloggen', fn () => $beheer->uitloggen());
$router->get('beheer/planning', fn () => $beheer->planning());
$router->post('beheer/werkorder/maak', fn () => $beheer->maakWerkorder());
$router->get('beheer/werkorder', fn () => $beheer->toonWerkorder());
$router->post('beheer/werkorder/status', fn () => $beheer->wijzigWerkorderStatus());
$router->post('beheer/werkorder/monteur', fn () => $beheer->wijsMonteurToe());
$router->post('beheer/werkorder/regel', fn () => $beheer->voegRegelToe());

$route = (string) ($_GET['route'] ?? 'home');
$methode = (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');

$router->verwerk($route, $methode);
