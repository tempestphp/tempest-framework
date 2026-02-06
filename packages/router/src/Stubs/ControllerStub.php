<?php

declare(strict_types=1);

namespace Tempest\Router\Stubs;

use Tempest\Discovery\SkipDiscovery;
use Tempest\Router\Get;
use Tempest\View\View;

use function Tempest\View\view;

#[SkipDiscovery]
final class ControllerStub
{
    #[Get(uri: '/dummy-path')]
    public function __invoke(): View
    {
        return view('dummy-view');
    }
}
