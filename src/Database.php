<?php

namespace GarageFlow;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Maakt en hergebruikt de PDO-verbinding met de MySQL-database. We gebruiken
 * PDO met prepared statements; dat is de standaardmanier om SQL-injectie te
 * voorkomen.
 */
class Database
{
    private static ?PDO $verbinding = null;

    /**
     * @param array{host:string, port:int, naam:string, gebruiker:string, wachtwoord:string, charset:string} $config
     */
    public static function verbind(array $config): PDO
    {
        if (self::$verbinding instanceof PDO) {
            return self::$verbinding;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['naam'],
            $config['charset']
        );

        try {
            self::$verbinding = new PDO($dsn, $config['gebruiker'], $config['wachtwoord'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // We tonen de technische melding niet aan de gebruiker in productie.
            throw new RuntimeException('Kan geen verbinding maken met de database.', 0, $e);
        }

        return self::$verbinding;
    }

    /**
     * Wordt door de tests gebruikt om de gedeelde verbinding los te koppelen.
     */
    public static function resetVerbinding(): void
    {
        self::$verbinding = null;
    }
}
