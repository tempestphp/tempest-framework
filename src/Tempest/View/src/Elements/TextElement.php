<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;

final class TextElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly string $text,
    ) {
    }

    public function compile(): string
    {
        return preg_replace_callback(
            pattern: '/{{\s*(?<php>\$.*?)\s*}}/',
            callback: function (array $matches): string {
                return sprintf('<?= %s ?>', $matches['php']);
            },
            subject: $this->text,
        );
    }
}
