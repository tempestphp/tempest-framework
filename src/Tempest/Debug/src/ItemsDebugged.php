<?php

declare(strict_types=1);

namespace Tempest\Debug;

final class ItemsDebugged
{
    public function __construct(
        public array $items,
    ) {
    }
}
