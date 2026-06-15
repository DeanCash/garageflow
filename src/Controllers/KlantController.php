<?php

namespace GarageFlow\Controllers;

use GarageFlow\Domein\Kenteken;
use GarageFlow\Domein\Tijdslot;
use GarageFlow\Repositories\AfspraakRepository;
use GarageFlow\Repositories\DienstRepository;
use GarageFlow\Repositories\HefbrugRepository;
use GarageFlow\Repositories\KlantRepository;
use GarageFlow\Repositories\VoertuigRepository;
use GarageFlow\Support\Auth;
use GarageFlow\Support\Csrf;
use GarageFlow\Support\Validatie;
use GarageFlow\Support\View;
use PDO;

/**
 * Het klantportaal: registreren/inloggen, voertuig toevoegen, online een
 * afspraak maken en de eigen afspraken inzien.
 */
class KlantController
{
    private KlantRepository $klanten;
    private VoertuigRepository $voertuigen;
    private DienstRepository $diensten;
    private HefbrugRepository $hefbruggen;
    private AfspraakRepository $afspraken;

    public function __construct(PDO $db)
    {
        $this->klanten = new KlantRepository($db);
        $this->voertuigen = new VoertuigRepository($db);
        $this->diensten = new DienstRepository($db);
        $this->hefbruggen = new HefbrugRepository($db);
        $this->afspraken = new AfspraakRepository($db);
    }

    public function toonRegistreren(): void
    {
        View::render('klant/registreren', ['oud' => []], 'Registreren');
    }

    public function registreren(): void
    {
        $this->controleerCsrf();

        $validatie = new Validatie($_POST);
        $validatie->verplicht('voornaam', 'Voornaam')
            ->verplicht('achternaam', 'Achternaam')
            ->verplicht('email', 'E-mailadres')
            ->email('email', 'E-mailadres')
            ->minimaleLengte('wachtwoord', 'Wachtwoord', 8);

        $email = trim((string) ($_POST['email'] ?? ''));
        if ($email !== '' && $this->klanten->vindOpEmail($email) !== null) {
            $validatie->voegFoutToe('email', 'Er bestaat al een account met dit e-mailadres.');
        }

        if (!$validatie->isGeldig()) {
            View::render('klant/registreren', [
                'fouten' => $validatie->fouten(),
                'oud'    => $_POST,
            ], 'Registreren');

            return;
        }

        $klantId = $this->klanten->voegToe(
            trim((string) $_POST['voornaam']),
            trim((string) $_POST['achternaam']),
            $email,
            trim((string) ($_POST['telefoon'] ?? '')) ?: null,
            password_hash((string) $_POST['wachtwoord'], PASSWORD_DEFAULT)
        );

        Auth::logKlantIn($klantId, trim((string) $_POST['voornaam']));
        zetMelding('succes', 'Je account is aangemaakt. Welkom!');
        redirect('klant/dashboard');
    }

    public function toonInloggen(): void
    {
        View::render('klant/inloggen', ['oud' => []], 'Inloggen');
    }

    public function inloggen(): void
    {
        $this->controleerCsrf();

        $email = trim((string) ($_POST['email'] ?? ''));
        $wachtwoord = (string) ($_POST['wachtwoord'] ?? '');
        $klant = $this->klanten->vindOpEmail($email);

        if ($klant === null || !password_verify($wachtwoord, $klant['wachtwoord_hash'])) {
            View::render('klant/inloggen', [
                'fouten' => ['email' => 'Onjuiste combinatie van e-mailadres en wachtwoord.'],
                'oud'    => $_POST,
            ], 'Inloggen');

            return;
        }

        Auth::logKlantIn((int) $klant['id'], $klant['voornaam']);
        redirect('klant/dashboard');
    }

    public function uitloggen(): void
    {
        Auth::uitloggen();
        redirect('home');
    }

    public function dashboard(): void
    {
        $klantId = $this->vereisKlant();

        View::render('klant/dashboard', [
            'voertuigen' => $this->voertuigen->vindVoorKlant($klantId),
            'afspraken'  => $this->afspraken->vindVoorKlant($klantId),
        ], 'Mijn overzicht');
    }

    public function toonVoertuigForm(): void
    {
        $this->vereisKlant();
        View::render('klant/voertuig', ['oud' => []], 'Voertuig toevoegen');
    }

