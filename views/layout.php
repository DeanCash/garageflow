<?php

use GarageFlow\Support\Auth;

/** @var string $titel */
/** @var string $inhoud */
$meldingen = haalMeldingen();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titel) ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
<header class="topbalk">
    <a class="logo" href="<?= url('home') ?>">Garage Sandvos<span>BMW-specialist</span></a>
    <nav>
        <?php if (Auth::isKlant()): ?>
            <a href="<?= url('klant/dashboard') ?>">Mijn overzicht</a>
            <a href="<?= url('klant/afspraak') ?>">Afspraak maken</a>
            <a href="<?= url('klant/uitloggen') ?>">Uitloggen</a>
        <?php elseif (Auth::isMedewerker()): ?>
            <a href="<?= url('beheer/planning') ?>">Dagplanning</a>
            <a href="<?= url('beheer/uitloggen') ?>">Uitloggen</a>
        <?php else: ?>
            <a href="<?= url('klant/inloggen') ?>">Inloggen</a>
            <a href="<?= url('beheer/inloggen') ?>">Medewerker</a>
        <?php endif; ?>
    </nav>
</header>

<main class="inhoud">
    <?php if (!empty($terug)): ?>
        <a class="teruglink" href="<?= url($terug['route']) ?>">&larr; <?= e($terug['label']) ?></a>
    <?php endif; ?>

    <?php foreach ($meldingen as $type => $tekst): ?>
        <div class="melding melding-<?= e($type) ?>"><?= e($tekst) ?></div>
    <?php endforeach; ?>

    <?= $inhoud ?>
</main>

<footer class="voetbalk">
    <p>GarageFlow &middot; afspraken- en werkordersysteem &middot; Garage Sandvos, Breda</p>
</footer>
</body>
</html>
