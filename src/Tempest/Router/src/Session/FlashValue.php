<?php

declare(strict_types=1);

namespace Tempest\Router\Session;

final readonly class FlashValue
{
    public function __construct(
        public mixed $value,
    ) {
    }
}
