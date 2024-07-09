<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;

final class PreElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly string $html,
    ) {
    }

    public function getHtml(): string
    {
        return $this->html;
    }
}
