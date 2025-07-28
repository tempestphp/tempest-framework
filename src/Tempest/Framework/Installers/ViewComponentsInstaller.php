<?php

namespace Tempest\Framework\Installers;

use Tempest\Console\HasConsole;
use Tempest\Core\Installer;
use Tempest\Core\IsComponentInstaller;
use Tempest\View\Components\ViewComponent;
use Tempest\View\ViewConfig;

use function Tempest\src_path;
use function Tempest\Support\arr;

final class ViewComponentsInstaller implements Installer
{
    private(set) string $name = 'view-components';

    use HasConsole;
    use IsComponentInstaller;

    public function __construct(
        private readonly ViewConfig $viewConfig,
    ) {}

    public function install(): void
    {
        $searchOptions = arr($this->viewConfig->viewComponents)
            ->filter(fn (mixed $input) => $input instanceof ViewComponent)
            ->filter(fn (ViewComponent $viewComponent) => $viewComponent->isVendorComponent);

        if ($searchOptions->isEmpty()) {
            $this->error('No installable view vendor components found.');
            return;
        }

        $selected = $this->ask(
            question: 'Select which view components you want to install',
            options: $searchOptions->keys(),
            multiple: true,
        );

        foreach ($selected as $selectedItem) {
            /** @var ViewComponent $viewComponent */
            $viewComponent = $searchOptions[$selectedItem];

            if (! is_file($viewComponent->file)) {
                $this->error("Could not publish `{$viewComponent->name}` because the source file `{$viewComponent->file}` could not be found.");

                continue;
            }

            $this->publish(
                $viewComponent->file,
                src_path("Views/{$selectedItem}.view.php"),
            );
        }
    }
}
