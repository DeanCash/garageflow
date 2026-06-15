<?php

namespace GarageFlow\Repositories;

use PDO;

class WerkorderRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function maakVoorAfspraak(int $afspraakId): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO werkorder (afspraak_id) VALUES (:afspraak)'
        );
        $stmt->execute(['afspraak' => $afspraakId]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function vindOpId(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT w.*, CONCAT(m.voornaam, ' ', m.achternaam) AS monteur_naam
             FROM werkorder w
             LEFT JOIN medewerker m ON m.id = w.monteur_id
             WHERE w.id = :id"
        );
        $stmt->execute(['id' => $id]);
        $werkorder = $stmt->fetch();

        return $werkorder ?: null;
    }

    public function wijsMonteurToe(int $werkorderId, int $monteurId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE werkorder SET monteur_id = :monteur WHERE id = :id'
        );
        $stmt->execute(['monteur' => $monteurId, 'id' => $werkorderId]);
    }

    public function wijzigStatus(int $werkorderId, string $status): void
    {
        // Houd de start- en gereedtijd bij wanneer de status verandert.
        $extra = '';
        if ($status === 'in_uitvoering') {
            $extra = ', gestart_op = COALESCE(gestart_op, NOW())';
        } elseif ($status === 'gereed') {
            $extra = ', gereed_op = NOW()';
        }

        $stmt = $this->db->prepare(
            "UPDATE werkorder SET status = :status{$extra} WHERE id = :id"
        );
        $stmt->execute(['status' => $status, 'id' => $werkorderId]);
    }

    public function voegRegelToe(
        int $werkorderId,
        string $soort,
        string $omschrijving,
        float $aantal,
        float $prijsPerStuk
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO werkorderregel (werkorder_id, soort, omschrijving, aantal, prijs_per_stuk)
             VALUES (:werkorder, :soort, :omschrijving, :aantal, :prijs)'
        );
        $stmt->execute([
            'werkorder'    => $werkorderId,
            'soort'        => $soort,
            'omschrijving' => $omschrijving,
            'aantal'       => $aantal,
            'prijs'        => $prijsPerStuk,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function regels(int $werkorderId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM werkorderregel WHERE werkorder_id = :werkorder ORDER BY id'
        );
        $stmt->execute(['werkorder' => $werkorderId]);

        return $stmt->fetchAll();
    }
}
