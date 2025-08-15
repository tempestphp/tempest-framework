<?php

declare(strict_types=1);

namespace Tempest\Validation\Rules;

use Attribute;
use InvalidArgumentException;
use Tempest\Database\PrimaryKey;
use Tempest\Validation\HasTranslationVariables;
use Tempest\Validation\Rule;

use function Tempest\Database\inspect;
use function Tempest\Database\query;

/**
 * Ensures that for the given model, the primary key value associated to this rule exists in the database.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Exists implements Rule, HasTranslationVariables
{
    /**
     * @param class-string|non-empty-string $table
     */
    public function __construct(
        private string $table,
        private ?string $column = null,
    ) {
        if (! class_exists($table) && $column === null) {
            throw new InvalidArgumentException('A column must be specified when the table is not a model class.');
        }
    }

    public function isValid(mixed $value): bool
    {
        if (! is_object($value) && ! is_numeric($value) && ! is_string($value)) {
            return false;
        }

        if (is_float($value)) {
            return false;
        }

        $column = match (is_null($this->column)) {
            false => $this->column,
            true => match ($key = inspect($this->table)->getPrimaryKey()) {
                null => throw new InvalidArgumentException("Model `{$this->table}` does not have a primary key, and a column was not specified."),
                default => $key,
            },
        };

        return query($this->table)
            ->count()
            ->whereField($column, $value)
            ->execute() > 0;
    }

    public function getTranslationVariables(): array
    {
        return [
            'model' => $this->table,
        ];
    }
}
