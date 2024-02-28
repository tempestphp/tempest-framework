<?php

declare(strict_types=1);

namespace Tempest\Container;

use Tempest\Container\Exceptions\CircularDependencyException;

final class ContainerLog
{
    public function __construct(
        /** @var \Tempest\Container\ContainerLogItem[] */
        private array $lines = [],
    ) {
    }

    public function add(ContainerLogItem $item): self
    {
        if (isset($this->lines[$item->id])) {
            throw new CircularDependencyException($item->id, $this);
        }

        $this->lines[$item->id] = $item;

        return $this;
    }

    public function __toString(): string
    {
        $message = '';
        $lines = array_reverse($this->lines);
        $i = 1;
        $count = count($lines);

        foreach($lines as $line) {
            $message .= match(true) {
                $i === $count => PHP_EOL . "\t\t└── {$line}",
                default => PHP_EOL . "\t\t├── {$line}",
            };

            $i += 1;
        }

        return $message;
    }
}
