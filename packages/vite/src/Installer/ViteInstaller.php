<?php

declare(strict_types=1);

namespace Tempest\Vite\Installer;

use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use Tempest\Support\JavaScript\DependencyInstaller;
use Tempest\Support\JavaScript\PackageManager;
use Tempest\Support\Str\ImmutableString;
use Tempest\Vite\ViteConfig;

use function Tempest\root_path;
use function Tempest\src_path;

final class ViteInstaller implements Installer
{
    use PublishesFiles;

    private(set) string $name = 'vite';

    public function __construct(
        private readonly DependencyInstaller $javascript,
        private readonly ViteConfig $viteConfig,
        private readonly ConsoleArgumentBag $consoleArgumentBag,
    ) {}

    private function shouldInstallTailwind(): bool
    {
        $argument = $this->consoleArgumentBag->get('tailwind');

        if ($argument === null || ! is_bool($argument->value)) {
            return $this->console->confirm('Install Tailwind CSS as well?', default: true);
        }

        return (bool) $argument->value;
    }

    public function install(): void
    {
        $shouldInstallTailwind = $this->shouldInstallTailwind();
        $templateDirectory = $shouldInstallTailwind
            ? 'Tailwind'
            : 'Vanilla';

        // Installs the dependencies
        $this->javascript->installDependencies(
            cwd: root_path(),
            dependencies: [
                'vite',
                'vite-plugin-tempest',
                ...($shouldInstallTailwind ? ['tailwindcss', '@tailwindcss/vite'] : []),
            ],
            dev: true,
        );

        // Publishes the Vite config
        $this->publish(__DIR__ . "/{$templateDirectory}/vite.config.ts", destination: root_path('vite.config.ts'));
        $mainTs = $this->publish(__DIR__ . "/{$templateDirectory}/main.ts", destination: src_path('main.entrypoint.ts'));
        $mainCss = $shouldInstallTailwind
            ? $this->publish(__DIR__ . "/{$templateDirectory}/main.css", destination: src_path('main.entrypoint.css'))
            : null;

        // Install package.json scripts
        $this->updateJson(root_path('package.json'), function (array $json) {
            $json['type'] = 'module';
            $json['scripts'] ??= [];
            $json['scripts'] = [
                'dev' => 'vite',
                'build' => 'vite build',
                ...$json['scripts'],
            ];

            return $json;
        });

        // Updates the .gitignore
        $this->update(
            path: root_path('.gitignore'),
            callback: function (ImmutableString $gitignore) {
                if (! $gitignore->contains($this->viteConfig->bridgeFileName)) {
                    $gitignore = $gitignore->append(PHP_EOL, $this->viteConfig->bridgeFileName);
                }

                if (! $gitignore->contains('node_modules')) {
                    $gitignore = $gitignore->append(PHP_EOL, 'node_modules/');
                }

                if (! $gitignore->contains('public/build')) {
                    return $gitignore->append(PHP_EOL, 'public/build/');
                }

                return $gitignore;
            },
            ignoreNonExisting: true,
        );

        $packageManager = PackageManager::detect(root_path());

        $this->console->instructions([
            $shouldInstallTailwind
                ? '<strong>Vite and Tailwind CSS are now installed in your project</strong>!'
                : '<strong>Vite is now installed in your project</strong>!',
            PHP_EOL,
            "Run <code>{$packageManager->getRunCommand('dev')}</code> to start the <strong>development server</strong>",
            $mainTs || $mainCss
                ? 'Update <code>*.entrypoint.{ts,css}</code> files and observe changes in your browser'
                : 'Create a <code>src/main.entrypoint.ts</code> file to get started with front-end code',
            PHP_EOL,
            // '<style="fg-green">→</style> Read the <href="https://tempestphp.com/main/framework/vite">documentation</href>', // TODO: update when we have Vite docs
            '<style="fg-green">→</style> Join the <href="https://tempestphp.com/discord">Discord server</href>',
        ]);
    }
}
