<?php

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
