<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Console\Components\Concerns\HasState;
use Tempest\Console\Console;
use Tempest\Console\InteractiveConsoleComponent;
use Tempest\Console\Terminal\Terminal;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class TerminalTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function supports_tty(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (Console $console): void {
                $terminal = new Terminal($console);

                $terminal->disableTty();

                $this->assertFalse($terminal->supportsTty);
            });
    }

    #[Test]
    public function interactive_render_keeps_cursor_within_terminal_when_body_is_taller_than_terminal(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (Console $console): void {
                $terminal = new Terminal($console);
                $terminal->disableTty();

                $component = new class implements InteractiveConsoleComponent {
                    use HasState;

                    public int $render = 0;

                    public function render(Terminal $terminal): string
                    {
                        $this->render += 1;

                        return implode(PHP_EOL, array_map(
                            fn (int $line): string => "render {$this->render} line {$line}",
                            range(1, 40),
                        ));
                    }

                    public function renderFooter(Terminal $terminal): ?string
                    {
                        return null;
                    }

                    public function setErrors(array $errors): self
                    {
                        return $this;
                    }
                };

                $this->assertGreaterThanOrEqual(0, $terminal->cursor->getPosition()->y);
            });
    }
}
