<?php

/*
 * ===========================================================================
 *  TESTS DRAAIEN  (lees dit als je gevraagd wordt de tests te runnen)
 * ===========================================================================
 *  Voer uit vanuit de projectmap (de map met composer.json en phpunit.xml).
 *
 *    > ALLE tests (unit + integratie):
 *          vendor/bin/phpunit
 *      of via Composer:
 *          composer test
 *
 *    > ALLEEN de unit-tests (geen database nodig):
 *          vendor/bin/phpunit --testsuite Unit
 *
 *    > ALLEEN de integratietests (vereist database 'garageflow_test'):
 *          vendor/bin/phpunit --testsuite Integratie
 *
 *    > Alleen deze testklasse:
 *          vendor/bin/phpunit --filter BedragTest
 *
 *  Dit bestand bevat UNIT-tests: pure PHP-logica, er is GEEN database nodig.
 *
 *  Voorwaarde voor de integratietest: start XAMPP MySQL en maak eenmalig de
 *  lege database 'garageflow_test' aan, bijvoorbeeld:
 *        mysql -u root -e "CREATE DATABASE IF NOT EXISTS garageflow_test"
 *  Standaardverbinding: host 127.0.0.1, gebruiker root, leeg wachtwoord. Aanpassen
 *  kan via de omgevingsvariabelen GF_TEST_DB_HOST / _NAAM / _USER / _PASS.
 *  (Is er geen testdatabase, dan slaat de integratietest zichzelf netjes over.)
 * ===========================================================================
 */

namespace GarageFlow\Tests\Unit;

use GarageFlow\Domein\Bedrag;
use PHPUnit\Framework\TestCase;

class BedragTest extends TestCase
{
    /** @var array<int, array{aantal: float, prijs_per_stuk: float}> */
    private array $regels = [
        ['aantal' => 2, 'prijs_per_stuk' => 45.00],   // 90,00 (remblokken)
        ['aantal' => 1.5, 'prijs_per_stuk' => 60.00], // 90,00 (1,5 uur arbeid)
    ];

    public function test_subtotaal_telt_alle_regels_op(): void
    {
        $this->assertSame(180.00, Bedrag::subtotaal($this->regels));
    }

    public function test_btw_is_21_procent(): void
    {
        $this->assertSame(37.80, Bedrag::btw(180.00));
    }

    public function test_totaal_is_subtotaal_plus_btw(): void
    {
        $this->assertSame(217.80, Bedrag::totaalInclusiefBtw($this->regels));
    }

    public function test_lege_werkorder_is_nul(): void
    {
        $this->assertSame(0.0, Bedrag::subtotaal([]));
        $this->assertSame(0.0, Bedrag::totaalInclusiefBtw([]));
    }
}
