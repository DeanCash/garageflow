<?php
/** @var array<string,mixed> $werkorder */
/** @var array<string,mixed>|null $afspraak */
/** @var array<int,array<string,mixed>> $regels */
/** @var array<int,array<string,mixed>> $monteurs */
/** @var float $subtotaal */
/** @var float $btw */
/** @var float $totaal */
$euro = fn (float $bedrag): string => '&euro; ' . number_format($bedrag, 2, ',', '.');
// Terug naar de planning van de dag waar de werkorder bij hoort, zodat je op
// dezelfde dag terugkomt als waar je vandaan kwam.
$terugRoute = 'beheer/planning';
if (!empty($afspraak['datum'])) {
    $terugRoute .= '&datum=' . urlencode((string) $afspraak['datum']);
}
$terug = ['route' => $terugRoute, 'label' => 'Terug naar planning'];
?>
<h1>Werkorder #<?= e((string) $werkorder['id']) ?></h1>

<?php if ($afspraak !== null): ?>
<div class="kaart">
    <h2>Voertuig &amp; afspraak</h2>
    <table>
        <tr><th>Kenteken</th><td><?= e($afspraak['kenteken']) ?> (<?= e($afspraak['model']) ?>, <?= e((string) ($afspraak['bouwjaar'] ?? '-')) ?>)</td></tr>
        <tr><th>Klant</th><td><?= e($afspraak['klant_naam']) ?></td></tr>
        <tr><th>Dienst</th><td><?= e($afspraak['dienst_naam']) ?></td></tr>
        <tr><th>Datum</th><td><?= e(date('d-m-Y', strtotime($afspraak['datum']))) ?>, <?= e(substr($afspraak['starttijd'], 0, 5)) ?> - <?= e(substr($afspraak['eindtijd'], 0, 5)) ?> (<?= e($afspraak['hefbrug']) ?>)</td></tr>
    </table>
</div>
<?php endif; ?>

<div class="kaart">
    <h2>Status &amp; monteur</h2>
    <p>
        Huidige status:
        <span class="badge badge-<?= e($werkorder['status'] === 'gereed' ? 'gereed' : ($werkorder['status'] === 'in_uitvoering' ? 'in_uitvoering' : 'ingepland')) ?>">
            <?= e(str_replace('_', ' ', $werkorder['status'])) ?>
        </span>
        &nbsp;|&nbsp; Monteur: <?= e($werkorder['monteur_naam'] ?? 'nog niet toegewezen') ?>
    </p>

    <form method="post" action="<?= url('beheer/werkorder/status') ?>" style="display:inline-block; margin-right:20px;">
        <?= \GarageFlow\Support\Csrf::veld() ?>
        <input type="hidden" name="werkorder_id" value="<?= e((string) $werkorder['id']) ?>">
        <label for="status">Status wijzigen</label>
        <select id="status" name="status">
            <option value="open" <?= $werkorder['status'] === 'open' ? 'selected' : '' ?>>Open</option>
            <option value="in_uitvoering" <?= $werkorder['status'] === 'in_uitvoering' ? 'selected' : '' ?>>In uitvoering</option>
            <option value="gereed" <?= $werkorder['status'] === 'gereed' ? 'selected' : '' ?>>Gereed</option>
        </select>
        <div class="knoprij"><button type="submit" class="knop knop-klein">Opslaan</button></div>
    </form>

    <form method="post" action="<?= url('beheer/werkorder/monteur') ?>" style="display:inline-block;">
        <?= \GarageFlow\Support\Csrf::veld() ?>
        <input type="hidden" name="werkorder_id" value="<?= e((string) $werkorder['id']) ?>">
        <label for="monteur_id">Monteur toewijzen</label>
        <select id="monteur_id" name="monteur_id">
            <?php foreach ($monteurs as $monteur): ?>
                <option value="<?= e((string) $monteur['id']) ?>" <?= ((int) ($werkorder['monteur_id'] ?? 0) === (int) $monteur['id']) ? 'selected' : '' ?>>
                    <?= e($monteur['voornaam'] . ' ' . $monteur['achternaam']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="knoprij"><button type="submit" class="knop knop-klein">Toewijzen</button></div>
    </form>
</div>

<div class="kaart">
    <h2>Onderdelen &amp; arbeid</h2>
    <?php if ($regels === []): ?>
        <p class="hint">Nog geen regels geregistreerd.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Soort</th><th>Omschrijving</th><th>Aantal</th><th>Prijs/stuk</th><th>Totaal</th></tr></thead>
            <tbody>
            <?php foreach ($regels as $regel): ?>
                <?php $regeltotaal = (float) $regel['aantal'] * (float) $regel['prijs_per_stuk']; ?>
                <tr>
                    <td><?= e($regel['soort']) ?></td>
                    <td><?= e($regel['omschrijving']) ?></td>
                    <td><?= e(number_format((float) $regel['aantal'], 2, ',', '.')) ?></td>
                    <td><?= $euro((float) $regel['prijs_per_stuk']) ?></td>
                    <td><?= $euro($regeltotaal) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><td colspan="4" style="text-align:right;">Subtotaal</td><td><?= $euro($subtotaal) ?></td></tr>
                <tr><td colspan="4" style="text-align:right;">Btw 21%</td><td><?= $euro($btw) ?></td></tr>
                <tr><td colspan="4" style="text-align:right;"><strong>Totaal</strong></td><td><strong><?= $euro($totaal) ?></strong></td></tr>
            </tfoot>
        </table>
    <?php endif; ?>

    <h2 style="margin-top:24px;">Regel toevoegen</h2>
    <form method="post" action="<?= url('beheer/werkorder/regel') ?>">
        <?= \GarageFlow\Support\Csrf::veld() ?>
        <input type="hidden" name="werkorder_id" value="<?= e((string) $werkorder['id']) ?>">
        <label for="soort">Soort</label>
        <select id="soort" name="soort">
            <option value="onderdeel">Onderdeel</option>
            <option value="arbeid">Arbeid</option>
        </select>
        <label for="omschrijving">Omschrijving</label>
        <input type="text" id="omschrijving" name="omschrijving" placeholder="bijv. Remblokken vooras / Arbeid (uur)">
        <label for="aantal">Aantal</label>
        <input type="number" id="aantal" name="aantal" step="0.25" min="0.25" value="1">
        <label for="prijs_per_stuk">Prijs per stuk (&euro;)</label>
        <input type="number" id="prijs_per_stuk" name="prijs_per_stuk" step="0.01" min="0" value="0.00">
        <div class="knoprij"><button type="submit" class="knop">Toevoegen</button></div>
    </form>
</div>

<a class="knop knop-secundair" href="<?= url($terug['route']) ?>">Terug naar planning</a>
