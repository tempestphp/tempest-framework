<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use Tempest\Generation\Php\Exceptions\ValueWasNotExportable;
use UnitEnum;

/**
 * @internal
 */
final class ValueExporter
{
    public static function export(mixed $value): Expr
    {
        return match (true) {
            $value instanceof Literal => SourceParser::parseExpression($value->code),
            $value instanceof UnitEnum => new Expr\ClassConstFetch(new Name\FullyQualified($value::class), $value->name),
            $value === null => new Expr\ConstFetch(new Name('null')),
            is_bool($value) => new Expr\ConstFetch(new Name($value ? 'true' : 'false')),
            is_int($value) => self::exportInteger($value),
            is_float($value) => new Scalar\Float_($value),
            is_string($value) => new Scalar\String_($value),
            is_array($value) => self::exportArray($value),
            default => throw new ValueWasNotExportable($value),
        };
    }

    /**
     * @param array<array-key, mixed> $arguments
     * @return Arg[]
     */
    public static function exportArguments(array $arguments): array
    {
        $args = [];

        foreach ($arguments as $name => $value) {
            $args[] = new Arg(self::export($value), name: is_string($name) ? new Identifier($name) : null);
        }

        return $args;
    }

    private static function exportInteger(int $value): Expr
    {
        if ($value === PHP_INT_MIN) {
            return new Expr\BinaryOp\Minus(new Expr\UnaryMinus(new Scalar\Int_(PHP_INT_MAX)), new Scalar\Int_(1));
        }

        return $value < 0 ? new Expr\UnaryMinus(new Scalar\Int_(-$value)) : new Scalar\Int_($value);
    }

    private static function exportArray(array $values): Expr\Array_
    {
        $items = [];
        $nextIndex = 0;

        foreach ($values as $key => $value) {
            $items[] = new ArrayItem(self::export($value), $key === $nextIndex ? null : self::export($key));

            if (is_int($key)) {
                $nextIndex = max($nextIndex, $key + 1);
            }
        }

        return new Expr\Array_($items, ['kind' => Expr\Array_::KIND_SHORT]);
    }
}
