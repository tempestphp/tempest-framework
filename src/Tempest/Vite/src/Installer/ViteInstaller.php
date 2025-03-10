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
    ) {
    }

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
        $viteConfig = $this->publish(__DIR__ . "/{$templateDirectory}/vite.config.ts", destination: root_path('vite.config.ts'));
        $mainTs = $this->publish(__DIR__ . "/{$templateDirectory}/main.ts", destination: src_path('main.ts'));
        $mainCss = $shouldInstallTailwind
            ? $this->publish(__DIR__ . "/{$templateDirectory}/main.css", destination: src_path('main.css'))
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
        $this->update(root_path('.gitignore'), function (ImmutableString $gitignore) {
            if (! $gitignore->contains($this->viteConfig->build->bridgeFileName)) {
                $gitignore = $gitignore->append(PHP_EOL, $this->viteConfig->build->bridgeFileName);
            }

            if (! $gitignore->contains('node_modules')) {
                $gitignore = $gitignore->append(PHP_EOL, 'node_modules/');
            }

            if (! $gitignore->contains('public/build')) {
                return $gitignore->append(PHP_EOL, 'public/build/');
            }

            return $gitignore;
        });

        $packageManager = PackageManager::detect(root_path());

        $this->console->instructions([
            $shouldInstallTailwind
                ? '<strong>Vite and Tailwind CSS are now installed in your project</strong>!'
                : '<strong>Vite is now installed in your project</strong>!',
            PHP_EOL,
            $viteConfig
                ? sprintf('Configure <href="file://%s">vite.config.ts</href> as you see fit', $viteConfig)
                : null,
            $mainTs
                ? sprintf("Add <code><x-vite-tags :entrypoints='%s' /></code> to your template", json_encode(array_filter([$mainCss, $mainTs]), JSON_UNESCAPED_SLASHES))
                : 'Create a file and include it in your template with <code><x-vite-tags entrypoint="./path/to/file.ts" /></code>',
            "Run <code>{$packageManager->getBinaryName()} dev</code> to start the <strong>development server</strong>",
            PHP_EOL,
            '<style="fg-green">→</style> Read the <href="https://tempestphp.com/docs/vite">documentation</href>',
            '<style="fg-green">→</style> Join the <href="https://discord.tempestphp.com">Discord server</href>',
        ]);
    }
}
