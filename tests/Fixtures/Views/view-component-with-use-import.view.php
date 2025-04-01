<?php

use Tests\Tempest\Fixtures\Modules\Home\HomeController;

use function Tempest\uri;

?>

<x-component name="x-view-component-with-use-import">
because this;
    <?= uri(HomeController::class) ?>
</x-component>
