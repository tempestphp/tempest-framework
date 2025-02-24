<?php

declare(strict_types=1);

namespace Tempest\Vite\Installer;

use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;
use Tempest\Support\JavaScript\DependencyInstaller;
use Tempest\Support\JavaScript\PackageManager;
use Tempest\Support\StringHelper;
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
    ) {
    }

    public function install(): void
    {
        // Installs the dependencies
        $this->javascript->installDependencies(
            cwd: root_path(),
            dependencies: [
                'vite',
                'vite-plugin-tempest',
            ],
            dev: true,
        );

        // Publishes the Vite config
        $viteConfig = $this->publish(__DIR__ . '/Vanilla/vite.config.ts', destination: root_path('vite.config.ts'));
        $main = $this->publish(__DIR__ . '/Vanilla/main.ts', destination: src_path('main.ts'));

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
        $this->update(root_path('.gitignore'), function (StringHelper $gitignore) {
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
            '<strong>Vite is now installed in your project</strong>!',
            PHP_EOL,
            $main
                ? "Add <code>\\Tempest\\vite_tags('{$main}')</code> to your template"
                : 'Create a file and include it in your template with <code>\\Tempest\\vite_tags()</code>',
            $viteConfig
                ? sprintf('Configure <href="file://%s">vite.config.ts</href> as you see fit', $viteConfig)
                : null,
            "Run <code>{$packageManager->getBinaryName()} dev</code> to start the <strong>development server</strong>",
            PHP_EOL,
            '<style="fg-green">→</style> Read the <href="https://tempestphp.com/docs/vite">documentation</href>',
            '<style="fg-green">→</style> Join the <href="https://discord.tempestphp.com">Discord server</href>',
        ]);
    }
}
