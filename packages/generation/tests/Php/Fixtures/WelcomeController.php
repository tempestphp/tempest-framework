<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\Php\Fixtures;

use Tempest\Router\Get;
use Tempest\View\View;

use function Tempest\View\view;

final class WelcomeController
{
    #[Get(uri: '/welcome')]
    public function __invoke(): View
    {
        return view('welcome');
    }
}
