<?php

declare(strict_types=1);

namespace Tempest\Http\Routing\Construction;

use Tempest\Http\Route;

final readonly class MarkedRoute
{
    public const string REGEX_MARK_TOKEN = 'MARK';

    public function __construct(
        public string $mark,
        public Route $route,
    ) {
    }
}
