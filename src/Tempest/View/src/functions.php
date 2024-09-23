<?php


declare(strict_types=1);

namespace Tempest {

    use Tempest\View\GenericView;
    use Tempest\View\View;

    function view(string $path, mixed ...$params): View
    {
        return (new GenericView($path))->data(...$params);
    }
}
