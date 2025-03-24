<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;

final class CommentElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly string $content,
    ) {}

    public function compile(): string
    {
        return sprintf('<!--%s-->', $this->content);
    }
}
