<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

final readonly class FlashValue
{
    public function __construct(
        public mixed $value,
    ) {
    }
}
