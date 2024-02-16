<?php declare(strict_types=1);
/** @var \Tempest\View\GenericView $this */?>
<html lang="en">
<head>
    <title><?= $this->title ?? 'Home' ?></title>
</head>
<body>
<div class="defaultSlot"><?= $this->slot() ?></div>

<div class="namedSlot"><?= $this->slot('namedSlot') ?? '' ?></div>

<div class="namedSlot2"><?= $this->slot('namedSlot2') ?? '' ?></div>
</body>
</html>