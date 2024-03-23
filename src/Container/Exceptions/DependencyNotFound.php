<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class DependencyNotFound extends Exception implements NotFoundExceptionInterface
{
}
