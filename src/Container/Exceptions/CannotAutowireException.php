<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;
use Tempest\Container\ContainerLog;

final class CannotAutowireException extends Exception
{
    public function __construct(ContainerLog $containerLog)
    {
        $stack = $containerLog->getStack();

        $firstContext = $stack[array_key_first($stack)];
        $lastContext = $stack[array_key_last($stack)];

        $message = PHP_EOL . PHP_EOL . "Cannot autowire {$firstContext->getName()} because {$lastContext->getName()} cannot be resolved" . PHP_EOL;

        $i = 0;

        foreach ($stack as $currentContext) {
            $pipe = match ($i) {
                0 => '┌──',
                count($stack) - 1 => '└──',
                default => '├──',
            };

            $message .= PHP_EOL . "\t{$pipe} " . $currentContext;

            $i++;
        }

        $currentDependency = $lastContext->currentDependency();
        $currentDependencyName = (string) $currentDependency;
        $firstPart = explode($currentDependencyName, (string) $lastContext)[0] ?? null;
        $fillerSpaces = str_repeat(' ', strlen($firstPart) + 3);
        $fillerArrows = str_repeat('▒', strlen($currentDependencyName));
        $message .= PHP_EOL . "\t {$fillerSpaces}{$fillerArrows}";


        $message .= PHP_EOL . PHP_EOL;

        $message .= "Originally called in {$containerLog->getOrigin()}";
        $message .= PHP_EOL;

        parent::__construct($message);
    }
}
