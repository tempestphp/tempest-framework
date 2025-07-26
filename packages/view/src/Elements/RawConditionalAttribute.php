<?php

namespace Tempest\View\Elements;

use Tempest\View\Attributes\ExpressionAttribute;
use function Tempest\Support\str;

final readonly class RawConditionalAttribute
{
    public function __construct(
        private string $name,
        private string $value,
    ) {}
}
