<?php
/** @var array<int,array<string,mixed>> $voertuigen */
/** @var array<int,array<string,mixed>> $afspraken */
?>
<h1>Mijn overzicht</h1>

<div class="kaart">
    <h2>Mijn voertuigen</h2>
    <?php if ($voertuigen === []): ?>
        <p class="hint">Je hebt nog geen voertuig toegevoegd.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Kenteken</th><th>Merk</th><th>Model</th><th>Bouwjaar</th></tr></thead>
            <tbody>
            <?php foreach ($voertuigen as $voertuig): ?>
                <tr>
                    <td><strong><?= e($voertuig['kenteken']) ?></strong></td>
                    <td><?= e($voertuig['merk']) ?></td>
                    <td><?= e($voertuig['model']) ?></td>
                    <td><?= e((string) ($voertuig['bouwjaar'] ?? '-')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <div class="knoprij">
        <a class="knop knop-secundair knop-klein" href="<?= url('klant/voertuig') ?>">Voertuig toevoegen</a>
    </div>
</div>

<div class="kaart">
    <h2>Mijn afspraken</h2>
    <?php if ($afspraken === []): ?>
        <p class="hint">Je hebt nog geen afspraken. <a href="<?= url('klant/afspraak') ?>">Maak je eerste afspraak</a>.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Datum</th><th>Tijd</th><th>Voertuig</th><th>Dienst</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($afspraken as $afspraak): ?>
                <tr>
                    <td><?= e(date('d-m-Y', strtotime($afspraak['datum']))) ?></td>
                    <td><?= e(substr($afspraak['starttijd'], 0, 5)) ?> - <?= e(substr($afspraak['eindtijd'], 0, 5)) ?></td>
                    <td><?= e($afspraak['kenteken']) ?> (<?= e($afspraak['model']) ?>)</td>
                    <td><?= e($afspraak['dienst_naam']) ?></td>
                    <td><span class="badge badge-<?= e($afspraak['status']) ?>"><?= e(str_replace('_', ' ', $afspraak['status'])) ?></span></td>
                    <td>
                        <?php if ($afspraak['status'] === 'ingepland'): ?>
                            <form method="post" action="<?= url('klant/afspraak/annuleren') ?>" onsubmit="return confirm('Afspraak annuleren?');">
                                <?= \GarageFlow\Support\Csrf::veld() ?>
                                <input type="hidden" name="afspraak_id" value="<?= e((string) $afspraak['id']) ?>">
                                <button type="submit" class="knop knop-gevaar knop-klein">Annuleren</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <div class="knoprij">
        <a class="knop knop-klein" href="<?= url('klant/afspraak') ?>">Nieuwe afspraak</a>
    </div>
</div>
