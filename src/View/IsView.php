<?php

declare(strict_types=1);

namespace Tempest\View;

use Exception;
use Tempest\AppConfig;
use function Tempest\get;
use Tempest\Http\Session\Session;
use function Tempest\path;
use function Tempest\view;

trait IsView
{
    public string $path;
    public array $params = [];
    private array $rawParams = [];
    public ?string $extendsPath = null;
    public array $extendsParams = [];
    private AppConfig $appConfig;
    /** @var array<mixed>|null */
    private ?array $errors = null;
    /** @var array<mixed>|null */
    private ?array $originalValues = null;

    public function __construct(
        string $path,
        array $params = [],
    ) {
        $this->path = $path;
        $this->params = $this->escape($params);
        $this->rawParams = $params;
    }

    public function __get(string $name): mixed
    {
        return $this->params[$name] ?? null;
    }

    public function path(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function data(...$params): self
    {
        $this->rawParams = [...$this->rawParams, ...$params];
        $this->params = [...$this->params, ...$this->escape($params)];

        return $this;
    }

    public function extends(string $path, ...$params): self
    {
        $this->extendsPath = $path;
        $this->extendsParams = $params;

        return $this;
    }

    public function include(string $path, ...$params): string
    {
        return view($path)->data(...$this->rawParams, ...$params)->render();
    }

    public function raw(string $name): ?string
    {
        return $this->rawParams[$name] ?? null;
    }

    public function slot(string $name = 'slot'): ?string
    {
        return $this->rawParams[$name] ?? null;
    }

    public function render(): string
    {
        $contents = $this->resolveContent();

        if ($this->extendsPath) {
            $slots = $this->parseSlots($contents);

            $extendsData = [...$slots, ...$this->extendsParams];

            return view($this->extendsPath)
                ->data(...$extendsData)
                ->render();
        }

        return $this->parseViewComponents($contents);
    }

    private function escape(array $items): array
    {
        foreach ($items as $key => $value) {
            $items[$key] = htmlentities($value);
        }

        return $items;
    }

    private function parseSlots(string $content): array
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

    private function parseViewComponents(string $contents): string
    {
        $viewConfig = get(ViewConfig::class);

        preg_match_all(
            pattern: '/<x-(?<tag>\w+)(?<attributes>(.|\n)*?(?<!-))>(?<body>(.|\n)*?)<\/x/',
            subject: $contents,
            matches: $matches,
            flags: PREG_OFFSET_CAPTURE,
        );

        $additionalOffset = 0;

        foreach ($matches[0] as $key => $match) {
            $tag = $matches['tag'][$key][0];

            $componentClass = $viewConfig->viewComponents[$tag] ?? null;

            if (! $componentClass) {
                continue;
            }

            preg_match_all(
                pattern: '/(?<injection>:)?(?<attribute>\w+)="(?<value>(.|\n)*?)"/',
                subject: trim($matches['attributes'][$key][0]),
                matches: $attributeMatches,
            );

            $attributes = [];

            foreach ($attributeMatches[0] as $attributeKey => $attributeMatch) {
                $value = $attributeMatches['value'][$attributeKey];
                $attribute = $attributeMatches['attribute'][$attributeKey];

                if ($attributeMatches['injection'][$attributeKey] === ':') {
                    /** @phpstan-ignore-next-line */
                    $value = eval("return {$value};");
                }

                $attributes[$attribute] = $value;
            }

            /** @var ViewComponent $component */
            $component = new $componentClass(...$attributes);

            $body = $matches['body'][$key][0];

            $rendered = $component->render($body);

            $fullMatch = $match[0];
            $offset = $match[1];

            $contents = substr_replace(
                string: $contents,
                replace: $rendered,
                offset: $offset + $additionalOffset,
            );

            $additionalOffset += strlen($rendered) - strlen($fullMatch);
        }

        return $contents;
    }

    private function determineSlotName(string $content): string
    {
        preg_match('/name=\"(\w+)\">/', $content, $matches);

        return $matches[1] ?? 'slot';
    }

    /**
     * @param string $name
     * @return \Tempest\Validation\Rule[]
     */
    public function getErrorsFor(string $name): array
    {
        $errors = $this->resolveValidationErrors();

        return $errors[$name] ?? [];
    }

    public function hasErrorsFor(string $name): bool
    {
        $errors = $this->resolveValidationErrors();

        return array_key_exists($name, $errors);
    }

    public function hasErrors(): bool
    {
        $errors = $this->resolveValidationErrors();

        return $errors !== null;
    }

    public function original(string $name, mixed $default = ''): mixed
    {
        $originalValues = $this->resolveOriginalValues();

        return $originalValues[$name] ?? $default;
    }

    /** @return array<mixed> */
    private function resolveValidationErrors(): ?array
    {
        $this->errors ??= $this->getSession()->get(Session::VALIDATION_ERRORS);

        return $this->errors;
    }

    /** @return array<mixed> */
    private function resolveOriginalValues(): ?array
    {
        $this->originalValues ??= $this->getSession()->get(Session::ORIGINAL_VALUES);

        return $this->originalValues;
    }

    private function getSession(): Session
    {
        return get(Session::class);
    }

    public function resolveContent(): string
    {
        $appConfig = get(AppConfig::class);

        if (! str_ends_with($this->path, '.php')) {
            return $this->path;
        }

        $path = $this->path;
        $discoveryLocations = $appConfig->discoveryLocations;

        while (! file_exists($path) && $location = current($discoveryLocations)) {
            $path = path($location->path, $this->path);
            next($discoveryLocations);
        }

        if (! file_exists($path)) {
            throw new Exception("View {$path} not found");
        }

        ob_start();

        include $path;

        return ob_get_clean();
    }
}
