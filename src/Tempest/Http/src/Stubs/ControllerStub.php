<?php

declare(strict_types=1);

namespace Tempest\Http\Stubs;

use Tempest\Http\Get;
use Tempest\View\View;
use function Tempest\view;

final class ControllerStub
{
    #[Get(uri: '/dummy-path')]
    public function __invoke(): View
    {
        return view('dummy-view');
    }
}
