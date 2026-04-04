<?php

declare(strict_types=1);

namespace Tempest\Database\Builder\QueryBuilders;

final readonly class WhereRawScope implements QueryScope
{
    public function __construct(
        private string $statement,
        private mixed $binding,
    ) {}

    public function apply(SupportsWhereStatements $builder): void
    {
        $builder->whereRaw($this->statement, $this->binding);
    }
}
