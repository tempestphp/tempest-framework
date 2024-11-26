<?php

declare(strict_types=1);

namespace Tempest\Router\Stubs;

use Tempest\Router\Get;
use function Tempest\view;
use Tempest\View\View;

final class ControllerStub
{
    #[Get(uri: '/dummy-path')]
    public function __invoke(): View
    {
        return view('dummy-view');
    }
}
