<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Components;

use Exception;
use Tempest\Console\Components\Interactive\TaskComponent;
use Tempest\Console\Console;
use Tempest\Console\Terminal\Terminal;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class TaskComponentTest extends FrameworkIntegrationTestCase
{
    public function test_no_task(): void
    {
        $this->console->withoutPrompting()->call(function (Console $console): void {
            $terminal = new Terminal($console);
            $component = new TaskComponent('Task in progress');

            $frames = iterator_to_array($component->render($terminal));

            $this->assertStringContainsString('Task in progress', $frames[0]);
            $this->assertStringContainsString('DONE', $frames[1]);
        });
    }

    public function test_successful_task(): void
    {
        $this->console->withoutPrompting()->call(function (Console $console): void {
            $terminal = new Terminal($console);
            $component = new TaskComponent('Task in progress', function (): void {});

            $frames = iterator_to_array($component->render($terminal));

            $this->assertStringContainsString('Task in progress', $frames[0]);
            $this->assertStringContainsString('ms', $frames[1]); // execution time
            $this->assertStringContainsString('DONE', $frames[1]);
        });
    }

    public function test_failing_task(): void
    {
        $this->console->withoutPrompting()->call(function (Console $console): void {
            $terminal = new Terminal($console);
            $component = new TaskComponent('Task in progress', function (): never {
                throw new Exception('Failure');
            });

            $frames = iterator_to_array($component->render($terminal));

            $this->assertStringContainsString('Task in progress', $frames[0]);
            $this->assertStringContainsString('FAIL', $frames[1]);
        });
    }
}
