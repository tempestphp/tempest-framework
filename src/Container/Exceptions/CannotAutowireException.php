<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;
use Tempest\Container\DependencyChain;

final class CannotAutowireException extends Exception
{
    public function __construct(DependencyChain $chain)
    {
        $stack = $chain->all();

        $firstDependency = $chain->first();
        $lastDependency = $chain->last();

        $message = PHP_EOL . PHP_EOL . "Cannot autowire {$firstDependency->getName()} because {$lastDependency->getName()} cannot be resolved" . PHP_EOL;

        $i = 0;

        foreach ($stack as $currentDependency) {
            $pipe = match ($i) {
                0 => '┌──',
                count($stack) - 1 => '└──',
                default => '├──',
            };

            $message .= PHP_EOL . "\t{$pipe} " . $currentDependency->getShortName();

            $i++;
        }

        $currentDependency = $lastDependency;
        $currentDependencyName = $currentDependency->getShortName();
        $firstPart = explode($currentDependencyName, $lastDependency->getShortName())[0] ?? null;
        $fillerSpaces = str_repeat(' ', strlen($firstPart) + 3);
        $fillerArrows = str_repeat('▒', strlen($currentDependencyName));
        $message .= PHP_EOL . "\t {$fillerSpaces}{$fillerArrows}";

        $message .= PHP_EOL . PHP_EOL;

        $message .= "Originally called in {$chain->getOrigin()}";
        $message .= PHP_EOL;

        parent::__construct($message);
    }
}
