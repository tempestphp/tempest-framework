<?php

namespace Tempest\Container;

final class ContainerLog
{
    public function __construct(
        private array $lines = [],
    ) {
    }

    public function add(string $item): self
    {
        $this->lines[] = $item;

        return $this;
    }

    public function __toString(): string
    {
        $string = '';

        foreach ($this->lines as $i => $line) {
            $string .= PHP_EOL;

            $string .= str_repeat("\t", $i);

            if ($i > 0) {
                $string .= "^ ";
            }

            $string .= $line;
        }

        return $string;
    }
}
