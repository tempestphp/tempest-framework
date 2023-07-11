<?php

namespace Tempest\View;

final readonly class RenderedView
{
    public function __construct(
        public string $contents,
    ) {
    }
}
