<?php

namespace Tempest\Database\Builder\QueryBuilders;

use ArrayAccess;
use BackedEnum;
use Countable;
use Tempest\Database\Builder\WhereOperator;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\Support\Str;
use UnitEnum;

/**
 * @template TModel of object
 * @phpstan-require-implements \Tempest\Database\Builder\QueryBuilders\SupportsWhereStatements
 *
 * Shared methods for building WHERE conditions and convenience WHERE methods.
 */
trait HasConvenientWhereMethods
{
    /**
     * Builds a SQL condition with bindings for a given field, operator, and value.
     *
     * @return array{ sql: string, bindings: array<mixed> }
     */
    protected function buildCondition(string $fieldDefinition, WhereOperator $operator, mixed $value): array
    {
        $sql = $fieldDefinition;
        $bindings = [];

        switch ($operator) {
            case WhereOperator::IS_NULL:
                $sql .= ' IS NULL';
                break;

            case WhereOperator::IS_NOT_NULL:
                $sql .= ' IS NOT NULL';
                break;

            case WhereOperator::IN:
            case WhereOperator::NOT_IN:
                if (is_a($value, UnitEnum::class, allow_string: true)) {
                    $value = $value::cases();
                }

                if (! is_array($value)) {
                    throw new \InvalidArgumentException("{$operator->value} operator requires an array of values");
                }

                $value = array_map(fn (mixed $value) => match (true) {
                    $value instanceof BackedEnum => $value->value,
                    $value instanceof UnitEnum => $value->name,
                    $value instanceof ArrayAccess => (array) $value,
                    default => $value,
                }, $value);

                $placeholders = str_repeat('?,', times: count($value) - 1) . '?';
                $sql .= " {$operator->value} ({$placeholders})";
                $bindings = array_values($value);
                break;

            case WhereOperator::BETWEEN:
            case WhereOperator::NOT_BETWEEN:
                if (! is_array($value) || count($value) !== 2) {
                    throw new \InvalidArgumentException("{$operator->value} operator requires an array with exactly 2 values");
                }

                $sql .= " {$operator->value} ? AND ?";
                $bindings = array_map(
                    fn (DateTimeInterface|string|float|int|Countable $value) => match (true) {
                        $value instanceof Countable => count($value),
                        default => $value,
                    },
                    $value,
                );
                break;

            default:
                if ($operator->requiresValue() && $value === null) {
                    throw new \InvalidArgumentException("{$operator->value} operator requires a value");
                }

                if ($operator->requiresValue()) {
                    $sql .= " {$operator->value} ?";
                    $bindings[] = $value;
                } else {
                    $sql .= " {$operator->value}";
                }
                break;
        }

        return [
            'sql' => $sql,
            'bindings' => $bindings,
        ];
    }

    private function looksLikeWhereRawStatement(string $statement, array $bindings): bool
    {
        if (count($bindings) === 2 && $bindings[1] instanceof WhereOperator) {
            return false;
        }

        if (! Str\contains($statement, [' ', ...array_map(fn (WhereOperator $op) => $op->value, WhereOperator::cases())])) {
            return false;
        }

        return true;
    }

    /**
     * Adds a `WHERE IN` condition.
     *
     * @param class-string<UnitEnum>|UnitEnum|array<UnitEnum|mixed> $values
     *
     * @return self<TModel>
     */
    public function whereIn(string $field, string|UnitEnum|array|ArrayAccess $values): self
    {
        return $this->whereField($field, $values, WhereOperator::IN);
    }

    /**
     * Adds a `WHERE NOT IN` condition.
     *
     * @param class-string<UnitEnum>|UnitEnum|array<UnitEnum|mixed> $values
     *
     * @return self<TModel>
     */
    public function whereNotIn(string $field, string|UnitEnum|array|ArrayAccess $values): self
    {
        return $this->whereField($field, $values, WhereOperator::NOT_IN);
    }

    /**
     * Adds a `WHERE BETWEEN` condition.
     *
     * @return self<TModel>
     */
    public function whereBetween(string $field, DateTimeInterface|string|float|int|Countable $min, DateTimeInterface|string|float|int|Countable $max): self
    {
        return $this->whereField($field, [$min, $max], WhereOperator::BETWEEN);
    }

    /**
     * Adds a `WHERE NOT BETWEEN` condition.
     *
     * @return self<TModel>
     */
    public function whereNotBetween(string $field, DateTimeInterface|string|float|int|Countable $min, DateTimeInterface|string|float|int|Countable $max): self
    {
        return $this->whereField($field, [$min, $max], WhereOperator::NOT_BETWEEN);
    }

    /**
     * Adds a `WHERE IS NULL` condition.
     *
     * @return self<TModel>
     */
    public function whereNull(string $field): self
    {
        return $this->whereField($field, null, WhereOperator::IS_NULL);
    }

