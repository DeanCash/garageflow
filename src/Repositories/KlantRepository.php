<?php

namespace GarageFlow\Repositories;

use PDO;

class KlantRepository
{
    public function __construct(private PDO $db)
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function vindOpEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM klant WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $klant = $stmt->fetch();

        return $klant ?: null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function vindOpId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM klant WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $klant = $stmt->fetch();

        return $klant ?: null;
    }

    public function voegToe(
        string $voornaam,
        string $achternaam,
        string $email,
        ?string $telefoon,
        string $wachtwoordHash
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO klant (voornaam, achternaam, email, telefoon, wachtwoord_hash)
             VALUES (:voornaam, :achternaam, :email, :telefoon, :hash)'
        );
        $stmt->execute([
            'voornaam'   => $voornaam,
            'achternaam' => $achternaam,
            'email'      => $email,
            'telefoon'   => $telefoon,
            'hash'       => $wachtwoordHash,
        ]);

        return (int) $this->db->lastInsertId();
    }
}
