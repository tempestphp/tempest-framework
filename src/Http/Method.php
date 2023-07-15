<?php

declare(strict_types=1);

namespace Tempest\Http;

enum Method: string
{
    case GET = 'GET';
    case POST = 'POST';
}
