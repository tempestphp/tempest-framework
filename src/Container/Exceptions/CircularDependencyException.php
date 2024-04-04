<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;
use Tempest\Container\ContainerLog;
use Tempest\Container\Context;

final class CircularDependencyException extends Exception
{
    public function __construct(ContainerLog $containerLog, Context $circularDependencyContext)
    {
        $stack = $containerLog->getStack();
        $firstContext = $stack[array_key_first($stack)];
        $lastContext = $stack[array_key_last($stack)];

        $message = PHP_EOL . PHP_EOL . "Cannot autowire {$firstContext->getName()} because it has a circular dependency on {$circularDependencyContext->getName()}:" . PHP_EOL;

        $hasSeenDependency = false;

        foreach ($stack as $context) {
            if ($context->getName() === $circularDependencyContext->getName()) {
                $prefix = '┌─►';
                $hasSeenDependency = true;
            } elseif ($hasSeenDependency) {
                $prefix = '│  ';
            } else {
                $prefix = '   ';
            }

            $message .= PHP_EOL . "\t{$prefix} " . $context;
        }

        $circularDependencyName = $circularDependencyContext->getShortName();
        $firstPart = explode($circularDependencyName, (string) $lastContext)[0] ?? null;
        $fillerLines = str_repeat('─', strlen($firstPart) + 3);
        $fillerArrows = str_repeat('▒', strlen($circularDependencyName));

        $message .= PHP_EOL . "\t└{$fillerLines}{$fillerArrows}";
        $message .= PHP_EOL . PHP_EOL;

        $message .= "Originally called in {$containerLog->getOrigin()}";
        $message .= PHP_EOL;

        parent::__construct($message);
    }
}
