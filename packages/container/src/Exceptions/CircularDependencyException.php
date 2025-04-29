<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;
use Tempest\Container\Dependency;
use Tempest\Container\DependencyChain;

final class CircularDependencyException extends Exception implements ContainerException
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

        $selectionLine = preg_replace_callback(
            pattern: '/(?<prefix>(.*))(?<selection>' . $circularDependency->getTypeName() . '\s\$\w+)(.*)/',
            callback: function ($matches) {
                return '└' . str_repeat('─', strlen($matches['prefix']) + 3) . str_repeat('▒', strlen($matches['selection']));
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
