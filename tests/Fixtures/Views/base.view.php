<?php declare(strict_types=1);

use Tempest\View\GenericView;

/** @var GenericView $this */?>

<x-component name="x-base">
    <html lang="en">
    <head>
        <title><?= $this->title ?? 'Home' ?></title>
    </head>
    <body>
    <x-slot />
    </body>
    </html>
</x-component>