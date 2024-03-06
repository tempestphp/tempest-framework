<?php

namespace Tempest\Container\Exceptions;

use Exception;
use ReflectionClass;
use Tempest\Container\ContainerLog;

final class CannotInstantiateDependencyException extends Exception
{
    public function __construct(ReflectionClass $class, ContainerLog $containerLog)
    {
        $message = "Cannot resolve {$class->getName()} because it is not an instantiable class. Maybe it's missing an initializer class?" . PHP_EOL;

        $stack = $containerLog->getStack();

        $lastContext = $stack[array_key_last($stack)];

        $i = 0;

        foreach ($stack as $currentContext) {
            $pipe = match (true) {
                count($stack) > 1 && $i === 0 => '┌──',
                count($stack) > 1 && $i === count($stack) - 1 => '└──',
                count($stack) === 1 => '   ',
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