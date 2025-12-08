<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Attribute;
use BackedEnum;
use UnitEnum;

/**
 * Defines the context in which a mapper, serializer, or caster operates.
 *
 * Contexts allow different implementations for different scenarios:
 * - 'default' - Standard mapping/serialization
 * - 'database.postgresql' - PostgreSQL-specific serialization
 * - 'database.mysql' - MySQL-specific serialization
 * - 'api' - API response serialization
 * - Custom contexts as needed
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Context
{
    public const string DEFAULT = 'default';
    public const string DATABASE_POSTGRESQL = 'database:postgresql';
    public const string DATABASE_MYSQL = 'database:mysql';
    public const string DATABASE_SQLITE = 'database:sqlite';
    public const string API = 'api';

    public function __construct(
        public BackedEnum|UnitEnum|string $name = self::DEFAULT,
    ) {}

    public function __toString(): string
    {
        if ($this->name instanceof BackedEnum) {
            return $this->name->value;
        }

        if ($this->name instanceof UnitEnum) {
            return $this->name->name;
        }

        return $this->name;
    }
}
