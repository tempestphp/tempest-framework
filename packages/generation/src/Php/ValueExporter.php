<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Expr\Array_;
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
            $value instanceof UnitEnum => new ClassConstFetch(new FullyQualified($value::class), $value->name),
            $value === null => new ConstFetch(new Name('null')),
            is_bool($value) => new ConstFetch(new Name($value ? 'true' : 'false')),
            is_int($value) => self::exportInteger($value),
            is_float($value) => new Float_($value),
            is_string($value) => new String_($value),
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
            return new Minus(new UnaryMinus(new Int_(PHP_INT_MAX)), new Int_(1));
        }

        return $value < 0 ? new UnaryMinus(new Int_(-$value)) : new Int_($value);
    }

    private static function exportArray(array $values): Array_
    {
        $items = [];
        $nextIndex = 0;

        foreach ($values as $key => $value) {
            $items[] = new ArrayItem(self::export($value), $key === $nextIndex ? null : self::export($key));

            if (is_int($key)) {
                $nextIndex = max($nextIndex, $key + 1);
            }
        }

        return new Array_($items, ['kind' => Array_::KIND_SHORT]);
    }
}
