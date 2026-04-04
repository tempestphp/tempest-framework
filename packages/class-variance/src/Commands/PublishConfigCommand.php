<?php

declare(strict_types=1);

namespace Tempest\ClassVariance\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Support\Filesystem;

use function Tempest\root_path;
use function Tempest\src_path;

final class PublishConfigCommand
{
    use HasConsole;

    #[ConsoleCommand(
        name: 'class-variance:publish:config',
        description: 'Publishes a class variance config file to your application',
    )]
    public function __invoke(): void
    {
        $suggested = src_path('class-variance.config.php');
        $relative = ltrim(str_replace(root_path(), '', $suggested), '/\\');

        $destination = $this->console->ask(
            question: 'Where should the config file be created?',
            default: $relative,
        );

        $destination = str_starts_with($destination, '/')
            ? $destination
            : root_path($destination);

        if (Filesystem\is_file($destination)) {
            $overwrite = $this->console->confirm(
                question: "The file <em>{$relative}</em> already exists. Overwrite it?",
                default: false,
            );

            if (! $overwrite) {
                $this->console->info('Aborted.');

                return;
            }
        }

        Filesystem\ensure_directory_exists(dirname($destination));
        Filesystem\write_file($destination, $this->stub());

        $this->console->success("Config published to <em>{$relative}</em>.");
    }

    private function stub(): string
    {
        $stub = <<<'PHP'
        <?php

        declare(strict_types=1);

        use Tempest\ClassVariance\Classmaps\Classmap;
        use Tempest\ClassVariance\Config\TailwindClassVarianceConfig;

        /**
         * Class Variance configuration.
         *
         * This file is auto-discovered by Tempest. The object you return here is
         * bound in the container and picked up by tv() and the TvMergerInitializer.
         *
         * Available options:
         *
         *   $prefix    — Tailwind class prefix, e.g. 'tw-' (default: '')
         *   $separator — Variant separator, e.g. '_' (default: ':')
         *   $extend    — Additive class-group definitions merged on top of the defaults
         *   $override  — Class-group definitions that fully replace the matching defaults
         *
         * To add custom utilities you can use $extend:
         *
         *   $extend: new Classmap(
         *       classGroups: [
         *           'my-group' => ['my-class', ['my-prefix']],
         *       ],
         *       conflictingClassGroups: [
         *           'my-group' => ['another-group'],
         *       ],
         *   ),
         *
         * For cv() / GenericClassVarianceConfig, swap TailwindClassVarianceConfig
         * for GenericClassVarianceConfig and use its $separator and $classGroups options.
         */
        return new TailwindClassVarianceConfig(
            prefix: '',
            separator: ':',
            extend: null,
            override: null,
        );
        PHP;

        return str_replace('        ', '', $stub);
    }
}
