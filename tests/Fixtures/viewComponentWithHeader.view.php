<?php

use Tests\Tempest\Fixtures\Modules\Home\HomeController;
use function Tempest\uri;

?>

<x-component name="x-with-header">
    <?= uri(HomeController::class) ?>
</x-component>