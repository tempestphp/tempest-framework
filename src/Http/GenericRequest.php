<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Interfaces\Request;

final class GenericRequest implements Request
{
    use BaseRequest;
}
