<?php /** @var string $melding */ ?>
<div class="kaart">
    <h1>Er ging iets mis</h1>
    <p><?= e($melding ?? 'Onbekende fout.') ?></p>
    <a class="knop" href="<?= url('home') ?>">Terug naar start</a>
</div>
