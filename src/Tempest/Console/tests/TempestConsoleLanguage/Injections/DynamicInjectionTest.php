<?php

declare(strict_types=1);

namespace Tempest\Console\Tests\TempestConsoleLanguage\Injections;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Console\Highlight\TempestConsoleLanguage\Injections\DynamicInjection;
use Tempest\Console\Highlight\TempestTerminalTheme;
use Tempest\Highlight\Highlighter;

/**
 * @internal
 */
final class DynamicInjectionTest extends TestCase
{
    #[TestWith(['<style="fg-cyan">foo</style>', "\e[96mfoo\e[0m"])]
    #[TestWith(['<style="bg-red">foo</style>', "\e[101mfoo\e[0m"])]
    #[TestWith(['<style="bold">foo</style>', "\e[1mfoo\e[0m"])]
    #[TestWith(['<style="underline">foo</style>', "\e[4mfoo\e[0m"])]
    #[TestWith(['<style="reset">foo</style>', "\e[0mfoo\e[0m"])]
    #[TestWith(['<style="reverse-text">foo</style>', "\e[7mfoo\e[0m"])]
    #[TestWith(['<style="bg-darkcyan fg-cyan underline">Tempest</style>', "\e[46m\e[96m\e[4mTempest\e[0m\e[0m\e[0m"])]
    #[TestWith(['<style="bg-dark-cyan fg-cyan underline">Tempest</style>', "\e[46m\e[96m\e[4mTempest\e[0m\e[0m\e[0m"])]
    #[TestWith(['<style="fg-cyan"><style="bg-dark-red">foo</style></style>', "\e[96m\e[41mfoo\e[0m\e[0m"])]
    #[Test]
    public function language(string $content, string $expected): void
    {
        $highlighter = new Highlighter(new TempestTerminalTheme());

        $this->assertSame(
            $expected,
            (new DynamicInjection())->parse($content, $highlighter)->content,
        );
    }
}
