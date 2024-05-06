<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;
use Tempest\Container\Dependency;
use Tempest\Container\DependencyChain;

final class CircularDependencyException extends Exception
{
    public function __construct(DependencyChain $chain, Dependency $circularDependency)
    {
        $firstDependency = $chain->first();

        $message = PHP_EOL . PHP_EOL . "Cannot autowire {$firstDependency->getName()} because it has a circular dependency on {$circularDependency->getName()}:" . PHP_EOL;

        $hasSeenCircularDependency = false;

        $stack = $chain->all();

        foreach ($stack as $currentDependency) {
            if ($hasSeenCircularDependency) {
                $prefix = '│  ';
            } elseif ($currentDependency->equals($circularDependency)) {
                $prefix = '┌─►';
                $hasSeenCircularDependency = true;
            } else {
                $prefix = '   ';
            }

            $message .= PHP_EOL . "\t{$prefix} " . $currentDependency->getShortName();
        }

        $circularDependencyName = $circularDependency->getShortName();
        $lastDependencyName = $chain->last()->getShortName();

        $firstPart = explode($circularDependencyName, $lastDependencyName)[0] ?? null;

        if ($lastDependencyName === $firstPart) {
            $fillerLines = str_repeat('─', 3);
        } else {
            $fillerLines = str_repeat('─', strlen($firstPart) + 3);
        }

        $fillerArrows = str_repeat('▒', strlen($circularDependencyName));
        $message .= PHP_EOL . "\t└{$fillerLines}{$fillerArrows}";
        $message .= PHP_EOL . PHP_EOL;

        $message .= "Originally called in {$chain->getOrigin()}";
        $message .= PHP_EOL;

        parent::__construct($message);
    }
}
