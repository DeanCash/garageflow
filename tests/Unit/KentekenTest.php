<?php

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
