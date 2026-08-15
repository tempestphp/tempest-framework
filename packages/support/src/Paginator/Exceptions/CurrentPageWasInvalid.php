<?php

namespace Tempest\Support\Paginator\Exceptions;

use Exception;

final class CurrentPageWasInvalid extends Exception implements PaginationException
{
    public function __construct(int $currentPage, int $itemsPerPage, int $maximumCurrentPage)
    {
        parent::__construct("Current page should be between 1 and {$maximumCurrentPage} for {$itemsPerPage} items per page. Instead got {$currentPage}");
    }
}
