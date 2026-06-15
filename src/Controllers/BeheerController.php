<?php

namespace GarageFlow\Controllers;

use GarageFlow\Domein\Bedrag;
use GarageFlow\Repositories\AfspraakRepository;
use GarageFlow\Repositories\MedewerkerRepository;
use GarageFlow\Repositories\WerkorderRepository;
use GarageFlow\Support\Auth;
use GarageFlow\Support\Csrf;
use GarageFlow\Support\View;
use PDO;

/**
 * Het werkplaatsbeheer voor serviceadviseur en monteur: dagplanning, werkorders
 * aanmaken en afhandelen, onderdelen en arbeid registreren.
 */
class BeheerController
{
    private MedewerkerRepository $medewerkers;
    private AfspraakRepository $afspraken;
    private WerkorderRepository $werkorders;

    public function __construct(PDO $db)
    {
        $this->medewerkers = new MedewerkerRepository($db);
        $this->afspraken = new AfspraakRepository($db);
        $this->werkorders = new WerkorderRepository($db);
    }

    public function toonInloggen(): void
    {
        View::render('beheer/inloggen', ['oud' => []], 'Beheer - inloggen');
    }

    public function inloggen(): void
    {
        $this->controleerCsrf();

        $email = trim((string) ($_POST['email'] ?? ''));
        $wachtwoord = (string) ($_POST['wachtwoord'] ?? '');
        $medewerker = $this->medewerkers->vindOpEmail($email);

        if ($medewerker === null || !password_verify($wachtwoord, $medewerker['wachtwoord_hash'])) {
            View::render('beheer/inloggen', [
                'fouten' => ['email' => 'Onjuiste inloggegevens.'],
                'oud'    => $_POST,
            ], 'Beheer - inloggen');

            return;
        }

        Auth::logMedewerkerIn(
            (int) $medewerker['id'],
            $medewerker['voornaam'],
            $medewerker['rol']
        );
        redirect('beheer/planning');
    }

    public function uitloggen(): void
    {
        Auth::uitloggen();
        redirect('home');
    }

    public function planning(): void
    {
        $this->vereisMedewerker();

        $datum = (string) ($_GET['datum'] ?? date('Y-m-d'));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $datum) !== 1) {
            $datum = date('Y-m-d');
        }

        View::render('beheer/planning', [
            'datum'     => $datum,
            'afspraken' => $this->afspraken->vindOpDatum($datum),
        ], 'Dagplanning');
    }

    public function maakWerkorder(): void
    {
        $this->vereisMedewerker();
        $this->controleerCsrf();

        $afspraakId = (int) ($_POST['afspraak_id'] ?? 0);
        $this->werkorders->maakVoorAfspraak($afspraakId);
        $this->afspraken->wijzigStatus($afspraakId, 'in_uitvoering');

        zetMelding('succes', 'Werkorder aangemaakt.');
        redirect('beheer/planning&datum=' . urlencode((string) ($_POST['datum'] ?? date('Y-m-d'))));
    }

    public function toonWerkorder(): void
    {
        $this->vereisMedewerker();

        $werkorderId = (int) ($_GET['id'] ?? 0);
        $werkorder = $this->werkorders->vindOpId($werkorderId);

        if ($werkorder === null) {
            http_response_code(404);
            View::render('fout', ['melding' => 'Werkorder niet gevonden.'], 'Niet gevonden');

            return;
        }

        $afspraak = $this->afspraken->vindOpId((int) $werkorder['afspraak_id']);
        $regels = $this->werkorders->regels($werkorderId);

        View::render('beheer/werkorder', [
            'werkorder' => $werkorder,
            'afspraak'  => $afspraak,
            'regels'    => $regels,
            'monteurs'  => $this->medewerkers->monteurs(),
            'subtotaal' => Bedrag::subtotaal($regels),
            'btw'       => Bedrag::btw(Bedrag::subtotaal($regels)),
            'totaal'    => Bedrag::totaalInclusiefBtw($regels),
        ], 'Werkorder #' . $werkorderId);
    }

    public function wijzigWerkorderStatus(): void
    {
        $this->vereisMedewerker();
        $this->controleerCsrf();

        $werkorderId = (int) ($_POST['werkorder_id'] ?? 0);
        $status = (string) ($_POST['status'] ?? 'open');
        $this->werkorders->wijzigStatus($werkorderId, $status);

        // Houd de status van de afspraak gelijk met die van de werkorder.
        $werkorder = $this->werkorders->vindOpId($werkorderId);
        if ($werkorder !== null && $status === 'gereed') {
            $this->afspraken->wijzigStatus((int) $werkorder['afspraak_id'], 'gereed');
        }

        zetMelding('succes', 'Status bijgewerkt.');
        redirect('beheer/werkorder&id=' . $werkorderId);
    }

    public function wijsMonteurToe(): void
    {
        $this->vereisMedewerker();
        $this->controleerCsrf();

        $werkorderId = (int) ($_POST['werkorder_id'] ?? 0);
        $this->werkorders->wijsMonteurToe($werkorderId, (int) ($_POST['monteur_id'] ?? 0));

        zetMelding('succes', 'Monteur toegewezen.');
        redirect('beheer/werkorder&id=' . $werkorderId);
    }

    public function voegRegelToe(): void
    {
        $this->vereisMedewerker();
        $this->controleerCsrf();

        $werkorderId = (int) ($_POST['werkorder_id'] ?? 0);
        $omschrijving = trim((string) ($_POST['omschrijving'] ?? ''));
        $aantal = (float) ($_POST['aantal'] ?? 0);
        $prijs = (float) ($_POST['prijs_per_stuk'] ?? 0);
        $soort = ($_POST['soort'] ?? 'onderdeel') === 'arbeid' ? 'arbeid' : 'onderdeel';

        if ($omschrijving === '' || $aantal <= 0) {
            zetMelding('fout', 'Vul een omschrijving en een positief aantal in.');
            redirect('beheer/werkorder&id=' . $werkorderId);
        }

        $this->werkorders->voegRegelToe($werkorderId, $soort, $omschrijving, $aantal, $prijs);
        zetMelding('succes', 'Regel toegevoegd.');
        redirect('beheer/werkorder&id=' . $werkorderId);
    }

    private function vereisMedewerker(): void
    {
        if (!Auth::isMedewerker()) {
            zetMelding('fout', 'Log in als medewerker om het beheer te gebruiken.');
            redirect('beheer/inloggen');
        }
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
