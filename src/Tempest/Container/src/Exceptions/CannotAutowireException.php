<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;
use Tempest\Container\Dependency;
use Tempest\Container\DependencyChain;

final class CannotAutowireException extends Exception implements ContainerException
{
    public function __construct(DependencyChain $chain, Dependency $brokenDependency)
    {
        $stack = $chain->all();

        $firstDependency = $chain->first();

        $message = PHP_EOL . PHP_EOL . "Cannot autowire {$firstDependency->getName()} because {$brokenDependency->getName()} cannot be resolved" . PHP_EOL;

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

        $selectionLine = preg_replace_callback(
            pattern: '/(?<prefix>(.*))(?<selection>' . $brokenDependency->getTypeName() . '\s\$\w+)(.*)/',
            callback: function ($matches) {
                return str_repeat(' ', strlen($matches['prefix']) + 4) . str_repeat('▒', strlen($matches['selection']));
            },
            subject: $chain->last()->getShortName(),
        );

        $message .= PHP_EOL;
        $message .= "\t{$selectionLine}";
        $message .= PHP_EOL;
        $message .= "Originally called in {$chain->getOrigin()}";
        $message .= PHP_EOL;

        parent::__construct($message);
    }
}
