<?php

declare(strict_types=1);

namespace Tempest\Console\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Console\Components\Interactive\TextInputComponent;
use Tempest\Console\Console;
use Tempest\Console\Cursor;
use Tempest\Console\Point;
use Tempest\Console\Terminal\Terminal;

final class TerminalTest extends TestCase
{
    #[Test]
    public function cursor_is_hidden_while_redrawing_content(): void
    {
        $events = [];

        $console = $this->createStub(Console::class);
        $console
            ->method('write')
            ->willReturnCallback(function (string $_) use (&$events, $console): Console {
                $events[] = 'console:write';

                return $console;
            });

        $cursor = $this->createStub(Cursor::class);
        $cursor
            ->method('hide')
            ->willReturnCallback(function () use (&$events, $cursor): Cursor {
                $events[] = 'cursor:hide';

                return $cursor;
            });
        $cursor
            ->method('place')
            ->willReturnCallback(function (Point $_) use (&$events, $cursor): Cursor {
                $events[] = 'cursor:place';

                return $cursor;
            });
        $cursor
            ->method('clearAfter')
            ->willReturnCallback(function () use (&$events, $cursor): Cursor {
                $events[] = 'cursor:clearAfter';

                return $cursor;
            });
        $cursor
            ->method('show')
            ->willReturnCallback(function () use (&$events, $cursor): Cursor {
                $events[] = 'cursor:show';

                return $cursor;
            });

        $terminal = new Terminal($console);
        $terminal->disableTty();
        $terminal->cursor = $cursor;

        iterator_to_array($terminal->render(new TextInputComponent(label: 'Name')));

        $this->assertSame(
            [
                'cursor:hide',
                'cursor:place',
                'cursor:clearAfter',
                'console:write',
                'cursor:place',
                'cursor:show',
            ],
            $events,
        );
    }
}
