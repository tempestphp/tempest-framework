<?php

namespace Tempest\Interfaces;

use Closure;
use Tempest\ORM\FieldName;
use Tempest\ORM\TableName;

interface Model
{
    public static function table(): TableName;

    public static function field(string $field): FieldName;

    /**
     * @return Query<static>
     */
    public static function query(): Query;

    public static function create(...$params): static;

    public function load(Closure $load): self;
}
