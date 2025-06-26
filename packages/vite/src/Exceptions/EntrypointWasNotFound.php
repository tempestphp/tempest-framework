<?php

declare(strict_types=1);

namespace Tempest\Vite\Exceptions;

use Exception;

abstract class EntrypointWasNotFound extends Exception implements ViteException
{
}
