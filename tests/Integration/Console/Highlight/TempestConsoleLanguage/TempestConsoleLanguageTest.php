<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Highlight\LogLanguage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Console\Highlight\TempestConsoleLanguage\TempestConsoleLanguage;
use Tempest\Console\Highlight\TempestTerminalTheme;
use Tempest\Highlight\Highlighter;

/**
 * @internal
 */
final class TempestConsoleLanguageTest extends TestCase
{
    #[TestWith(['<fg=cyan>foo</fg>', "\e[96mfoo\e[0m"])]
    #[TestWith(['<fg=darkcyan>foo</fg>', "\e[36mfoo\e[0m"])]
    #[TestWith(['<bg=red>foo</bg>', "\e[101mfoo\e[0m"])]
    #[TestWith(['<bg=darkred>foo</bg>', "\e[41mfoo\e[0m"])]
    #[TestWith(['<mod=bold>foo</mod>', "\e[1mfoo\e[0m"])]
    #[TestWith(['<mod=underline>foo</mod>', "\e[4mfoo\e[0m"])]
    #[TestWith(['<mod=reset>foo</mod>', "\e[0mfoo\e[0m"])]
    #[TestWith(['<mod=reversetext>foo</mod>', "\e[7mfoo\e[0m"])]
    #[TestWith(['<bg=darkcyan><fg=cyan><mod=underline>Tempest</mod></fg></bg>', "\e[46m\e[96m\e[4mTempest\e[0m\e[0m\e[0m"])]
    #[Test]
    public function language(string $content, string $expected): void
    {
        $highlighter = new Highlighter(new TempestTerminalTheme());

        $this->assertSame(
            $expected,
            $highlighter->parse($content, new TempestConsoleLanguage())
        );
    }
}
