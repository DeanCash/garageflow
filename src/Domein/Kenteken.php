<?php

namespace GarageFlow\Domein;

/**
 * Hulpfuncties rond Nederlandse kentekens. Een kenteken is onder de AVG een
 * persoonsgegeven, dus we normaliseren het netjes en valideren het formaat
 * voordat het wordt opgeslagen.
 */
class Kenteken
{
    /**
     * Haalt streepjes en spaties weg en zet alles in hoofdletters.
     */
    public static function normaliseer(string $kenteken): string
    {
        $schoon = str_replace(['-', ' '], '', $kenteken);

        return strtoupper(trim($schoon));
    }

    /**
     * Controleert of het kenteken uit 6 tekens bestaat en uit een geldige
     * combinatie van letter- en cijfergroepen. Dit dekt de gangbare sidecodes
     * die je op moderne (BMW-)voertuigen tegenkomt.
     */
    public static function isGeldig(string $kenteken): bool
    {
        $kenteken = self::normaliseer($kenteken);

        if (strlen($kenteken) !== 6) {
            return false;
        }

        $patronen = [
            '/^[A-Z]{2}[0-9]{2}[0-9]{2}$/',  // XX-99-99
            '/^[0-9]{2}[0-9]{2}[A-Z]{2}$/',  // 99-99-XX
            '/^[0-9]{2}[A-Z]{2}[0-9]{2}$/',  // 99-XX-99
            '/^[A-Z]{2}[0-9]{2}[A-Z]{2}$/',  // XX-99-XX
            '/^[A-Z]{2}[A-Z]{2}[0-9]{2}$/',  // XX-XX-99
            '/^[0-9]{2}[A-Z]{2}[A-Z]{2}$/',  // 99-XX-XX
            '/^[0-9]{1}[A-Z]{3}[0-9]{2}$/',  // 9-XXX-99
            '/^[0-9]{2}[A-Z]{3}[0-9]{1}$/',  // 99-XXX-9
        ];

        foreach ($patronen as $patroon) {
            if (preg_match($patroon, $kenteken) === 1) {
                return true;
            }
        }

        return false;
    }
}
