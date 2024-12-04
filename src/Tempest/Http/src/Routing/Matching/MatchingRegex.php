<?php

declare(strict_types=1);

namespace Tempest\Http\Routing\Matching;

use RuntimeException;
use Tempest\Http\Routing\Construction\MarkedRoute;

final readonly class MatchingRegex
{
    /**
     * @param string[] $patterns
     */
    public function __construct(
        public array $patterns,
    ) {
    }

    public function match(string $uri): ?RouteMatch
    {
        foreach ($this->patterns as $pattern) {
            $matchResult = preg_match($pattern, $uri, $matches);

            if ($matchResult === false) {
                throw new RuntimeException('Failed to use matching regex. Got error ' . preg_last_error());
            }

            if (! $matchResult) {
                continue;
            }

            if (! array_key_exists(MarkedRoute::REGEX_MARK_TOKEN, $matches)) {
                continue;
            }

            return RouteMatch::match($matches);
        }

        return null;
    }
}
