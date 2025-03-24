<?php declare(strict_types=1);

use Tests\Tempest\Fixtures\Modules\Home\HomeView;

/** @var HomeView $this */
?>

<x-base>
    Hello, <?= $this->name ?>
</x-base>
