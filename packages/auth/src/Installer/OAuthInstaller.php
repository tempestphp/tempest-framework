<?php

declare(strict_types=1);

namespace Tempest\Auth\Installer;

use Tempest\Auth\OAuth\SupportedOAuthProvider;
use Tempest\Core\PublishesFiles;
use Tempest\Process\ProcessExecutor;
use Tempest\Support\Filesystem\Exceptions\PathWasNotFound;
use Tempest\Support\Filesystem\Exceptions\PathWasNotReadable;
use Tempest\Support\Str\ImmutableString;

use function Tempest\root_path;
use function Tempest\src_path;
use function Tempest\Support\arr;
use function Tempest\Support\Filesystem\read_file;
use function Tempest\Support\Namespace\to_fqcn;
use function Tempest\Support\str;

final class OAuthInstaller
{
    use PublishesFiles;

    public function __construct(
        private readonly ProcessExecutor $processExecutor,
    ) {}

    public function install(): void
    {
        $providers = $this->getProviders();

        if (count($providers) === 0) {
            return;
        }

        $this->publishStubs(...$providers);

        if ($this->confirm('Would you like to add the OAuth config variables to your .env file?', default: true)) {
            $this->updateEnvFile(...$providers);
        }

        if ($this->confirm('Install composer dependencies?', default: true)) {
            $this->installComposerDependencies(...$providers);
        }

        $this->console->instructions([
            sprintf('<strong>The selected OAuth %s installed in your project</strong>', count($providers) > 1 ? 'providers are' : 'provider is'),
            '',
            'Next steps:',
            '1. Update the .env file with your OAuth credentials',
            '2. Implement the OAuth controller callback method',
            '3. Review and customize the published files if needed',
            '',
            '<strong>Published files</strong>',
            ...arr($this->publishedFiles)->map(fn (string $file) => '<style="fg-green">→</style> ' . $file),
        ]);
    }

    /**
     * @return list<SupportedOAuthProvider>
     */
    private function getProviders(): array
    {
        return $this->ask(
            question: 'Please choose an OAuth provider',
            options: SupportedOAuthProvider::cases(),
            multiple: true,
        );
    }

    private function publishStubs(SupportedOAuthProvider ...$providers): void
    {
        foreach ($providers as $provider) {
            $this->publishController($provider);

            $this->publishConfig($provider);

            $this->publishImports();
        }
    }

    private function publishConfig(SupportedOAuthProvider $provider): void
    {
        $name = strtolower($provider->name);
        $source = __DIR__ . "/../Installer/oauth/{$name}.config.stub.php";

        $this->publish(
            source: $source,
            destination: src_path("Authentication/OAuth/{$name}.config.php"),
        );
    }

    private function publishController(SupportedOAuthProvider $provider): void
    {
        $fileName = str($provider->getName())
            ->replace('Provider', '')
            ->append('Controller.php')
            ->toString();

        $this->publish(
            source: __DIR__ . '/oauth/OAuthControllerStub.php',
            destination: src_path("Authentication/OAuth/{$fileName}"),
            callback: function (string $source, string $destination) use ($provider) {
                $providerFqcn = $provider::class;
                $name = strtolower($provider->getName());
                $userModelFqcn = to_fqcn(src_path('Authentication/User.php'), root: root_path());

                $this->update(
                    path: $destination,
                    callback: fn (ImmutableString $contents) => $contents->replace(
                        search: [
                            "'tag_name'",
                            'redirect-route',
                            'callback-route',
                            "'user-model-fqcn'",
                            'provider_db_column',
                        ],
                        replace: [
                            "\\{$providerFqcn}::{$provider->name}",
                            "/auth/{$name}",
                            "/auth/{$name}/callback",
                            "\\{$userModelFqcn}::class",
                            "{$name}_id",
                        ],
                    ),
                );
            },
        );
    }

    private function installComposerDependencies(SupportedOAuthProvider ...$providers): void
    {
        $packages = arr($providers)
            ->map(fn (SupportedOAuthProvider $provider) => $provider->composerPackage())
            ->filter();

        if ($packages->isNotEmpty()) {
            $this->processExecutor->run("composer require {$packages->implode(' ')}");
        }
    }

    private function updateEnvFile(SupportedOAuthProvider ...$providers): void
    {
        arr($providers)
            ->map(fn (SupportedOAuthProvider $provider) => $this->extractSettings($provider))
            ->filter()
            ->flatten()
            ->each(function (string $setting) {
                foreach (['.env', '.env.example'] as $envFile) {
                    $this->update(
                        path: root_path($envFile),
                        callback: static fn (ImmutableString $contents): ImmutableString => $contents->contains($setting)
                            ? $contents
                            : $contents->append(PHP_EOL, "{$setting}="),
                        ignoreNonExisting: true,
                    );
                }
            });
    }

    private function extractSettings(SupportedOAuthProvider $provider): array
    {
        $name = strtolower($provider->name);
        $configPath = __DIR__ . "/../Installer/oauth/{$name}.config.stub.php";

        try {
            return str(read_file($configPath))
                ->matchAll("/env\('(OAUTH_[^']*)'/", matches: 1)
                ->map(fn (array $matches) => $matches[1] ?? null)
                ->filter()
                ->toArray();
        } catch (PathWasNotFound|PathWasNotReadable) {
            return [];
        }
    }
}
