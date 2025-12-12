<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Relation;

/**
 * @template TModel of object
 */
interface SupportsRelations
{
    /**
     * The current relation names requested for eager loading.
     *
     * @return array<string>
     */
    public array $relations {
        get;
    }

    /**
     * Gets all resolved relations with their join statements.
     *
     * @return Relation[]
     */
    public function getResolvedRelations(): array;
}
