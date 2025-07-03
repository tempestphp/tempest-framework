<?php

namespace Tempest\Cryptography;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ExitCode;
use Tempest\Cryptography\Encryption\EncryptionConfig;
use Tempest\Cryptography\Encryption\EncryptionKey;
use Tempest\Support\Filesystem;
use Tempest\Support\Regex;
use Tempest\Support\Str;

use function Tempest\root_path;

final readonly class CreateSigningKeyCommand
{
    public function __construct(
        private EncryptionConfig $encryptionConfig,
        private Console $console,
    ) {}

    #[ConsoleCommand('key:generate', description: 'Generates the signing key required to sign and verify data.')]
    public function __invoke(): ExitCode
    {
        $key = EncryptionKey::generate($this->encryptionConfig->algorithm);

        $this->console->writeln();
        $this->console->success('Signing key generated successfully.');

        $this->createDotEnvIfNotExists();
        $this->addToDotEnv($key->toString());

        return ExitCode::SUCCESS;
    }

    private function getDotEnvPath(): string
    {
        return root_path('.env');
    }

    private function addToDotEnv(string $key): void
    {
        $file = Filesystem\read_file($this->getDotEnvPath());

        if (! Str\contains($file, 'SIGNING_KEY=')) {
            $file .= "\nSIGNING_KEY={$key}\n";
        } else {
            $file = Regex\replace($file, '/^SIGNING_KEY=.*$/m', "SIGNING_KEY={$key}");
        }

        Filesystem\write_file($this->getDotEnvPath(), $file);
    }

    private function createDotEnvIfNotExists(): void
    {
        if (Filesystem\exists($this->getDotEnvPath())) {
            return;
        }

        Filesystem\create_file($this->getDotEnvPath());
    }
}
