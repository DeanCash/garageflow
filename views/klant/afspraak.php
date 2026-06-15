<?php
/** @var array<int,array<string,mixed>> $voertuigen */
/** @var array<int,array<string,mixed>> $diensten */
/** @var array<int,array<string,mixed>> $hefbruggen */
/** @var array<string,string> $fouten */
/** @var array<string,mixed> $oud */
$fouten = $fouten ?? [];
$oud = $oud ?? [];
$terug = ['route' => 'klant/dashboard', 'label' => 'Terug naar overzicht'];
?>
<div class="kaart" style="max-width: 560px;">
    <h1>Afspraak maken</h1>

    <?php if ($voertuigen === []): ?>
        <p class="hint">Voeg eerst een voertuig toe voordat je een afspraak maakt.</p>
        <a class="knop" href="<?= url('klant/voertuig') ?>">Voertuig toevoegen</a>
    <?php else: ?>
        <form method="post" action="<?= url('klant/afspraak') ?>">
            <?= \GarageFlow\Support\Csrf::veld() ?>

            <label for="voertuig_id">Voertuig</label>
            <select id="voertuig_id" name="voertuig_id">
                <?php foreach ($voertuigen as $voertuig): ?>
                    <option value="<?= e((string) $voertuig['id']) ?>" <?= (($oud['voertuig_id'] ?? '') == $voertuig['id']) ? 'selected' : '' ?>>
                        <?= e($voertuig['kenteken']) ?> - <?= e($voertuig['merk'] . ' ' . $voertuig['model']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($fouten['voertuig_id'])): ?><p class="veldfout"><?= e($fouten['voertuig_id']) ?></p><?php endif; ?>

            <label for="dienst_id">Dienst</label>
            <select id="dienst_id" name="dienst_id">
                <?php foreach ($diensten as $dienst): ?>
                    <option value="<?= e((string) $dienst['id']) ?>" <?= (($oud['dienst_id'] ?? '') == $dienst['id']) ? 'selected' : '' ?>>
                        <?= e($dienst['naam']) ?> (&plusmn; <?= e((string) $dienst['standaard_duur_min']) ?> min, &euro; <?= e(number_format((float) $dienst['prijs'], 2, ',', '.')) ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="hefbrug_id">Hefbrug</label>
            <select id="hefbrug_id" name="hefbrug_id">
                <?php foreach ($hefbruggen as $hefbrug): ?>
                    <option value="<?= e((string) $hefbrug['id']) ?>" <?= (($oud['hefbrug_id'] ?? '') == $hefbrug['id']) ? 'selected' : '' ?>>
                        <?= e($hefbrug['aanduiding']) ?> (<?= e($hefbrug['type']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="datum">Datum</label>
            <input type="date" id="datum" name="datum" min="<?= e(date('Y-m-d')) ?>" value="<?= e($oud['datum'] ?? date('Y-m-d')) ?>">
            <?php if (isset($fouten['datum'])): ?><p class="veldfout"><?= e($fouten['datum']) ?></p><?php endif; ?>

            <label for="starttijd">Starttijd <span class="hint">(openingstijden 08:00 - 17:30)</span></label>
            <input type="time" id="starttijd" name="starttijd" min="08:00" max="17:30" step="900" value="<?= e($oud['starttijd'] ?? '09:00') ?>">
            <?php if (isset($fouten['starttijd'])): ?><p class="veldfout"><?= e($fouten['starttijd']) ?></p><?php endif; ?>

            <label for="opmerking">Opmerking <span class="hint">(optioneel)</span></label>
            <textarea id="opmerking" name="opmerking" rows="3"><?= e($oud['opmerking'] ?? '') ?></textarea>

            <div class="knoprij">
                <button type="submit" class="knop">Afspraak inplannen</button>
                <a class="knop knop-secundair" href="<?= url('klant/dashboard') ?>">Annuleren</a>
            </div>
        </form>
    <?php endif; ?>
</div>
