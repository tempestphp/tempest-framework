<?php

declare(strict_types=1);

namespace Tempest\Http;

enum Status
{
    case HTTP_200;
    case HTTP_301;
    case HTTP_302;
    case HTTP_400;
    case HTTP_404;
    case HTTP_500;
}