    public function voegVoertuigToe(): void
    {
        $klantId = $this->vereisKlant();
        $this->controleerCsrf();

        $validatie = new Validatie($_POST);
        $validatie->verplicht('kenteken', 'Kenteken')
            ->verplicht('model', 'Model');

        $kenteken = Kenteken::normaliseer((string) ($_POST['kenteken'] ?? ''));
        if ($kenteken !== '' && !Kenteken::isGeldig($kenteken)) {
            $validatie->voegFoutToe('kenteken', 'Dit is geen geldig Nederlands kenteken.');
        } elseif ($kenteken !== '' && $this->voertuigen->bestaatKenteken($kenteken)) {
            $validatie->voegFoutToe('kenteken', 'Dit kenteken is al geregistreerd.');
        }

        if (!$validatie->isGeldig()) {
            View::render('klant/voertuig', [
                'fouten' => $validatie->fouten(),
                'oud'    => $_POST,
            ], 'Voertuig toevoegen');

            return;
        }

        $bouwjaar = (int) ($_POST['bouwjaar'] ?? 0);
        $this->voertuigen->voegToe(
            $klantId,
            $kenteken,
            trim((string) ($_POST['merk'] ?? 'BMW')) ?: 'BMW',
            trim((string) $_POST['model']),
            $bouwjaar > 0 ? $bouwjaar : null
        );

        zetMelding('succes', 'Voertuig toegevoegd.');
        redirect('klant/dashboard');
    }

    public function toonAfspraakForm(): void
    {
        $klantId = $this->vereisKlant();

        View::render('klant/afspraak', [
            'voertuigen' => $this->voertuigen->vindVoorKlant($klantId),
            'diensten'   => $this->diensten->alle(),
            'hefbruggen' => $this->hefbruggen->alle(),
            'oud'        => [],
        ], 'Afspraak maken');
    }

    public function maakAfspraak(): void
    {
        $klantId = $this->vereisKlant();
        $this->controleerCsrf();

        $validatie = new Validatie($_POST);
        $validatie->verplicht('voertuig_id', 'Voertuig')
            ->verplicht('dienst_id', 'Dienst')
            ->verplicht('hefbrug_id', 'Hefbrug')
            ->verplicht('datum', 'Datum')
            ->verplicht('starttijd', 'Starttijd');

        $voertuig = $this->voertuigen->vindOpId((int) ($_POST['voertuig_id'] ?? 0));
        $dienst = $this->diensten->vindOpId((int) ($_POST['dienst_id'] ?? 0));

        // Controleer dat het gekozen voertuig echt van de ingelogde klant is.
        if ($voertuig === null || (int) $voertuig['klant_id'] !== $klantId) {
            $validatie->voegFoutToe('voertuig_id', 'Kies een geldig voertuig.');
        }

        $datum = (string) ($_POST['datum'] ?? '');
        if ($datum !== '' && $datum < date('Y-m-d')) {
            $validatie->voegFoutToe('datum', 'De datum kan niet in het verleden liggen.');
        }

        if ($dienst !== null && $validatie->isGeldig()) {
            $tijdslot = new Tijdslot((string) $_POST['starttijd'], (int) $dienst['standaard_duur_min']);
            $eindtijd = $tijdslot->eindtijd();
            $hefbrugId = (int) $_POST['hefbrug_id'];

            if ($this->afspraken->heeftOverlap($hefbrugId, $datum, (string) $_POST['starttijd'], $eindtijd)) {
                $validatie->voegFoutToe('starttijd', 'Deze hefbrug is op dat tijdstip al bezet. Kies een ander tijdslot.');
            }
        }

        if (!$validatie->isGeldig()) {
            View::render('klant/afspraak', [
                'fouten'     => $validatie->fouten(),
                'voertuigen' => $this->voertuigen->vindVoorKlant($klantId),
                'diensten'   => $this->diensten->alle(),
                'hefbruggen' => $this->hefbruggen->alle(),
                'oud'        => $_POST,
            ], 'Afspraak maken');

            return;
        }

        $this->afspraken->voegToe(
            (int) $_POST['voertuig_id'],
            (int) $_POST['dienst_id'],
            (int) $_POST['hefbrug_id'],
            $datum,
            (string) $_POST['starttijd'],
            $eindtijd,
            trim((string) ($_POST['opmerking'] ?? '')) ?: null
        );

        zetMelding('succes', 'Je afspraak is ingepland.');
        redirect('klant/dashboard');
    }

    public function annuleerAfspraak(): void
    {
        $this->vereisKlant();
        $this->controleerCsrf();

        $this->afspraken->wijzigStatus((int) ($_POST['afspraak_id'] ?? 0), 'geannuleerd');
        zetMelding('succes', 'Afspraak geannuleerd.');
        redirect('klant/dashboard');
    }

    private function vereisKlant(): int
    {
        $klantId = Auth::klantId();
        if ($klantId === null) {
            zetMelding('fout', 'Log eerst in om verder te gaan.');
            redirect('klant/inloggen');
        }

        return $klantId;
    }

    private function controleerCsrf(): void
    {
        if (!Csrf::controleer($_POST['csrf_token'] ?? null)) {
            http_response_code(400);
            View::render('fout', ['melding' => 'Ongeldig of verlopen formulier. Probeer het opnieuw.'], 'Foutmelding');
            exit;
        }
    }
}