    /**
     * Adds a `WHERE IS NOT NULL` condition.
     *
     * @return self<TModel>
     */
    public function whereNotNull(string $field): self
    {
        return $this->whereField($field, null, WhereOperator::IS_NOT_NULL);
    }

    /**
     * Adds a `WHERE NOT` condition (shorthand for != operator).
     *
     * @return self<TModel>
     */
    public function whereNot(string $field, mixed $value): self
    {
        return $this->whereField($field, $value, WhereOperator::NOT_EQUALS);
    }

    /**
     * Adds a `WHERE LIKE` condition.
     *
     * @return self<TModel>
     */
    public function whereLike(string $field, string $value): self
    {
        return $this->whereField($field, $value, WhereOperator::LIKE);
    }

    /**
     * Adds a `WHERE NOT LIKE` condition.
     *
     * @return self<TModel>
     */
    public function whereNotLike(string $field, string $value): self
    {
        return $this->whereField($field, $value, WhereOperator::NOT_LIKE);
    }

    /**
     * Adds an `OR WHERE IN` condition.
     *
     * @param class-string<UnitEnum>|UnitEnum|array<UnitEnum|mixed> $values
     *
     * @return self<TModel>
     */
    public function orWhereIn(string $field, string|UnitEnum|array|ArrayAccess $values): self
    {
        return $this->orWhere($field, $values, WhereOperator::IN);
    }

    /**
     * Adds an `OR WHERE NOT IN` condition.
     *
     * @param class-string<UnitEnum>|UnitEnum|array<UnitEnum|mixed> $values
     *
     * @return self<TModel>
     */
    public function orWhereNotIn(string $field, string|UnitEnum|array|ArrayAccess $values): self
    {
        return $this->orWhere($field, $values, WhereOperator::NOT_IN);
    }

    /**
     * Adds an `OR WHERE BETWEEN` condition.
     *
     * @return self<TModel>
     */
    public function orWhereBetween(string $field, DateTimeInterface|string|float|int|Countable $min, DateTimeInterface|string|float|int|Countable $max): self
    {
        return $this->orWhere($field, [$min, $max], WhereOperator::BETWEEN);
    }

    /**
     * Adds an `OR WHERE NOT BETWEEN` condition.
     *
     * @return self<TModel>
     */
    public function orWhereNotBetween(string $field, DateTimeInterface|string|float|int|Countable $min, DateTimeInterface|string|float|int|Countable $max): self
    {
        return $this->orWhere($field, [$min, $max], WhereOperator::NOT_BETWEEN);
    }

    /**
     * Adds an `OR WHERE IS NULL` condition.
     *
     * @return self<TModel>
     */
    public function orWhereNull(string $field): self
    {
        return $this->orWhere($field, null, WhereOperator::IS_NULL);
    }

    /**
     * Adds an `OR WHERE IS NOT NULL` condition.
     *
     * @return self<TModel>
     */
    public function orWhereNotNull(string $field): self
    {
        return $this->orWhere($field, null, WhereOperator::IS_NOT_NULL);
    }

    /**
     * Adds an `OR WHERE NOT` condition (shorthand for != operator).
     *
     * @return self<TModel>
     */
    public function orWhereNot(string $field, mixed $value): self
    {
        return $this->orWhere($field, $value, WhereOperator::NOT_EQUALS);
    }

    /**
     * Adds an `OR WHERE LIKE` condition.
     *
     * @return self<TModel>
     */
    public function orWhereLike(string $field, string $value): self
    {
        return $this->orWhere($field, $value, WhereOperator::LIKE);
    }

    /**
     * Adds an `OR WHERE NOT LIKE` condition.
     *
     * @return self<TModel>
     */
    public function orWhereNotLike(string $field, string $value): self
    {
        return $this->orWhere($field, $value, WhereOperator::NOT_LIKE);
    }

    /**
     * Adds a `WHERE` condition for records from today.
     *
     * @return self<TModel>
     */
    public function whereToday(string $field): self
    {
        $today = DateTime::now();

        return $this->whereBetween($field, $today->startOfDay(), $today->endOfDay());
    }

    /**
     * Adds a `WHERE` condition for records from yesterday.
     *
     * @return self<TModel>
     */
    public function whereYesterday(string $field): self
    {
        $yesterday = DateTime::now()->minusDay();

        return $this->whereBetween($field, $yesterday->startOfDay(), $yesterday->endOfDay());
    }

    /**
     * Adds a `WHERE` condition for records from this week.
     *
     * @return self<TModel>
     */
    public function whereThisWeek(string $field): self
    {
        $today = DateTime::now();

        return $this->whereBetween($field, $today->startOfWeek(), $today->endOfWeek());
    }

