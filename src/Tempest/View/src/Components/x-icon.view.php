<?php

use Tempest\View\Components\Icon;

use function Tempest\get;

?>

{{ get(Icon::class)->render($name, $class ?? null) }}
