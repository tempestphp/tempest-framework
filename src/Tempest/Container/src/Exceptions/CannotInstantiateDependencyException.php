<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;
use Tempest\Container\DependencyChain;
use Tempest\Support\Reflection\ClassReflector;

final class CannotInstantiateDependencyException extends Exception
{
    public function __construct(ClassReflector $class, DependencyChain $chain)
    {
        $message = "Cannot resolve {$class->getName()} because it is not an instantiable class. Maybe it's missing an initializer class?" . PHP_EOL;

        $stack = $chain->all();

        if ($stack === []) {
            parent::__construct($message);

            return;
        }

        $i = 0;

        foreach ($stack as $currentDependency) {
            $pipe = match (true) {
                count($stack) > 1 && $i === 0 => '┌──',
                count($stack) > 1 && $i === count($stack) - 1 => '└──',
                count($stack) === 1 => '   ',
                default => '├──',
            };

            $message .= PHP_EOL . "\t{$pipe} " . $currentDependency->getShortName();

            $i++;
        }

        $lastDependency = $chain->last();
        //        $currentDependencyName = $lastDependency->getShortName();
        //        $firstPart = explode($currentDependencyName, (string)$lastDependency)[0] ?? null;
        //        $fillerSpaces = str_repeat(' ', strlen($firstPart) + 3);
        //        $fillerArrows = str_repeat('▒', strlen($currentDependencyName));
        //        $message .= PHP_EOL . "\t {$fillerSpaces}{$fillerArrows}";
        //
        //        $message .= PHP_EOL . PHP_EOL;

        $message .= "Originally called in {$chain->getOrigin()}";
        $message .= PHP_EOL;

        parent::__construct($message);
    }
}
