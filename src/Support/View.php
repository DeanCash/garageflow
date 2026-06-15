<?php

namespace GarageFlow\Support;

use RuntimeException;

/**
 * Heel lichte template-renderer. Een view is gewoon een PHP-bestand in de map
 * /views. De meegegeven data wordt als losse variabelen beschikbaar gemaakt en
 * de view wordt binnen een gedeelde layout getoond.
 */
class View
{
    private static string $viewsPad = __DIR__ . '/../../views';

    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $view, array $data = [], string $titel = 'GarageFlow'): void
    {
        $bestand = self::$viewsPad . '/' . $view . '.php';

        if (!is_file($bestand)) {
            throw new RuntimeException("View bestaat niet: {$view}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $bestand;
        $inhoud = ob_get_clean();

        require self::$viewsPad . '/layout.php';
    }

    /**
     * Korte helper om uitvoer te ontsnappen tegen XSS.
     */
    public static function e(?string $waarde): string
    {
        return htmlspecialchars($waarde ?? '', ENT_QUOTES, 'UTF-8');
    }
}
