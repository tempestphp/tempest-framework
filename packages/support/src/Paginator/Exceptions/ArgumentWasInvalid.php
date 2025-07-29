<?php

namespace Tempest\Support\Paginator\Exceptions;

use InvalidArgumentException as PhpInvalidArgumentException;

final class ArgumentWasInvalid extends PhpInvalidArgumentException implements PaginationException
{
}
