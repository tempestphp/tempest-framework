<?php

namespace Tempest\HttpApi;

final readonly class CursorPagination implements Pagination
{
    public function __construct(
        public int $defaultLimit = 15,
    ) {}
}
