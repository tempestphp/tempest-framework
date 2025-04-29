<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\Fixtures;

use Tempest\Router\Get;
use Tempest\View\View;

use function Tempest\view;

final class WelcomeController
{
    #[Get(uri: '/welcome')]
    public function __invoke(): View
    {
        return view('welcome');
    }
}
