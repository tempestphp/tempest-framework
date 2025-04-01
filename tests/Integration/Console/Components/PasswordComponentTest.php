<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Components;

use Tempest\Console\Components\Interactive\PasswordComponent;
use Tempest\Console\Console;
use Tempest\Console\Terminal\Terminal;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class PasswordComponentTest extends FrameworkIntegrationTestCase
{
    public function test_password_component(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (Console $console): void {
                $terminal = new Terminal($console);
                $component = new PasswordComponent('Enter password');

                $this->assertStringContainsString('Enter password', $component->render($terminal));

                $component->input('a');
                $component->input('b');
                $component->input('c');

                $this->assertStringContainsString('***', $component->render($terminal));

                $component->deletePreviousCharacter();

                $this->assertSame('ab', $component->enter());
            });
    }
}
