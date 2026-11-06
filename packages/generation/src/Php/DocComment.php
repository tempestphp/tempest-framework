<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use PhpParser\Comment\Doc;

/**
 * @internal
 */
final class DocComment
{
    public static function from(string $text): Doc
    {
        $lines = array_map(
            static fn (string $line) => rtrim(' * ' . $line),
            explode("\n", trim($text)),
        );

        return new Doc("/**\n" . implode("\n", $lines) . "\n */");
    }
}
