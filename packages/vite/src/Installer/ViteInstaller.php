<?php

declare(strict_types=1);

namespace Tempest\Vite\Installer;

use Tempest\Core\Installer;
use Tempest\Core\InstallerArgument;
use Tempest\Core\PublishesFiles;
use Tempest\Support\JavaScript\DependencyInstaller;
use Tempest\Support\JavaScript\PackageManager;
use Tempest\Support\Str\ImmutableString;
use Tempest\Vite\ViteConfig;

use function Tempest\root_path;
use function Tempest\src_path;

final class ViteInstaller
{
    use PublishesFiles;

    public function __construct(
        private readonly DependencyInstaller $javascript,
        private readonly ViteConfig $viteConfig,
    ) {}

    #[Installer('Vite', alias: 'vite')]
    public function install(
        #[InstallerArgument(prompt: 'Install Tailwind CSS as well?')]
        bool $tailwindcss = true,
    ): void {
        $templateDirectory = $tailwindcss
            ? 'tailwindcss'
            : 'vanilla';

        // Installs the dependencies
        $this->javascript->installDependencies(
            cwd: root_path(),
            dependencies: [
                'vite',
                'vite-plugin-tempest',
                ...($tailwindcss ? ['tailwindcss', '@tailwindcss/vite'] : []),
            ],
            dev: true,
        );

        // Publishes the Vite config
        $this->publish(__DIR__ . "/{$templateDirectory}/vite.config.ts", destination: root_path('vite.config.ts'));
        $mainTs = $this->publish(__DIR__ . "/{$templateDirectory}/main.ts", destination: src_path('main.entrypoint.ts'));
        $mainCss = $tailwindcss
            ? $this->publish(__DIR__ . "/{$templateDirectory}/main.css", destination: src_path('main.entrypoint.css'))
            : null;

        // Adds `package.json` if it does not exist
        if (! is_file(root_path('package.json'))) {
            $this->publish(__DIR__ . '/package.json', root_path('package.json'));
        }

        // Installs package.json scripts
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

        $packageManager = PackageManager::detect(root_path()) ?? PackageManager::NPM;

        $this->console->instructions([
            $tailwindcss
                ? '<strong>Vite and Tailwind CSS are now installed in your project</strong>!'
                : '<strong>Vite is now installed in your project</strong>!',
            PHP_EOL,
            'Add <code><x-vite-tags /></code> to the <code><head></code> of your template',
            "Run <code>{$packageManager->getRunCommand('dev')}</code> to start the <strong>development server</strong>",
            $mainTs || $mainCss
                ? 'Update <code>*.entrypoint.{ts,css}</code> files and observe changes in your browser'
                : 'Create a <code>src/main.entrypoint.ts</code> file to get started with front-end code',
            PHP_EOL,
            '<style="fg-green">→</style> Read the <href="https://tempestphp.com/docs/features/asset-bundling">documentation</href>',
            '<style="fg-green">→</style> Join the <href="https://tempestphp.com/discord">Discord server</href>',
        ]);
    }
}
