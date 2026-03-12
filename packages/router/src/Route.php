<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Http\Method;

interface Route
{
    public Method $method {
        get;
        set;
    }

    public string $uri {
        get;
        set;
    }

    /** @var class-string<HttpMiddleware>[]  */
    public array $middleware {
        get;
        set;
    }

    /** @var class-string<HttpMiddleware>[]  */
    public array $without {
        get;
        set;
    }
}
