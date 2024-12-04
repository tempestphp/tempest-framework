<?php

declare(strict_types=1);

namespace Tempest\Router\Routing\Construction;

use Tempest\Router\Route;

final readonly class MarkedRoute
{
    public const string REGEX_MARK_TOKEN = 'MARK';

    public function __construct(
        public string $mark,
        public Route $route,
    ) {
    }
}
