<?php

namespace Tests\Tempest\Console\Highlight\LogLanguage;

use PHPUnit\Framework\TestCase;
use Tempest\Console\Highlight\LogLanguage\LogLanguage;
use Tempest\Console\Highlight\TempestTerminalTheme;
use Tempest\Highlight\Highlighter;

class LogLanguageTest extends TestCase
{
    public function test_language(): void
    {
        $highlighter = new Highlighter(new TempestTerminalTheme());
        
        $parsed = $highlighter->parse('[2024-05-25T18:42:01.154192+00:00] tempest.DEBUG: logger [] []', new LogLanguage());

        $this->assertSame("\e[1m\e[104m[2024-05-25T18:42:01.154192+00:00]\e[0m \e[94mtempest.DEBUG\e[0m: logger [] []", $parsed);
    }
}
