<?php

namespace Tempest\Framework\Commands;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Str\ImmutableString;
use Tempest\View\Slot;
use Tempest\View\ViewComponent;
use Tempest\View\ViewConfig;

use function Tempest\Support\arr;
use function Tempest\Support\Filesystem\is_file;
use function Tempest\Support\str;

final class MetaViewComponentCommand
{
    use HasConsole;

    public function __construct(
        private readonly ViewConfig $viewConfig,
    ) {}

    #[ConsoleCommand(name: 'meta:view-component', hidden: true)]
    public function __invoke(
        #[ConsoleArgument(description: "The view component's name or the path to a view component file")]
        ?string $viewComponent = null,
    ): void {
        if ($viewComponent) {
            $viewComponentName = $viewComponent;

            $viewComponent = $this->resolveViewComponent($viewComponentName);

            if ($viewComponent === null) {
                $this->error('Unknown view component `' . $viewComponentName . '`');
                return;
            }

            $data = $this->makeData($viewComponent);
        } else {
            $data = arr($this->viewConfig->viewComponents)
                ->map(fn (ViewComponent $viewComponent) => $this->makeData($viewComponent)->toArray());
        }

        $this->writeln($data->encodeJson(pretty: true));
    }

    private function makeData(ViewComponent $viewComponent): ImmutableArray
    {
        return arr([
            'file' => $viewComponent->file,
            'name' => $viewComponent->name,
            'slots' => $this->resolveSlots($viewComponent)->toArray(),
            'variables' => $this->resolveVariables($viewComponent)->toArray(),
        ]);
    }

    private function resolveViewComponent(string $viewComponent): ?ViewComponent
    {
        if (is_file($viewComponent)) {
            foreach ($this->viewConfig->viewComponents as $registeredViewComponent) {
                if ($registeredViewComponent->file !== $viewComponent) {
                    continue;
                }

                $viewComponent = $registeredViewComponent;

                break;
            }
        } else {
            $viewComponent = $this->viewConfig->viewComponents[$viewComponent] ?? null;
        }

        if ($viewComponent === null) {
            return null;
        }

        return $viewComponent;
    }

    private function resolveSlots(ViewComponent $viewComponent): ImmutableArray
    {
        preg_match_all('/<x-slot\s*(name="(?<name>[\w-]+)")?((\s*\/>)|>(?<default>(.|\n)*?)<\/x-slot>)/', $viewComponent->contents, $matches);

        return arr($matches['name'])
            ->mapWithKeys(fn (string $name) => yield $name => $name === '' ? Slot::DEFAULT : $name)
            ->values();
    }

    private function resolveVariables(ViewComponent $viewComponent): ImmutableArray
    {
        return str($viewComponent->contents)
            ->matchAll('/^\s*\*\s*@var.*$/m')
            ->map(fn (array $matches) => str($matches[0]))
            ->map(fn (ImmutableString $line) => $line->replaceRegex('/^\s*\*\s*@var\s*/', ''))
            ->map(fn (ImmutableString $line) => $line->trim())
            ->map(fn (ImmutableString $line) => $line->explode(limit: 3))
            ->mapWithKeys(
                fn (ImmutableArray $parts) => yield $parts[1] => [
                    'type' => $parts[0],
                    'name' => $parts[1],
                    'attributeName' => str($parts[1])->kebab()->ltrim('$'),
                    'description' => $parts[2] ?? null,
                ],
            )
            ->filter(fn (array $parts) => ! in_array($parts['name'], ['$this', '$attributes', '$slots'], strict: true))
            ->values();
    }
}
