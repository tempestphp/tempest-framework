<?php

declare(strict_types=1);

namespace Tempest\Router\Routing\Matching;

use Tempest\Router\Routing\Construction\MarkedRoute;

final readonly class RouteMatch
{
    private function __construct(
        public string $mark,
        public array $matches,
    ) {}

    public static function match(array $params): self
    {
        return new self($params[MarkedRoute::REGEX_MARK_TOKEN], $params);
    }
}
