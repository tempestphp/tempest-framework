<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Http;

/**
 * What a throttling attribute was declared on. A controller and its routes may both declare limits,
 * and both apply. The scope keeps their counters apart.
 */
enum ThrottleScope: string
{
    /**
     * Limits declared on the controller apply to it as a whole: one allowance covers every route
     * it exposes.
     */
    case CONTROLLER = 'controller';

    /**
     * Limits declared on a route apply to that route alone.
     */
    case ROUTE = 'route';
}
