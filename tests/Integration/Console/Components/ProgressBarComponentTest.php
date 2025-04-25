<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Components;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tempest\Console\Components\Interactive\ProgressBarComponent;
use Tempest\Console\Console;
use Tempest\Console\Terminal\Terminal;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class ProgressBarComponentTest extends FrameworkIntegrationTestCase
{
    public function test_progress_bar_component(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (Console $console): void {
                $terminal = new Terminal($console);
                $component = new ProgressBarComponent(
                    data: ['a', 'b', 'c', 'd'],
                    handler: fn (string $input) => $input . $input,
                );

                $generator = $component->render($terminal);

                $generator->rewind();
                $this->assertSame('[========>                      ] (1/4)', trim($generator->current()));

                $generator->next();
                $this->assertSame('[===============>               ] (2/4)', trim($generator->current()));

                $generator->next();
                $this->assertSame('[=======================>       ] (3/4)', trim($generator->current()));

                $generator->next();
                $this->assertSame('[==============================] (4/4)', trim($generator->current()));

                $generator->next();
                $this->assertSame(
                    ['aa', 'bb', 'cc', 'dd'],
                    $generator->getReturn(),
                );
            });
    }

    public function test_progress_bar_with_format(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (Console $console): void {
                $terminal = new Terminal($console);
                $component = new ProgressBarComponent(
                    data: ['a', 'b', 'c', 'd'],
                    handler: fn (string $input) => $input,
                    format: fn (int $step) => str_repeat(':', $step),
                );

                $generator = $component->render($terminal);

                $generator->rewind();
                $this->assertSame(':', trim($generator->current()));
            });
    }
}
