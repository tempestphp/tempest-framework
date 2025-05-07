<?php

declare(strict_types=1);

namespace Tempest\Router\Exceptions;

use Exception;

final class NotFoundException extends Exception implements RouterException
{
}
