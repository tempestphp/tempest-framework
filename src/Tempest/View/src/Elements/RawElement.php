<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;

final class RawElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly string $html,
    ) {
    }

    public function compile(): string
    {
        return $this->html;
    }
}
