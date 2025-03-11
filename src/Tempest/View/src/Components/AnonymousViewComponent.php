<?php

declare(strict_types=1);

namespace Tempest\View\Components;

use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;

final readonly class AnonymousViewComponent implements ViewComponent
{
    public function __construct(
        public string $contents,
        public string $file,
    ) {
    }

    public static function getName(): string
    {
        return 'x-component';
    }

    public function compile(ViewComponentElement $element): string
    {
        return $this->contents;
    }
}
