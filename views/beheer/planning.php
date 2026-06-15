<?php
/** @var string $datum */
/** @var array<int,array<string,mixed>> $afspraken */
?>
<h1>Dagplanning</h1>

<div class="kaart">
    <form method="get" action="<?= url() ?>" style="display:flex; gap:12px; align-items:flex-end; max-width:360px;">
        <input type="hidden" name="route" value="beheer/planning">
        <div style="flex:1;">
            <label for="datum">Datum</label>
            <input type="date" id="datum" name="datum" value="<?= e($datum) ?>">
        </div>
        <button type="submit" class="knop">Toon</button>
    </form>
</div>

<div class="kaart">
    <h2><?= e(date('l d-m-Y', strtotime($datum))) ?></h2>
    <?php if ($afspraken === []): ?>
        <p class="hint">Geen afspraken op deze dag.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>Tijd</th><th>Hefbrug</th><th>Kenteken</th><th>Klant</th><th>Dienst</th><th>Status</th><th>Werkorder</th></tr>
            </thead>
            <tbody>
            <?php foreach ($afspraken as $afspraak): ?>
                <tr>
                    <td><?= e(substr($afspraak['starttijd'], 0, 5)) ?> - <?= e(substr($afspraak['eindtijd'], 0, 5)) ?></td>
                    <td><?= e($afspraak['hefbrug']) ?></td>
                    <td><strong><?= e($afspraak['kenteken']) ?></strong><br><span class="hint"><?= e($afspraak['model']) ?></span></td>
                    <td><?= e($afspraak['klant_naam']) ?></td>
                    <td><?= e($afspraak['dienst_naam']) ?></td>
                    <td><span class="badge badge-<?= e($afspraak['status']) ?>"><?= e(str_replace('_', ' ', $afspraak['status'])) ?></span></td>
                    <td>
                        <?php if ($afspraak['werkorder_id'] !== null): ?>
                            <a class="knop knop-secundair knop-klein" href="<?= url('beheer/werkorder') ?>&id=<?= e((string) $afspraak['werkorder_id']) ?>">Open</a>
                        <?php elseif ($afspraak['status'] !== 'geannuleerd'): ?>
                            <form method="post" action="<?= url('beheer/werkorder/maak') ?>" style="margin:0;">
                                <?= \GarageFlow\Support\Csrf::veld() ?>
                                <input type="hidden" name="afspraak_id" value="<?= e((string) $afspraak['id']) ?>">
                                <input type="hidden" name="datum" value="<?= e($datum) ?>">
                                <button type="submit" class="knop knop-klein">Werkorder</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
