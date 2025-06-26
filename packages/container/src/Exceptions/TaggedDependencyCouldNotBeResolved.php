<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;
use Tempest\Container\Dependency;
use Tempest\Container\DependencyChain;

final class TaggedDependencyCouldNotBeResolved extends Exception implements ContainerException
{
    public function __construct(DependencyChain $chain, Dependency $brokenDependency, string $tag)
    {
        $stack = $chain->all();
        $stack[] = $brokenDependency;

        $message = PHP_EOL . PHP_EOL . "Could not resolve tagged dependency {$brokenDependency->getName()}#{$tag}, did you forget to define an initializer for it?" . PHP_EOL;

        if (count($stack) < 2) {
            parent::__construct($message);

            return;
        }

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
