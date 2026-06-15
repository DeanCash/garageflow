<?php

namespace GarageFlow\Support;

/**
 * Minimale router. Routes worden geregistreerd per HTTP-methode en gekoppeld
 * aan een handler (een [controller, methode]-paar). De gevraagde route komt
 * binnen via ?route=... zodat het ook zonder URL-rewriting op XAMPP werkt.
 */
class Router
{
    /** @var array<string, callable> */
    private array $routes = [];

    public function get(string $route, callable $handler): void
    {
        $this->routes['GET ' . $route] = $handler;
    }

    public function post(string $route, callable $handler): void
    {
        $this->routes['POST ' . $route] = $handler;
    }

    public function verwerk(string $route, string $methode): void
    {
        $sleutel = $methode . ' ' . $route;

        if (!isset($this->routes[$sleutel])) {
            http_response_code(404);
            View::render('fout', ['melding' => 'Pagina niet gevonden.'], 'Niet gevonden');

            return;
        }

        ($this->routes[$sleutel])();
    }
}
