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
 *          vendor/bin/phpunit --filter KentekenTest
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

use GarageFlow\Domein\Kenteken;
use PHPUnit\Framework\TestCase;

class KentekenTest extends TestCase
{
    public function test_normaliseert_streepjes_en_hoofdletters(): void
    {
        $this->assertSame('12ABC3', Kenteken::normaliseer('12-abc-3'));
        $this->assertSame('XR123A', Kenteken::normaliseer(' xr-123-a '));
    }

    public function test_geldige_kentekens_worden_geaccepteerd(): void
    {
        $this->assertTrue(Kenteken::isGeldig('12-ABC-3'));   // sidecode 9-XXX-9
        $this->assertTrue(Kenteken::isGeldig('GZ-12-34'));   // XX-99-99
        $this->assertTrue(Kenteken::isGeldig('1-KZB-99'));   // 9-XXX-99
    }

    public function test_ongeldige_kentekens_worden_geweigerd(): void
    {
        $this->assertFalse(Kenteken::isGeldig('ABC'));        // te kort
        $this->assertFalse(Kenteken::isGeldig('1234567'));    // te lang
        $this->assertFalse(Kenteken::isGeldig('!!-AB-12'));   // ongeldige tekens
    }
}
