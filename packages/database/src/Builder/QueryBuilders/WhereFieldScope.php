<?php

declare(strict_types=1);

namespace Tempest\Database\Builder\QueryBuilders;

final readonly class WhereFieldScope implements QueryScope
{
    public function __construct(
        private string $field,
        private mixed $value,
    ) {}

    public function apply(SupportsWhereStatements $builder): void
    {
        $builder->whereField(field: $this->field, value: $this->value);
    }
}
