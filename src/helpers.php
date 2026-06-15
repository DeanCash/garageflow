<?php

use GarageFlow\Support\Auth;

/**
 * Kleine, veelgebruikte hulpfuncties die overal in de applicatie en de views
 * beschikbaar moeten zijn.
 */

if (!function_exists('url')) {
    /**
     * Bouwt een interne URL op basis van de geconfigureerde basis-URL.
     */
    function url(string $route = ''): string
    {
        $basis = defined('BASE_URL') ? BASE_URL : '';

        if ($route === '') {
            return $basis . '/index.php';
        }

        return $basis . '/index.php?route=' . $route;
    }
}

if (!function_exists('asset')) {
    function asset(string $pad): string
    {
        $basis = defined('BASE_URL') ? BASE_URL : '';

        return $basis . '/assets/' . ltrim($pad, '/');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $route): never
    {
        header('Location: ' . url($route));
        exit;
    }
}

if (!function_exists('e')) {
    /**
     * Ontsnapt uitvoer tegen XSS. Korte naam omdat hij in views veel terugkomt.
     */
    function e(?string $waarde): string
    {
        return htmlspecialchars($waarde ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('zetMelding')) {
    /**
     * Bewaart een flash-melding (succes of fout) tot de volgende paginaweergave.
     */
    function zetMelding(string $type, string $tekst): void
    {
        Auth::start();
        $_SESSION['meldingen'][$type] = $tekst;
    }
}

if (!function_exists('haalMeldingen')) {
    /**
     * @return array<string, string>
     */
    function haalMeldingen(): array
    {
        Auth::start();
        $meldingen = $_SESSION['meldingen'] ?? [];
        unset($_SESSION['meldingen']);

        return $meldingen;
    }
}
