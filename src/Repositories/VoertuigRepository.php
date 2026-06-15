<?php

namespace GarageFlow\Repositories;

use PDO;

class VoertuigRepository
{
    public function __construct(private PDO $db)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function vindVoorKlant(int $klantId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM voertuig WHERE klant_id = :klant ORDER BY kenteken'
        );
        $stmt->execute(['klant' => $klantId]);

        return $stmt->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function vindOpId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM voertuig WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $voertuig = $stmt->fetch();

        return $voertuig ?: null;
    }

    public function bestaatKenteken(string $kenteken): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM voertuig WHERE kenteken = :kenteken');
        $stmt->execute(['kenteken' => $kenteken]);

        return $stmt->fetchColumn() !== false;
    }

    public function voegToe(
        int $klantId,
        string $kenteken,
        string $merk,
        string $model,
        ?int $bouwjaar
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO voertuig (klant_id, kenteken, merk, model, bouwjaar)
             VALUES (:klant, :kenteken, :merk, :model, :bouwjaar)'
        );
        $stmt->execute([
            'klant'    => $klantId,
            'kenteken' => $kenteken,
            'merk'     => $merk,
            'model'    => $model,
            'bouwjaar' => $bouwjaar,
        ]);

        return (int) $this->db->lastInsertId();
    }
}
