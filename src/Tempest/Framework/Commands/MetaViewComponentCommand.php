<?php

namespace Tempest\Framework\Commands;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\View\Slot;
use Tempest\View\ViewComponent;
use Tempest\View\ViewConfig;

use function Tempest\Support\arr;
use function Tempest\Support\Filesystem\is_file;
use function Tempest\Support\str;

final readonly class MetaViewComponentCommand
{
    use HasConsole;

    public function __construct(
        private ViewConfig $viewConfig,
    ) {}

    #[ConsoleCommand(name: 'meta:view-component', hidden: true)]
    public function __invoke(
        #[ConsoleArgument(description: "The view component's name or the path to a view component file")]
        ?string $viewComponent = null,
    ): void {
        if ($viewComponent) {
            $viewComponentName = $viewComponent;

            $viewComponent = $this->resolveViewComponent($viewComponentName);

            if (! $viewComponent instanceof ViewComponent) {
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
            return array_find(
                array: $this->viewConfig->viewComponents,
                callback: fn ($registeredViewComponent) => $registeredViewComponent->file === $viewComponent,
            );
        }

        return $this->viewConfig->viewComponents[$viewComponent] ?? null;
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
            ->matchAll(
                pattern: '/(?:^\s*\*|\/\*\*)[ \t]*@var[ \t]+(?<declaration>[^\r\n]*?)[ \t]*(?:\*\/(?:\s*(?<assignee>\$\w+)\s*=(?!=))?|$)/m',
                matches: ['declaration', 'assignee'],
            )
            ->map(fn (array $matches) => [
                'parts' => str($matches['declaration'])->explode(limit: 3),
                'assignee' => $matches['assignee'] ?? null,
            ])
            // A one-line `@var` right before an assignment types a local variable, not an attribute.
            ->filter(fn (array $match) => $match['assignee'] === null || $match['assignee'] !== ($match['parts'][1] ?? null))
            ->map(fn (array $match) => $match['parts'])
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
