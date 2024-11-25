<?php

declare(strict_types=1);

namespace Tempest\Http\Stubs;

use Tempest\Http\Get;
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
