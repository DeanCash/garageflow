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
        // Al ingelogd als medewerker? Dan direct door naar de planning in plaats
        // van opnieuw het inlogformulier tonen.
        if (Auth::isMedewerker()) {
            redirect('beheer/planning');
        }

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
        // Een nieuwe werkorder start als 'open' (werk nog niet begonnen). Houd de
        // afspraakstatus daarmee in lijn via dezelfde mapping, zodat de planning en
        // de werkorderpagina vanaf het begin hetzelfde tonen.
        $this->afspraken->wijzigStatus($afspraakId, $this->afspraakStatusBij('open'));

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

        // Houd de afspraakstatus gelijk met de werkorderstatus via één vaste
        // mapping (zie afspraakStatusBij), zodat de dagplanning (toont de
        // afspraakstatus) en de werkorderpagina (toont de werkorderstatus) altijd
        // hetzelfde laten zien.
        $werkorder = $this->werkorders->vindOpId($werkorderId);
        if ($werkorder !== null) {
            $this->afspraken->wijzigStatus(
                (int) $werkorder['afspraak_id'],
                $this->afspraakStatusBij($status)
            );
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

    /**
     * Eén bron van waarheid voor het koppelen van een werkorderstatus aan de
     * bijbehorende afspraakstatus. De afspraak (zichtbaar op de planning) volgt
     * zo altijd de werkorder (zichtbaar op de werkorderpagina):
     *   open          -> ingepland     (werkorder bestaat, werk nog niet gestart)
     *   in_uitvoering -> in_uitvoering
     *   gereed        -> gereed
     */
    private function afspraakStatusBij(string $werkorderStatus): string
    {
        return match ($werkorderStatus) {
            'gereed'        => 'gereed',
            'in_uitvoering' => 'in_uitvoering',
            default         => 'ingepland',
        };
    }

    private function vereisMedewerker(): void
    {
        if (!Auth::isMedewerker()) {
            zetMelding('info', 'Log in als medewerker om het beheer te gebruiken.');
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
