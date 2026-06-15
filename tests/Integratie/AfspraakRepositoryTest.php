<?php

namespace GarageFlow\Tests\Integratie;

use GarageFlow\Repositories\AfspraakRepository;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;

/**
 * Integratietest tegen een echte MySQL-testdatabase. Test of een afspraak wordt
 * opgeslagen en of de overlapcontrole een dubbele boeking op dezelfde hefbrug
 * tegenhoudt.
 *
 * Vereist een (lege) database 'garageflow_test'. Wanneer er geen verbinding
 * mogelijk is, wordt de test overgeslagen zodat de unit-tests blijven werken.
 * Verbinding is via omgevingsvariabelen aan te passen (GF_TEST_DB_*).
 */
class AfspraakRepositoryTest extends TestCase
{
    private ?PDO $db = null;

    protected function setUp(): void
    {
        $this->db = $this->maakVerbinding();

        if ($this->db === null) {
            $this->markTestSkipped('Geen testdatabase beschikbaar (garageflow_test).');
        }

        $this->laadSchema($this->db);
        $this->plaatsBasisgegevens($this->db);
    }

    public function test_afspraak_wordt_opgeslagen(): void
    {
        $repo = new AfspraakRepository($this->db);

        $id = $repo->voegToe(1, 1, 1, '2026-06-20', '09:00:00', '10:00:00', null);

        $this->assertGreaterThan(0, $id);
    }

    public function test_overlap_wordt_gedetecteerd(): void
    {
        $repo = new AfspraakRepository($this->db);
        $repo->voegToe(1, 1, 1, '2026-06-20', '09:00:00', '10:00:00', null);

        // Zelfde hefbrug, overlappend tijdslot -> moet als overlap gelden.
        $this->assertTrue($repo->heeftOverlap(1, '2026-06-20', '09:30:00', '10:30:00'));

        // Aansluitend tijdslot op dezelfde brug -> geen overlap.
        $this->assertFalse($repo->heeftOverlap(1, '2026-06-20', '10:00:00', '11:00:00'));

        // Andere hefbrug op hetzelfde tijdstip -> geen overlap.
        $this->assertFalse($repo->heeftOverlap(2, '2026-06-20', '09:30:00', '10:30:00'));
    }

    public function test_afspraak_behoort_alleen_toe_aan_eigen_klant(): void
    {
        $repo = new AfspraakRepository($this->db);
        $id = $repo->voegToe(1, 1, 1, '2026-06-20', '09:00:00', '10:00:00', null);

        // Klant 1 bezit voertuig 1 -> de afspraak is van klant 1, niet van een ander.
        $this->assertTrue($repo->behoortToeAanKlant($id, 1));
        $this->assertFalse($repo->behoortToeAanKlant($id, 999));
    }

    private function maakVerbinding(): ?PDO
    {
        $host = getenv('GF_TEST_DB_HOST') ?: '127.0.0.1';
        $naam = getenv('GF_TEST_DB_NAAM') ?: 'garageflow_test';
        $gebruiker = getenv('GF_TEST_DB_USER') ?: 'root';
        $wachtwoord = getenv('GF_TEST_DB_PASS') ?: '';

        try {
            return new PDO(
                "mysql:host={$host};dbname={$naam};charset=utf8mb4",
                $gebruiker,
                $wachtwoord,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException) {
            return null;
        }
    }

    private function laadSchema(PDO $db): void
    {
        $sql = (string) file_get_contents(__DIR__ . '/../../database/schema.sql');

        // De testdatabase bestaat al; we draaien alleen de DROP/CREATE TABLE-
        // statements. Per statement halen we eerst de commentaarregels weg en
        // slaan we CREATE DATABASE / USE (die over meerdere regels lopen) over.
        foreach (explode(';', $sql) as $ruwStatement) {
            $regels = preg_split('/\r?\n/', $ruwStatement) ?: [];
            $regels = array_filter(
                $regels,
                static fn (string $regel): bool => !str_starts_with(ltrim($regel), '--')
            );
            $statement = trim(implode("\n", $regels));

            if ($statement === ''
                || stripos($statement, 'CREATE DATABASE') === 0
                || stripos($statement, 'USE ') === 0
            ) {
                continue;
            }

            $db->exec($statement);
        }
    }

    private function plaatsBasisgegevens(PDO $db): void
    {
        $db->exec("INSERT INTO hefbrug (id, aanduiding, type) VALUES (1, 'Brug 1', 'tweekolom'), (2, 'Brug 2', 'tweekolom')");
        $db->exec("INSERT INTO dienst (id, naam, standaard_duur_min, prijs) VALUES (1, 'Kleine beurt', 60, 149.00)");
        $db->exec("INSERT INTO klant (id, voornaam, achternaam, email, wachtwoord_hash) VALUES (1, 'Test', 'Klant', 'test@example.com', 'x')");
        $db->exec("INSERT INTO voertuig (id, klant_id, kenteken, merk, model) VALUES (1, 1, '12ABC3', 'BMW', '320i')");
    }
}
