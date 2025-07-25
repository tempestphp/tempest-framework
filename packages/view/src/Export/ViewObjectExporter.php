<?php

namespace Tempest\View\Export;

use Tempest\Support\Arr\ImmutableArray;

final class ViewObjectExporter
{
    public static function export(ExportableViewObject|ImmutableArray $object): string
    {
        if ($object instanceof ImmutableArray) {
            return sprintf(
                'new \%s([%s])',
                ImmutableArray::class,
                $object->map(fn (mixed $value) => rtrim(self::export($value), ';'))->implode(', '),
            );
        }

        return sprintf(
            '\%s::restore(%s);',
            $object::class,
            $object
                ->exportData
                ->map(function (mixed $value, string $key) {
                    $value = match (true) {
                        $value instanceof ExportableViewObject => self::export($value),
                        is_string($value) => "<<<'STRING'" . PHP_EOL . $value . PHP_EOL . 'STRING',
                        default => var_export($value, true), // @mago-expect best-practices/no-debug-symbols
                    };

                    return "{$key} : {$value}";
                })
                ->implode(','),
        );
    }
}
