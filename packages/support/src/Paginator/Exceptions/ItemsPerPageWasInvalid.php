<?php

namespace Tempest\Support\Paginator\Exceptions;

use Exception;

final class ItemsPerPageWasInvalid extends Exception implements PaginationException
{
    public function __construct(int $itemsPerPage)
    {
        $maximum = PHP_INT_MAX - 1;

        parent::__construct("Items per page should be between 1 and {$maximum}. Instead got {$itemsPerPage}");
    }
}
