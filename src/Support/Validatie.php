<?php

namespace GarageFlow\Support;

/**
 * Verzamelt server-side validatie van formulierinvoer. We vertrouwen nooit op
 * alleen de validatie in de browser, dus elke invoer wordt hier opnieuw
 * gecontroleerd voordat hij de database in gaat.
 */
class Validatie
{
    /** @var array<string, string> */
    private array $fouten = [];

    /** @param array<string, mixed> $invoer */
    public function __construct(private array $invoer)
    {
    }

    public function verplicht(string $veld, string $label): self
    {
        $waarde = trim((string) ($this->invoer[$veld] ?? ''));

        if ($waarde === '') {
            $this->fouten[$veld] = "{$label} is verplicht.";
        }

        return $this;
    }

    public function email(string $veld, string $label): self
    {
        $waarde = trim((string) ($this->invoer[$veld] ?? ''));

        if ($waarde !== '' && !filter_var($waarde, FILTER_VALIDATE_EMAIL)) {
            $this->fouten[$veld] = "{$label} is geen geldig e-mailadres.";
        }

        return $this;
    }

    public function minimaleLengte(string $veld, string $label, int $lengte): self
    {
        $waarde = (string) ($this->invoer[$veld] ?? '');

        if (strlen($waarde) < $lengte) {
            $this->fouten[$veld] = "{$label} moet minimaal {$lengte} tekens bevatten.";
        }

        return $this;
    }

    public function isGeldig(): bool
    {
        return $this->fouten === [];
    }

    /** @return array<string, string> */
    public function fouten(): array
    {
        return $this->fouten;
    }

    public function voegFoutToe(string $veld, string $melding): void
    {
        $this->fouten[$veld] = $melding;
    }
}
