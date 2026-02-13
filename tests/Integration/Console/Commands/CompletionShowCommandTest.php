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
    protected function setUp(): void
    {
        parent::setUp();

        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Shell completion is not supported on Windows.');
        }
    }

    #[Test]
    public function show_zsh_completion_script(): void
    {
        $this->console
            ->call('completion:show --shell=zsh')
            ->assertSee('tempest-complete')
            ->assertSee('commands.json')
            ->assertSee('_tempest_php_original_compdef')
            ->assertSee('_compskip=all')
            ->assertSuccess();
    }

    #[Test]
    public function show_bash_completion_script(): void
    {
        $this->console
            ->call('completion:show --shell=bash')
            ->assertSee('tempest-complete')
            ->assertSee('commands.json')
            ->assertSee('if [[ "$current_word" == *[=:]* ]] && [[ "$current_segment" != "$current_word" ]]; then')
            ->assertSee('if [[ -z "$current_segment" || "$current_segment" == "=" ]]; then')
            ->assertSee('__tempest_php_original_completion')
            ->assertSee('complete -o bashdefault -o default -F _tempest tempest')
            ->assertSee('compopt +o default +o bashdefault')
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
