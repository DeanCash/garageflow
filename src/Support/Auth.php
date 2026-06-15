<?php

namespace GarageFlow\Support;

/**
 * Eenvoudig sessiebeheer voor zowel klanten als medewerkers. Wachtwoorden
 * worden nergens in platte tekst opgeslagen; we werken met password_hash en
 * password_verify.
 */
class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function logKlantIn(int $klantId, string $naam): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['klant_id'] = $klantId;
        $_SESSION['klant_naam'] = $naam;
    }

    public static function logMedewerkerIn(int $medewerkerId, string $naam, string $rol): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['medewerker_id'] = $medewerkerId;
        $_SESSION['medewerker_naam'] = $naam;
        $_SESSION['medewerker_rol'] = $rol;
    }

    public static function klantId(): ?int
    {
        self::start();

        return isset($_SESSION['klant_id']) ? (int) $_SESSION['klant_id'] : null;
    }

    public static function medewerkerId(): ?int
    {
        self::start();

        return isset($_SESSION['medewerker_id']) ? (int) $_SESSION['medewerker_id'] : null;
    }

    public static function rol(): ?string
    {
        self::start();

        return $_SESSION['medewerker_rol'] ?? null;
    }

    public static function isKlant(): bool
    {
        return self::klantId() !== null;
    }

    public static function isMedewerker(): bool
    {
        return self::medewerkerId() !== null;
    }

    public static function uitloggen(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();
    }
}
