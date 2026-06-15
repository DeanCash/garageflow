<?php

namespace GarageFlow\Repositories;

use PDO;

class HefbrugRepository
{
    public function __construct(private PDO $db)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function alle(): array
    {
        return $this->db->query('SELECT * FROM hefbrug ORDER BY aanduiding')->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function vindOpId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM hefbrug WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $hefbrug = $stmt->fetch();

        return $hefbrug ?: null;
    }
}
