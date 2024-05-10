<?php

declare(strict_types=1);

namespace Tempest\View;

final readonly class RenderedView
{
    public function __construct(
        public string $contents,
    ) {
    }
}
