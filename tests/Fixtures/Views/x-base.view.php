<?php declare(strict_types=1);

use Tempest\View\GenericView;

/**
 * @var GenericView $this
 * @var string|null $title
 */
?>

<html lang="en">
<head>
    <title><?= $title ?? 'Home' ?></title>
</head>
<body>
<x-slot/>
</body>
</html>
