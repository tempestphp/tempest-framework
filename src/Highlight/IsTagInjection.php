<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight;

use Tempest\Highlight\Highlighter;
use Tempest\Highlight\IsInjection;

trait IsTagInjection
{
    use IsInjection;

    abstract public function getTag(): string;

    abstract public function style(string $content): string;

    public function getPattern(): string
    {
        $tag = $this->getTag();

        return '(?<match>\<' . $tag . '\>(.|\n)*?\<\/' . $tag . '\>)';
    }

    public function parseContent(string $content, Highlighter $highlighter): string
    {
        $tag = $this->getTag();

        $content = str_replace(["<{$tag}>", "</{$tag}>"], '', $content);

        return $this->style($content);
    }
}
