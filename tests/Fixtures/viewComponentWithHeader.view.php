<?php

use function Tempest\uri;
use Tests\Tempest\Fixtures\Modules\Home\HomeController;

?>

<x-component name="x-with-header">
    <?= uri(HomeController::class) ?>
</x-component>