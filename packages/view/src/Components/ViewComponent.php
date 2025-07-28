<?php

declare(strict_types=1);

namespace Tempest\View\Components;

final readonly class ViewComponent
{
    public function __construct(
        public string $name,
        public string $contents,
        public string $file,
        public bool $isVendorComponent,
    ) {}
}
