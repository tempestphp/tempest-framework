<?php

namespace Tempest\HttpApi;

final readonly class OffsetPagination implements Pagination
{
    public function __construct(
        public int $defaultLimit = 15,
    ) {}
}
