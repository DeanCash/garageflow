<?php

namespace GarageFlow\Support;

/**
 * Genereert en controleert een CSRF-token. Elk formulier dat data wijzigt
 * (POST) krijgt een verborgen veld met dit token, zodat aanvragen van buitenaf
 * worden geweigerd.
 */
class Csrf
{
    public static function token(): string
    {
        Auth::start();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function veld(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES);

        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    public static function controleer(?string $token): bool
    {
        Auth::start();

        return is_string($token)
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}
