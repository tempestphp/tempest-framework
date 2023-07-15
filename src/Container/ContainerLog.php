<?php

declare(strict_types=1);

namespace Tempest\Container;

use _PHPStan_690619d82\Nette\Neon\Exception;

final class ContainerLog
{
    public function __construct(
        private array $lines = [],
    ) {
    }

    public function add(string $item): self
    {
        if (in_array($item, $this->lines)) {
            throw new Exception("Circular dependency on {$item}!");
        }

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
