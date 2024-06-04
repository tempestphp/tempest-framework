<?php

declare(strict_types=1);

namespace Tempest\View;

use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use SimpleXMLElement;
use Tempest\AppConfig;
use function Tempest\get;
use function Tempest\path;
use function Tempest\type;
use function Tempest\view;

final class ViewRenderer
{
    private ?View $currentView = null;

    public function __construct(
        private readonly AppConfig $appConfig,
        private readonly ViewConfig $viewConfig,
    ) {}

    public function __get(string $name): mixed
    {
        return $this->currentView?->get($name);
    }

    public function __set(string $name, $value): void
    {
        if (! $this->currentView) {
            return;
        }

        $this->currentView->{$name} = $value;
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->currentView?->{$name}(...$arguments);
    }

    private function clone(): self
    {
        return clone $this;
    }

    public function render(View $view): string
    {
        $this->currentView = $view;

        $contents = $this->resolveContent($view);

        if ($extendsPath = $view->getExtendsPath()) {
            $slots = $this->renderSlots($contents);

            $extendsData = [...$slots, ...$view->getExtendsData()];

            return $this->clone()->render(
                view($extendsPath)->data(...$extendsData),
            );
        }

        return $this->renderViewComponents($contents);
    }

    private function resolveContent(View $view): string
    {
        $path = $view->getPath();

        if (! str_ends_with($path, '.php')) {
            return $path;
        }

        $discoveryLocations = $this->appConfig->discoveryLocations;

        while (! file_exists($path) && $location = current($discoveryLocations)) {
            $path = path($location->path, $view->getPath());
            next($discoveryLocations);
        }

        if (! file_exists($path)) {
            throw new Exception("View {$path} not found");
        }

        ob_start();

        include $path;

        return ob_get_clean();
    }

    private function renderSlots(string $content): array
    {
        $parts = array_map(
            fn (string $slot) => explode('<x-slot', $slot),
            explode('</x-slot>', $content),
        );

        $slots = [];

        foreach ($parts as $partsGroup) {
            foreach ($partsGroup as $part) {
                $part = trim($part);

                $slotName = $this->determineSlotName($part);

                if ($slotName !== 'slot') {
                    $part = trim(str_replace("name=\"{$slotName}\">", '', $part));
                }

                $slots[$slotName][] = $part;
            }
        }

        return array_map(
            fn (array $content) => implode(PHP_EOL, $content),
            $slots,
        );
    }

    private function determineSlotName(string $content): string
    {
        preg_match('/name=\"(\w+)\">/', $content, $matches);

        return $matches[1] ?? 'slot';
    }

    private function renderViewComponents(string $content): string
    {
        libxml_use_internal_errors(true);
        $xml = new SimpleXMLElement("<div>{$content}</div>");

        if ((string)$xml === $content) {
            return $content;
        }

        $parsed = [];

        foreach ($xml->children() as $element) {
            /** @var class-string<\Tempest\View\ViewComponent>|null $component */
            $componentClass = $this->viewConfig->viewComponents[$element->getName()]
                ?? $this->viewConfig->viewComponents[$element->getName()]
                ?? null;

            if (! $componentClass) {
                return $content;
            }

            $attributes = [
                'view' => $this->currentView,
            ];

            foreach ($element->attributes() as $attribute) {
                preg_match(
                    pattern: '/(?<injection>:)?(?<name>\w+)="(?<value>(.|\n)*?)"/',
                    subject: trim(html_entity_decode($attribute->asXML())),
                    matches: $attributeMatch,
                );

                $value = $attributeMatch['value'];
                $name = $attributeMatch['name'];

                if ($attributeMatch['injection'] === ':') {
                    /** @phpstan-ignore-next-line */
                    $value = eval("return {$value};");
                }

                $attributes[$name] = $value;
            }

            $reflection = new ReflectionClass($componentClass);

            $attributes = array_map(
                callback: function (ReflectionParameter $parameter) use ($attributes, $componentClass) {
                    if (array_key_exists($parameter->getName(), $attributes)) {
                        return $attributes[$parameter->getName()];
                    }

                    if ($parameter->isDefaultValueAvailable()) {
                        return $parameter->getDefaultValue();
                    }

                    try {
                        return get(type($parameter->getType()));
                    } catch (ReflectionException) {
                        throw new Exception("Could not resolve value for field {$componentClass}::\${$parameter->name}");
                    }
                },
                array: $reflection->getConstructor()->getParameters(),
            );

            /** @var ViewComponent $component */
            $component = $reflection->newInstance(...$attributes);

            $slot = preg_replace(
                pattern: [
                    "/^<{$element->getName()}(.|\n)*?(?<!-)>/",
                    "/<\/{$element->getName()}>$/",
                ],
                replacement: '',
                subject: html_entity_decode($element->asXML()),
            );

            $parsed[] = $component->render($this->renderViewComponents($slot));
        }

        libxml_clear_errors();
        libxml_use_internal_errors(false);

        return implode('', $parsed);
    }

    public function include(string $path, ...$params): string
    {
        return $this->render(view($path)->data(...$this->currentView->getRawData(), ...$params));
    }
}
