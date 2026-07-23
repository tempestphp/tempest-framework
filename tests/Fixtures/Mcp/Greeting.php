<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Mcp;

enum Greeting: string
{
    case HELLO = 'hello';
    case GOODBYE = 'goodbye';
}
