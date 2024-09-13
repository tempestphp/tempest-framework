<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Components;

use PHPUnit\Framework\TestCase;
use Tempest\Console\Components\Interactive\ProgressBarComponent;

/**
 * @internal
 * @small
 */
final class ProgressBarComponentTest extends TestCase
{
    public function test_progress_bar_component(): void
    {
        $component = new ProgressBarComponent(
            data: ['a', 'b', 'c', 'd'],
            handler: fn (string $input) => $input . $input,
        );

        $generator = $component->render();

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
    }

    public function test_progress_bar_with_format(): void
    {
        $component = new ProgressBarComponent(
            data: ['a', 'b', 'c', 'd'],
            handler: fn (string $input) => $input,
            format: fn (int $step, int $count) => str_repeat(':', $step),
        );

        $generator = $component->render();

        $generator->rewind();
        $this->assertSame(':', trim($generator->current()));
    }
}
