<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Testing;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Console\Console;
use Tempest\Console\ExitCode;
use Tests\Tempest\Integration\Console\Fixtures\ComplexCommand;
use Tests\Tempest\Integration\Console\Fixtures\Hello;
use Tests\Tempest\Integration\Console\Fixtures\InteractiveCommand;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ConsoleTesterTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function call_with_invokable(): void
    {
        $this->console
            ->call(ComplexCommand::class)
            ->assertContains('COMPLEX'); // missing input
    }

    #[Test]
    public function call_with_closure(): void
    {
        $this->console
            ->call(function (Console $console): void {
                $console->writeln('hi');
            })
            ->assertContains('hi');
    }

    #[Test]
    public function call_with_callable(): void
    {
        $this->console
            ->call([InteractiveCommand::class, 'validation'])
            ->assertContains('a');
    }

    #[Test]
    public function call_with_command(): void
    {
        $this->console
            ->call('interactive:validation')
            ->assertContains('a');
    }

    #[Test]
    public function exit_code_success(): void
    {
        $this->console
            ->call(fn () => ExitCode::SUCCESS)
            ->assertSuccess();
    }

    #[Test]
    public function exit_code_invalid(): void
    {
        $this->console
            ->call(fn () => ExitCode::INVALID)
            ->assertInvalid();
    }

    #[Test]
    public function exit_code_error(): void
    {
        $this->console
            ->call(fn () => ExitCode::ERROR)
            ->assertError();
    }

    #[Test]
    public function exit_code_cancelled(): void
    {
        $this->console
            ->call(fn () => ExitCode::CANCELLED)
            ->assertCancelled();
    }

    #[Test]
    public function exit_code_success_default(): void
    {
        $this->console
            ->call(fn () => null)
            ->assertSuccess();
    }

    #[Test]
    public function exit_code_from_command(): void
    {
        $this->console
            ->call([Hello::class, 'test'])
            ->assertSuccess();
    }
}
