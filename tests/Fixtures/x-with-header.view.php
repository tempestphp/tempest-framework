<?php

use Tests\Tempest\Fixtures\Modules\Home\HomeController;

use function Tempest\Router\uri;

?>

{{ uri(HomeController::class) }}
