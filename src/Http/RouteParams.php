<?php

declare(strict_types=1);

namespace Tempest\Http;

final readonly class RouteParams
{
    public function __construct(
        public array $params
    ) {
    }
}
