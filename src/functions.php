<?php

use Tempest\Interfaces\View;
use Tempest\View\GenericView;

function path(string ...$parts): string
{
    $path = implode('/', $parts);

    return str_replace(
        ['//', '\\', '\\\\'],
        ['/', '/', '/'],
        $path,
    );
}

function view(string $path): View
{
    return GenericView::new($path);
}