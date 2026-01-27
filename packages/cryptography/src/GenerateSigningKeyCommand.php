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

if (class_exists(\Tempest\Console\ConsoleCommand::class)) {
    final readonly class GenerateSigningKeyCommand
    {
        public function __construct(
            private EncryptionConfig $encryptionConfig,
            private Console $console,
        ) {}

        #[ConsoleCommand(
            name: 'key:generate',
            description: 'Generates the signing key required to sign and verify data.',
            aliases: ['generate:key'],
        )]
        public function __invoke(bool $override = true): ExitCode
        {
            $key = EncryptionKey::generate($this->encryptionConfig->algorithm);

            $this->createDotEnvIfNotExists();
            $this->addToDotEnv($key->toString(), $override);

            $this->console->writeln();

            if ($override) {
                $this->console->success('Signing key generated successfully.');
            } else {
                $this->console->info('The signing key already exists.');
            }

            return ExitCode::SUCCESS;
        }

        private function getDotEnvPath(): string
        {
            return root_path('.env');
        }

        private function addToDotEnv(string $key, bool $override): void
        {
            $file = Filesystem\read_file($this->getDotEnvPath());

            if (! Str\contains($file, 'SIGNING_KEY=')) {
                $file = "SIGNING_KEY={$key}\n" . $file;
            } elseif ($override) {
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
}
