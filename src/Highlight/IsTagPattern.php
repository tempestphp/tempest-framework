<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight;

use Tempest\Highlight\IsPattern;

trait IsTagPattern
{
    use IsPattern;

    abstract public function getTag(): string;

    public function getPattern(): string
    {
        $tag = $this->getTag();

        return '(?<match>\<' . $tag . '\>(.|\n)*?\<\/' . $tag . '\>)';
    }
}
