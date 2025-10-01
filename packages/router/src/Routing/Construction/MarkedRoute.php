<?php

declare(strict_types=1);

namespace Tempest\Router\Routing\Construction;

final readonly class MarkedRoute
{
    // @mago-expect lint:no-literal-password
    public const string REGEX_MARK_TOKEN = 'MARK';

    public function __construct(
        public string $mark,
        public DiscoveredRoute $route,
    ) {}
}
