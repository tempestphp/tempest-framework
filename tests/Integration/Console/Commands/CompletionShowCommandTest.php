<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CompletionShowCommandTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function show_zsh_completion_script(): void
    {
        $this->console
            ->call('completion:show --shell=zsh')
            ->assertSee('_tempest')
            ->assertSuccess();
    }

    #[Test]
    public function show_bash_completion_script(): void
    {
        $this->console
            ->call('completion:show --shell=bash')
            ->assertSee('_tempest')
            ->assertSuccess();
    }

    #[Test]
    public function show_with_invalid_shell(): void
    {
        $this->console
            ->withoutPrompting()
            ->call('completion:show --shell=fish')
            ->assertSee('Invalid argument `fish` for `shell` argument')
            ->assertError();
    }
}
