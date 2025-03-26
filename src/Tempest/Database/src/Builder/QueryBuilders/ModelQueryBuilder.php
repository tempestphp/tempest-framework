<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Mapper\SerializerFactory;

use function Tempest\get;

final readonly class ModelQueryBuilder
{
    public function __construct(
        private string|object $model,
    ) {}

    public function select(): SelectModelQueryBuilder
    {
        return new SelectModelQueryBuilder($this->model);
    }

    public function create(): CreateModelQueryBuilder
    {
        return new CreateModelQueryBuilder($this->model, get(SerializerFactory::class));
    }

    public function update(): UpdateModelQueryBuilder
    {
        return new UpdateModelQueryBuilder($this->model, get(SerializerFactory::class));
    }

    public function delete(): DeleteModelQueryBuilder
    {
        return new DeleteModelQueryBuilder($this->model);
    }
}
