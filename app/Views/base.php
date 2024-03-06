<?php declare(strict_types=1);
use Tempest\View\GenericView;

/** @var GenericView $this */?>

<html lang="en">
<head>
    <title><?= $this->title ?? 'Home' ?></title>
</head>
<body>
<?= $this->slot() ?? '' ?>
</body>
</html>