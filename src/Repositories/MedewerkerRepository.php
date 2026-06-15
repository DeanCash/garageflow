<?php

namespace GarageFlow\Repositories;

use PDO;

class MedewerkerRepository
{
    public function __construct(private PDO $db)
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function vindOpEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM medewerker WHERE email = :email AND actief = 1'
        );
        $stmt->execute(['email' => $email]);
        $medewerker = $stmt->fetch();

        return $medewerker ?: null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function monteurs(): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM medewerker WHERE rol = 'monteur' AND actief = 1
             ORDER BY voornaam"
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
