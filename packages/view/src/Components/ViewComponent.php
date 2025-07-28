<?php

declare(strict_types=1);

namespace Tempest\View\Components;

final class ViewComponent
{
    public function __construct(
        public readonly string $name,
        public readonly string $contents,
        public readonly string $file,
        public readonly bool $isVendorComponent,
    ) {}

    public bool $isProjectComponent {
        get => ! $this->isVendorComponent;
    }
}
