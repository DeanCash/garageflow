<?php

namespace GarageFlow\Domein;

/**
 * Berekent het totaalbedrag van een werkorder op basis van de regels
 * (onderdelen en arbeid). Houdt rekening met het btw-tarief van 21%.
 */
class Bedrag
{
    public const BTW_TARIEF = 0.21;

    /**
     * @param array<int, array{aantal: float|int, prijs_per_stuk: float|int}> $regels
     */
    public static function subtotaal(array $regels): float
    {
        $subtotaal = 0.0;

        foreach ($regels as $regel) {
            $subtotaal += ((float) $regel['aantal']) * ((float) $regel['prijs_per_stuk']);
        }

        return round($subtotaal, 2);
    }

    public static function btw(float $subtotaal): float
    {
        return round($subtotaal * self::BTW_TARIEF, 2);
    }

    /**
     * @param array<int, array{aantal: float|int, prijs_per_stuk: float|int}> $regels
     */
    public static function totaalInclusiefBtw(array $regels): float
    {
        $subtotaal = self::subtotaal($regels);

        return round($subtotaal + self::btw($subtotaal), 2);
    }
}
