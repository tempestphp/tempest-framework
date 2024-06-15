<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures;

use Tempest\View\View;
use Tempest\View\ViewComponent;
use Tempest\View\ViewRenderer;

final readonly class MyViewComponent implements ViewComponent
{
    public static function getName(): string
    {
        return 'my';
    }

    public function __construct(
        private ?string $foo = null,
        private ?string $bar = null,
        private ?View $slot = null,
    ) {
    }

    public function render(ViewRenderer $renderer): string
    {
        $renderedSlot = $this->slot ? $renderer->render($this->slot) : '';

        if ($this->foo && $this->bar) {
            return "<div foo=\"{$this->foo}\" bar=\"{$this->bar}\">" . $renderedSlot . '</div>';
        }

        return '<div>' . $renderedSlot . '</div>';
    }
}
