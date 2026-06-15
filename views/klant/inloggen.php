<?php
/** @var array<string,string> $fouten */
/** @var array<string,mixed> $oud */
$fouten = $fouten ?? [];
$oud = $oud ?? [];
?>
<div class="kaart" style="max-width: 420px;">
    <h1>Inloggen</h1>
    <form method="post" action="<?= url('klant/inloggen') ?>">
        <?= \GarageFlow\Support\Csrf::veld() ?>

        <label for="email">E-mailadres</label>
        <input type="email" id="email" name="email" value="<?= e($oud['email'] ?? '') ?>">
        <?php if (isset($fouten['email'])): ?><p class="veldfout"><?= e($fouten['email']) ?></p><?php endif; ?>

        <label for="wachtwoord">Wachtwoord</label>
        <input type="password" id="wachtwoord" name="wachtwoord">

        <div class="knoprij">
            <button type="submit" class="knop">Inloggen</button>
        </div>
    </form>
    <p class="hint" style="margin-top:16px;">Nog geen account? <a href="<?= url('klant/registreren') ?>">Registreren</a>.</p>
</div>
