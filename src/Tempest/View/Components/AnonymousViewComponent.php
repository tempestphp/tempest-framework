<?php

declare(strict_types=1);

namespace Tempest\View\Components;

use Tempest\View\Elements\GenericElement;
use Tempest\View\ViewComponent;
use Tempest\View\ViewRenderer;

final readonly class AnonymousViewComponent implements ViewComponent
{
    public function __construct(
        private string $contents,
        private string $file,
    ) {
    }

    public static function getName(): string
    {
        return 'x-component';
    }

    public function render(GenericElement $element, ViewRenderer $renderer): string
    {
        return $this->contents;
    }

    public function getPath(): string
    {
        return $this->file;
    }
}
