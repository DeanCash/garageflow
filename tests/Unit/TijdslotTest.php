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
 *          vendor/bin/phpunit --filter TijdslotTest
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

use GarageFlow\Domein\Tijdslot;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TijdslotTest extends TestCase
{
    public function test_berekent_eindtijd_op_basis_van_duur(): void
    {
        $this->assertSame('10:00', (new Tijdslot('09:00', 60))->eindtijd());
        $this->assertSame('11:00', (new Tijdslot('09:30', 90))->eindtijd());
        $this->assertSame('12:15', (new Tijdslot('08:00', 255))->eindtijd());
    }

    public function test_overlappende_sloten_worden_herkend(): void
    {
        $eersteAfspraak = new Tijdslot('09:00', 60);   // 09:00 - 10:00
        $overlapt = new Tijdslot('09:30', 60);          // 09:30 - 10:30

        $this->assertTrue($eersteAfspraak->overlaptMet($overlapt));
        $this->assertTrue($overlapt->overlaptMet($eersteAfspraak));
    }

    public function test_aansluitende_sloten_overlappen_niet(): void
    {
        $voor = new Tijdslot('10:00', 60);   // 10:00 - 11:00
        $na = new Tijdslot('11:00', 60);     // 11:00 - 12:00

        $this->assertFalse($voor->overlaptMet($na));
    }

    public function test_ongeldige_tijd_geeft_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Tijdslot('25:00', 60);
    }
}
