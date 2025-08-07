<?php

namespace Tempest\Database\Builder;

enum WhereOperator: string
{
    case EQUALS = '=';
    case NOT_EQUALS = '!=';
    case NOT_EQUALS_ALT = '<>';
    case GREATER_THAN = '>';
    case GREATER_THAN_OR_EQUAL = '>=';
    case LESS_THAN = '<';
    case LESS_THAN_OR_EQUAL = '<=';
    case LIKE = 'LIKE';
    case NOT_LIKE = 'NOT LIKE';
    case ILIKE = 'ILIKE';
    case NOT_ILIKE = 'NOT ILIKE';
    case IN = 'IN';
    case NOT_IN = 'NOT IN';
    case IS_NULL = 'IS NULL';
    case IS_NOT_NULL = 'IS NOT NULL';
    case BETWEEN = 'BETWEEN';
    case NOT_BETWEEN = 'NOT BETWEEN';
    case EXISTS = 'EXISTS';
    case NOT_EXISTS = 'NOT EXISTS';
    case REGEXP = 'REGEXP';
    case NOT_REGEXP = 'NOT REGEXP';
    case RLIKE = 'RLIKE';
    case NOT_RLIKE = 'NOT RLIKE';

    public static function fromOperator(WhereOperator|string $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::from(strtoupper($value));
    }

    public function requiresValue(): bool
    {
        return ! in_array($this, [self::IS_NULL, self::IS_NOT_NULL], strict: true);
    }

    public function requiresMultipleValues(): bool
    {
        return in_array($this, [self::IN, self::NOT_IN, self::BETWEEN, self::NOT_BETWEEN], strict: true);
    }

    public function supportsArray(): bool
    {
        return in_array($this, [self::IN, self::NOT_IN], strict: true);
    }
}
