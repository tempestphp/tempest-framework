<?php

declare(strict_types=1);

namespace Tempest\Http\Routing\Matching;

use Tempest\Http\Routing\Construction\MarkedRoute;

final readonly class RouteMatch
{
    private function __construct(
        public bool $isFound,
        public ?string $mark,
        public array $matches,
    ) {
    }

    public static function match(array $params): self
    {
        return new self(true, $params[MarkedRoute::REGEX_MARK_TOKEN], $params);
    }

    public static function notFound(): self
    {
        return new self(false, null, []);
    }
}
