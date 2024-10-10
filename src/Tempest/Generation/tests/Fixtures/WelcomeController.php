<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\Fixtures;

use Tempest\Http\Get;
use function Tempest\view;
use Tempest\View\View;

final class WelcomeController
{
    #[Get(uri: '/welcome')]
    public function __invoke(): View
    {
        return view('welcome');
    }
}
