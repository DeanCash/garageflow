<?php

namespace GarageFlow\Repositories;

use PDO;

class AfspraakRepository
{
    public function __construct(private PDO $db)
    {
    }

    /**
     * Controleert of er op dezelfde hefbrug en dag een afspraak is die qua tijd
     * overlapt met het opgegeven tijdslot. Aansluitende sloten tellen niet als
     * overlap (starttijd < eind EN eindtijd > start).
     */
    public function heeftOverlap(int $hefbrugId, string $datum, string $starttijd, string $eindtijd): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM afspraak
             WHERE hefbrug_id = :hefbrug
               AND datum = :datum
               AND status <> 'geannuleerd'
               AND starttijd < :eind
               AND eindtijd > :start"
        );
        $stmt->execute([
            'hefbrug' => $hefbrugId,
            'datum'   => $datum,
            'eind'    => $eindtijd,
            'start'   => $starttijd,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function voegToe(
        int $voertuigId,
        int $dienstId,
        int $hefbrugId,
        string $datum,
        string $starttijd,
        string $eindtijd,
        ?string $opmerking
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO afspraak (voertuig_id, dienst_id, hefbrug_id, datum, starttijd, eindtijd, opmerking)
             VALUES (:voertuig, :dienst, :hefbrug, :datum, :start, :eind, :opmerking)'
        );
        $stmt->execute([
            'voertuig'  => $voertuigId,
            'dienst'    => $dienstId,
            'hefbrug'   => $hefbrugId,
            'datum'     => $datum,
            'start'     => $starttijd,
            'eind'      => $eindtijd,
            'opmerking' => $opmerking,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Afspraken van een klant, met dienst- en voertuiggegevens, voor het
     * overzicht in het klantportaal.
     *
     * @return array<int, array<string, mixed>>
     */
    public function vindVoorKlant(int $klantId): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, v.kenteken, v.model, d.naam AS dienst_naam
             FROM afspraak a
             JOIN voertuig v ON v.id = a.voertuig_id
             JOIN dienst d ON d.id = a.dienst_id
             WHERE v.klant_id = :klant
             ORDER BY a.datum DESC, a.starttijd DESC'
        );
        $stmt->execute(['klant' => $klantId]);

        return $stmt->fetchAll();
    }

    /**
     * Volledige dagplanning voor het werkplaatsbeheer.
     *
     * @return array<int, array<string, mixed>>
     */
    public function vindOpDatum(string $datum): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, v.kenteken, v.model,
                    CONCAT(k.voornaam, ' ', k.achternaam) AS klant_naam,
                    d.naam AS dienst_naam, h.aanduiding AS hefbrug,
                    w.id AS werkorder_id, w.status AS werkorder_status
             FROM afspraak a
             JOIN voertuig v ON v.id = a.voertuig_id
             JOIN klant k ON k.id = v.klant_id
             JOIN dienst d ON d.id = a.dienst_id
             JOIN hefbrug h ON h.id = a.hefbrug_id
             LEFT JOIN werkorder w ON w.afspraak_id = a.id
             WHERE a.datum = :datum
             ORDER BY a.starttijd, h.aanduiding"
        );
        $stmt->execute(['datum' => $datum]);

        return $stmt->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function vindOpId(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, v.kenteken, v.model, v.bouwjaar,
                    CONCAT(k.voornaam, ' ', k.achternaam) AS klant_naam,
                    d.naam AS dienst_naam, d.prijs AS dienst_prijs,
                    h.aanduiding AS hefbrug
             FROM afspraak a
             JOIN voertuig v ON v.id = a.voertuig_id
             JOIN klant k ON k.id = v.klant_id
             JOIN dienst d ON d.id = a.dienst_id
             JOIN hefbrug h ON h.id = a.hefbrug_id
             WHERE a.id = :id"
        );
        $stmt->execute(['id' => $id]);
        $afspraak = $stmt->fetch();

        return $afspraak ?: null;
    }

    /**
     * Controleert of een afspraak toebehoort aan een specifieke klant (via het
     * voertuig). Wordt gebruikt om te voorkomen dat een klant de afspraak van een
     * ander kan wijzigen of annuleren.
     */
    public function behoortToeAanKlant(int $afspraakId, int $klantId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM afspraak a
             JOIN voertuig v ON v.id = a.voertuig_id
             WHERE a.id = :id AND v.klant_id = :klant'
        );
        $stmt->execute(['id' => $afspraakId, 'klant' => $klantId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function wijzigStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE afspraak SET status = :status WHERE id = :id');
        $stmt->execute(['status' => $status, 'id' => $id]);
    }
}
