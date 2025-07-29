<?php

namespace Tempest\View\Export;

use Tempest\Support\Arr\ImmutableArray;

final class ViewObjectExporter
{
    public static function export(ExportableViewObject|ImmutableArray $object): string
    {
        if ($object instanceof ImmutableArray) {
            return self::exportValue($object);
        }

        return sprintf(
            '\%s::restore(%s);',
            $object::class,
            $object
                ->exportData
                ->map(function (mixed $value, string $key) {
                    $value = self::exportValue($value);

                    return "{$key} : {$value}";
                })
                ->implode(','),
        );
    }

    public static function exportValue(mixed $value): string
    {
        return match (true) {
            $value instanceof ExportableViewObject => self::export($value),
            $value instanceof ImmutableArray => sprintf(
                'new \%s([%s])',
                ImmutableArray::class,
                $value->map(function (mixed $value, string|int $key) {
                    $key = is_int($key) ? $key : "'{$key}'";

                    return $key . ' => ' . rtrim(self::exportValue($value), ';');
                })->implode(', '),
            ),
            is_string($value) => "<<<'STRING'" . PHP_EOL . $value . PHP_EOL . 'STRING',
            default => var_export($value, true), // @mago-expect best-practices/no-debug-symbols
        };
    }
}
