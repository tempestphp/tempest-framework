<?php

declare(strict_types=1);

namespace Tempest\View\Exceptions;

use Exception;
use function Tempest\Support\str;
use Throwable;

final class CompileError extends Exception
{
    public function __construct(string $content, Throwable $previous)
    {
        $excerpt = str($content)
            ->excerpt(
                $previous->getLine() - 5,
                $previous->getLine() + 5,
                asArray: true,
            )
            ->map(function (string $line, int $number) use ($previous) {
                return sprintf(
                    "%s%s | %s",
                    $number === $previous->getLine() ? '> ' : '  ',
                    $number,
                    $line
                );
            })
            ->implode(PHP_EOL);

        $message = sprintf(
            '%s
%s
%s 
%s

Could not compile %s',
            str_repeat('-', strlen($previous->getMessage())),
            $previous->getMessage(),
            str_repeat('-', strlen($previous->getMessage())),
            $excerpt,
            $content,
        );

        parent::__construct(
            message: $message,
            previous: $previous,
        );
    }
}
