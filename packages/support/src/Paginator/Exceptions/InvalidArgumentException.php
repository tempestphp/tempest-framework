<?php

namespace Tempest\Support\Paginator\Exceptions;

use InvalidArgumentException as PhpInvalidArgumentException;

final class InvalidArgumentException extends PhpInvalidArgumentException implements PaginationException
{
}
