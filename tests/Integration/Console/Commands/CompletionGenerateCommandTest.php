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

    private ?string $helperBinary = null;

    private ?string $bundledHelperBinary = null;

    private bool $bundledHelperBinaryCreated = false;

    protected function tearDown(): void
    {
        if ($this->generatedPath !== null && Filesystem\is_file($this->generatedPath)) {
            Filesystem\delete_file($this->generatedPath);
            $this->generatedPath = null;
        }

        if ($this->helperBinary !== null && Filesystem\is_file($this->helperBinary)) {
            Filesystem\delete_file($this->helperBinary);
            $this->helperBinary = null;
        }

        if ($this->bundledHelperBinaryCreated && $this->bundledHelperBinary !== null && Filesystem\is_file($this->bundledHelperBinary)) {
            Filesystem\delete_file($this->bundledHelperBinary);
        }

        $this->bundledHelperBinary = null;
        $this->bundledHelperBinaryCreated = false;

        parent::tearDown();
    }

    #[Test]
    public function generate_writes_completion_metadata_to_default_path(): void
    {
        $this->generatedPath = CompletionRuntime::getMetadataPath();

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

    #[Test]
    public function generate_overwrites_runtime_helper_binary_when_hashes_do_not_match(): void
    {
        $this->generatedPath = CompletionRuntime::getMetadataPath();
        $this->helperBinary = CompletionRuntime::getHelperBinaryPath();
        $this->bundledHelperBinary = CompletionRuntime::getBundledHelperBinaryPath();

        if (! Filesystem\is_file($this->bundledHelperBinary)) {
            Filesystem\ensure_directory_exists(dirname($this->bundledHelperBinary));
            Filesystem\write_file($this->bundledHelperBinary, "#!/bin/sh\nexit 0\n");
            chmod($this->bundledHelperBinary, 0o755);
            $this->bundledHelperBinaryCreated = true;
        }

        Filesystem\ensure_directory_exists(dirname($this->helperBinary));
        Filesystem\write_file($this->helperBinary, "#!/bin/sh\necho stale\n");
        chmod($this->helperBinary, 0o755);

        $this->console
            ->call('completion:generate')
            ->assertSuccess();

        $this->assertSame(Filesystem\read_file($this->bundledHelperBinary), Filesystem\read_file($this->helperBinary));
    }
}
