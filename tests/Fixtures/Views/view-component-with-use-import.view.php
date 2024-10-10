<?php

use function Tempest\uri;
use Tests\Tempest\Fixtures\Modules\Home\HomeController;

?>

<x-component name="x-view-component-with-use-import">
    <?= uri(HomeController::class) ?>
</x-component>