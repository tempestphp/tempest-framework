<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures;

use Tempest\Http\Session\Session;
use Tempest\View\ViewComponent;

final readonly class MyViewComponentWithInjection implements ViewComponent
{
    public static function getName(): string
    {
        return 'x-with-injection';
    }

    public function __construct(
        /** @phpstan-ignore-next-line */
        private Session $session,
    ) {
    }

    public function render(string $slot): string
    {
        return 'hi';
    }
}
