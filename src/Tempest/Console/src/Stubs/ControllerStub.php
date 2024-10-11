<?php

declare(strict_types=1);

namespace Tempest\Console\Stubs;

use Tempest\Http\Get;
use function Tempest\view;
use Tempest\View\View;

final class ControllerStub
{
    #[Get(uri: '/welcome')]
    public function __invoke(): View
    {
        return view('welcome');
    }
}
