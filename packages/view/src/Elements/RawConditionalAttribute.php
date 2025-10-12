<?php

namespace Tempest\View\Elements;

final readonly class RawConditionalAttribute
{
    public function __construct(
        private string $name,
        private string $value,
    ) {}
}