    /**
     * Adds a `WHERE` condition for records from last week.
     *
     * @return self<TModel>
     */
    public function whereLastWeek(string $field): self
    {
        $lastWeek = DateTime::now()->minusDays(7);

        return $this->whereBetween($field, $lastWeek->startOfWeek(), $lastWeek->endOfWeek());
    }

    /**
     * Adds a `WHERE` condition for records from this month.
     *
     * @return self<TModel>
     */
    public function whereThisMonth(string $field): self
    {
        $today = DateTime::now();

        return $this->whereBetween($field, $today->startOfMonth(), $today->endOfMonth());
    }

    /**
     * Adds a `WHERE` condition for records from last month.
     *
     * @return self<TModel>
     */
    public function whereLastMonth(string $field): self
    {
        $lastMonth = DateTime::now()->minusMonths(1);

        return $this->whereBetween($field, $lastMonth->startOfMonth(), $lastMonth->endOfMonth());
    }

    /**
     * Adds a `WHERE` condition for records from this year.
     *
     * @return self<TModel>
     */
    public function whereThisYear(string $field): self
    {
        $today = DateTime::now();

        return $this->whereBetween($field, $today->startOfYear(), $today->endOfYear());
    }

    /**
     * Adds a `WHERE` condition for records from last year.
     *
     * @return self<TModel>
     */
    public function whereLastYear(string $field): self
    {
        $lastYear = DateTime::now()->minusYears(1);

        return $this->whereBetween($field, $lastYear->startOfYear(), $lastYear->endOfYear());
    }

    /**
     * Adds a `WHERE` condition for records which specified field is after a specific date.
     *
     * @return self<TModel>
     */
    public function whereAfter(string $field, DateTimeInterface|string $date): self
    {
        return $this->whereField($field, DateTime::parse($date), WhereOperator::GREATER_THAN);
    }

    /**
     * Adds a `WHERE` condition for records which specified field is before a specific date.
     *
     * @return self<TModel>
     */
    public function whereBefore(string $field, DateTimeInterface|string $date): self
    {
        return $this->whereField($field, DateTime::parse($date), WhereOperator::LESS_THAN);
    }

    /**
     * Adds an `OR WHERE` condition for records from today.
     *
     * @return self<TModel>
     */
    public function orWhereToday(string $field): self
    {
        $today = DateTime::now();
        return $this->orWhereBetween($field, $today->startOfDay(), $today->endOfDay());
    }

    /**
     * Adds an `OR WHERE` condition for records from yesterday.
     *
     * @return self<TModel>
     */
    public function orWhereYesterday(string $field): self
    {
        $yesterday = DateTime::now()->minusDay();

        return $this->orWhereBetween($field, $yesterday->startOfDay(), $yesterday->endOfDay());
    }

    /**
     * Adds an `OR WHERE` condition for records from this week.
     *
     * @return self<TModel>
     */
    public function orWhereThisWeek(string $field): self
    {
        $today = DateTime::now();

        return $this->orWhereBetween($field, $today->startOfWeek(), $today->endOfWeek());
    }

    /**
     * Adds an `OR WHERE` condition for records from this month.
     *
     * @return self<TModel>
     */
    public function orWhereThisMonth(string $field): self
    {
        $today = DateTime::now();

        return $this->orWhereBetween($field, $today->startOfMonth(), $today->endOfMonth());
    }

    /**
     * Adds an `OR WHERE` condition for records from this year.
     *
     * @return self<TModel>
     */
    public function orWhereThisYear(string $field): self
    {
        $today = DateTime::now();

        return $this->orWhereBetween($field, $today->startOfYear(), $today->endOfYear());
    }

    /**
     * Adds an `OR WHERE` condition for records created after a specific date.
     *
     * @return self<TModel>
     */
    public function orWhereAfter(string $field, DateTimeInterface|string $date): self
    {
        return $this->orWhere($field, DateTime::parse($date), WhereOperator::GREATER_THAN);
    }

    /**
     * Adds an `OR WHERE` condition for records created before a specific date.
     *
     * @return self<TModel>
     */
    public function orWhereBefore(string $field, DateTimeInterface|string $date): self
    {
        return $this->orWhere($field, DateTime::parse($date), WhereOperator::LESS_THAN);
    }

    /**
     * Abstract method that must be implemented by classes using this trait.
     * Should add a basic WHERE condition.
     *
     * @return self<TModel>
     */
    abstract public function whereField(string $field, mixed $value, string|WhereOperator $operator = WhereOperator::EQUALS): self;

    /**
     * Abstract method that must be implemented by classes using this trait.
     * Should add an OR WHERE condition.
     *
     * @return self<TModel>
     */
    abstract public function orWhere(string $field, mixed $value, WhereOperator $operator = WhereOperator::EQUALS): self;
}
