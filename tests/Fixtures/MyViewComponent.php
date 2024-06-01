<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures;

use Tempest\View\ViewComponent;

final readonly class MyViewComponent implements ViewComponent
{
    public static function getName(): string
    {
        return 'my';
    }

    public function __construct(
        private ?string $foo = null,
        private ?string $bar = null,
    ) {
    }

    public function render(string $slot): string
    {
        if ($this->foo && $this->bar) {
            return "<div foo=\"{$this->foo}\" bar=\"{$this->bar}\">" . $slot . '</div>';
        }

        return '<div>' . $slot . '</div>';
    }
}
