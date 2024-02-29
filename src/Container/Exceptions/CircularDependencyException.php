<?php

namespace Tempest\Container\Exceptions;

use Exception;
use Tempest\Container\ContainerLog;
use Tempest\Container\Context;

final class CircularDependencyException extends Exception
{
    public function __construct(ContainerLog $containerLog, Context $context)
    {
        $stack = $containerLog->getStack();
        $stack[] = $context;

        $firstContext = $stack[array_key_first($stack)];

        $message = PHP_EOL . PHP_EOL . "Cannot autowire {$firstContext->getId()} because it is a circular dependency:" . PHP_EOL;

        $i = 0;

        foreach ($stack as $currentContext) {
            $pipe = match ($i) {
                0 => '┌─►',
                count($stack) - 1 => '└─►',
                default => '│  ',
            };

            $message .= PHP_EOL . "\t{$pipe} " . $currentContext;

            $i++;
        }

        $message .= PHP_EOL;

        parent::__construct($message);
    }
}