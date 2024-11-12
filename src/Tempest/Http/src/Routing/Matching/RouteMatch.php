<?php

declare(strict_types=1);

namespace Tempest\Http\Routing\Matching;

use Tempest\Http\Routing\Construction\MarkedRoute;

final readonly class RouteMatch
{
    private function __construct(
        public ?string $mark,
        public array $matches,
    ) {
    }

    public static function match(array $params): self
    {
        return new self($params[MarkedRoute::REGEX_MARK_TOKEN], $params);
    }

    public static function notFound(): self
    {
        return new self(null, []);
    }

    public function isFound(): bool
    {
        return $this->mark !== null;
    }
}
