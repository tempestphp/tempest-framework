<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight;

use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Themes\LightTerminalTheme;

trait IsComponent
{
    abstract private function getPath(): string;

    public function render(): string
    {
        extract($this->getData());

        ob_start();
        include $this->getPath();
        $contents = trim(ob_get_clean()) . PHP_EOL;

        $highlighter = new Highlighter(new LightTerminalTheme());

        return $highlighter->parse($contents, new ConsoleComponentLanguage());
    }

    private function getData(): array
    {
        return [];
    }
}
