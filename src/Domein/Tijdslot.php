<?php

namespace GarageFlow\Domein;

use InvalidArgumentException;

/**
 * Rekenwerk rond tijdsloten op een hefbrug. De database houdt met een unieke
 * sleutel al exact dezelfde starttijd tegen, maar twee afspraken kunnen ook
 * overlappen met een verschillende starttijd (bv. 09:00-10:00 en 09:30-10:30).
 * Die overlap controleren we in de applicatielogica met deze klasse.
 */
class Tijdslot
{
    private int $startMinuten;
    private int $eindMinuten;

    public function __construct(string $starttijd, int $duurMinuten)
    {
        if ($duurMinuten <= 0) {
            throw new InvalidArgumentException('De duur moet groter dan nul zijn.');
        }

        $this->startMinuten = self::naarMinuten($starttijd);
        $this->eindMinuten = $this->startMinuten + $duurMinuten;
    }

    /**
     * Geeft de eindtijd terug als "HH:MM", handig om op te slaan bij de afspraak.
     */
    public function eindtijd(): string
    {
        $uren = intdiv($this->eindMinuten, 60);
        $minuten = $this->eindMinuten % 60;

        return sprintf('%02d:%02d', $uren, $minuten);
    }

    /**
     * Bepaalt of dit tijdslot overlapt met een ander tijdslot op dezelfde dag.
     * Aansluitende sloten (10:00-11:00 en 11:00-12:00) overlappen niet.
     */
    public function overlaptMet(Tijdslot $ander): bool
    {
        return $this->startMinuten < $ander->eindMinuten
            && $ander->startMinuten < $this->eindMinuten;
    }

    private static function naarMinuten(string $tijd): int
    {
        if (preg_match('/^([0-1][0-9]|2[0-3]):([0-5][0-9])$/', $tijd, $delen) !== 1) {
            throw new InvalidArgumentException("Ongeldige tijd: {$tijd}");
        }

        return ((int) $delen[1]) * 60 + (int) $delen[2];
    }
}
