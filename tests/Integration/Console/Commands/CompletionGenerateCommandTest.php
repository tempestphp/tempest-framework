<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Console\CompletionRuntime;
use Tempest\Support\Filesystem;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class CompletionGenerateCommandTest extends FrameworkIntegrationTestCase
{
    private ?string $generatedPath = null;

    private CompletionRuntime $completionRuntime;

    protected function setUp(): void
    {
        parent::setUp();

        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Shell completion is not supported on Windows.');
        }

        $this->completionRuntime = new CompletionRuntime();
    }

    protected function tearDown(): void
    {
        if ($this->generatedPath !== null && Filesystem\is_file($this->generatedPath)) {
            Filesystem\delete_file($this->generatedPath);
            $this->generatedPath = null;
        }

        parent::tearDown();
    }

    #[Test]
    public function generate_writes_completion_metadata_to_default_path(): void
    {
        $this->generatedPath = $this->completionRuntime->getMetadataPath();

        if (Filesystem\is_file($this->generatedPath)) {
            Filesystem\delete_file($this->generatedPath);
        }

        $this->console
            ->call('completion:generate')
            ->assertSee('Wrote completion metadata to:')
            ->assertSee('commands.json')
            ->assertSuccess();

        $this->assertTrue(Filesystem\is_file($this->generatedPath));

        $metadata = Filesystem\read_json($this->generatedPath);
        $flags = array_column($metadata['commands']['completion:test']['flags'], 'flag');
        $installFlags = array_column($metadata['commands']['completion:install']['flags'], null, 'name');

        $this->assertSame(['--flag', '--items=', '--value='], $flags);
        $this->assertSame('Install shell completion for Tempest', $metadata['commands']['completion:install']['description']);
        $this->assertSame(['-s'], $installFlags['shell']['aliases']);
        $this->assertSame('The shell to install completions for (zsh, bash)', $installFlags['shell']['description']);
        $this->assertSame(['bash', 'zsh'], $installFlags['shell']['value_options']);
    }

    #[Test]
    public function generate_writes_completion_metadata_to_custom_path(): void
    {
        $this->generatedPath = $this->internalStorage . '/completion/custom-commands.json';

        $this->console
            ->call("completion:generate --path={$this->generatedPath}")
            ->assertSee('Wrote completion metadata to:')
            ->assertSee('custom-commands.json')
            ->assertSuccess();

        $this->assertTrue(Filesystem\is_file($this->generatedPath));
    }
}
