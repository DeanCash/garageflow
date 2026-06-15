<?php

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
