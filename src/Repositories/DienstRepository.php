<?php

namespace GarageFlow\Repositories;

use PDO;

class DienstRepository
{
    public function __construct(private PDO $db)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function alle(): array
    {
        return $this->db->query('SELECT * FROM dienst ORDER BY naam')->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function vindOpId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM dienst WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $dienst = $stmt->fetch();

        return $dienst ?: null;
    }
}
