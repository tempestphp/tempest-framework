<?php

declare(strict_types=1);

namespace Tempest\View;

final class ViewConfig
{
    public function __construct(
        /** @var array<array-key, class-string<\Tempest\View\ViewComponent>> */
        public array $viewComponents = [],
    ) {
    }
}
