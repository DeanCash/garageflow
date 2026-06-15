<?php
/** @var array<string,string> $fouten */
/** @var array<string,mixed> $oud */
$fouten = $fouten ?? [];
$oud = $oud ?? [];
$terug = ['route' => 'home', 'label' => 'Terug naar home'];
?>
<div class="kaart" style="max-width: 480px;">
    <h1>Account aanmaken</h1>
    <form method="post" action="<?= url('klant/registreren') ?>">
        <?= \GarageFlow\Support\Csrf::veld() ?>

        <label for="voornaam">Voornaam</label>
        <input type="text" id="voornaam" name="voornaam" value="<?= e($oud['voornaam'] ?? '') ?>">
        <?php if (isset($fouten['voornaam'])): ?><p class="veldfout"><?= e($fouten['voornaam']) ?></p><?php endif; ?>

        <label for="achternaam">Achternaam</label>
        <input type="text" id="achternaam" name="achternaam" value="<?= e($oud['achternaam'] ?? '') ?>">
        <?php if (isset($fouten['achternaam'])): ?><p class="veldfout"><?= e($fouten['achternaam']) ?></p><?php endif; ?>

        <label for="email">E-mailadres</label>
        <input type="email" id="email" name="email" value="<?= e($oud['email'] ?? '') ?>">
        <?php if (isset($fouten['email'])): ?><p class="veldfout"><?= e($fouten['email']) ?></p><?php endif; ?>

        <label for="telefoon">Telefoonnummer <span class="hint">(optioneel)</span></label>
        <input type="text" id="telefoon" name="telefoon" value="<?= e($oud['telefoon'] ?? '') ?>">

        <label for="wachtwoord">Wachtwoord <span class="hint">(minimaal 8 tekens)</span></label>
        <input type="password" id="wachtwoord" name="wachtwoord">
        <?php if (isset($fouten['wachtwoord'])): ?><p class="veldfout"><?= e($fouten['wachtwoord']) ?></p><?php endif; ?>

        <div class="knoprij">
            <button type="submit" class="knop">Account aanmaken</button>
        </div>
    </form>
    <p class="hint" style="margin-top:16px;">Al een account? <a href="<?= url('klant/inloggen') ?>">Inloggen</a>.</p>
</div>
