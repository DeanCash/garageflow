<?php
/** @var array<string,string> $fouten */
/** @var array<string,mixed> $oud */
$fouten = $fouten ?? [];
$oud = $oud ?? [];
?>
<div class="kaart" style="max-width: 420px;">
    <h1>Werkplaatsbeheer</h1>
    <p class="hint">Inloggen voor serviceadviseurs en monteurs.</p>
    <form method="post" action="<?= url('beheer/inloggen') ?>">
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
</div>
