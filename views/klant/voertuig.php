<?php
/** @var array<string,string> $fouten */
/** @var array<string,mixed> $oud */
$fouten = $fouten ?? [];
$oud = $oud ?? [];
$terug = ['route' => 'klant/dashboard', 'label' => 'Terug naar overzicht'];
?>
<div class="kaart" style="max-width: 480px;">
    <h1>Voertuig toevoegen</h1>
    <form method="post" action="<?= url('klant/voertuig') ?>">
        <?= \GarageFlow\Support\Csrf::veld() ?>

        <label for="kenteken">Kenteken</label>
        <input type="text" id="kenteken" name="kenteken" placeholder="bijv. 12-ABC-3" value="<?= e($oud['kenteken'] ?? '') ?>">
        <?php if (isset($fouten['kenteken'])): ?><p class="veldfout"><?= e($fouten['kenteken']) ?></p><?php endif; ?>

        <label for="merk">Merk</label>
        <input type="text" id="merk" name="merk" value="<?= e($oud['merk'] ?? 'BMW') ?>">

        <label for="model">Model</label>
        <input type="text" id="model" name="model" placeholder="bijv. 320i Touring" value="<?= e($oud['model'] ?? '') ?>">
        <?php if (isset($fouten['model'])): ?><p class="veldfout"><?= e($fouten['model']) ?></p><?php endif; ?>

        <label for="bouwjaar">Bouwjaar <span class="hint">(optioneel)</span></label>
        <input type="number" id="bouwjaar" name="bouwjaar" min="1950" max="2026" value="<?= e($oud['bouwjaar'] ?? '') ?>">

        <div class="knoprij">
            <button type="submit" class="knop">Opslaan</button>
            <a class="knop knop-secundair" href="<?= url('klant/dashboard') ?>">Annuleren</a>
        </div>
    </form>
</div>
