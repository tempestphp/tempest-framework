<?php

declare(strict_types=1);

namespace Tempest\View;

use Tempest\Application\AppConfig;
use function Tempest\get;
use Tempest\Http\Session\Session;
use function Tempest\view;

trait IsView
{
    public string $path;
    public array $data = [];
    private array $rawData = [];
    public ?string $extendsPath = null;
    public array $extendsData = [];
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
        $this->data = $this->escape($params);
        $this->rawData = $params;
    }

    public function path(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getRawData(): array
    {
        return $this->rawData;
    }

    public function getRaw(string $key): mixed
    {
        return $this->rawData[$key] ?? null;
    }

    public function get(string $key): mixed
    {
        return $this->{$key} ?? $this->data[$key] ?? null;
    }

    public function getExtendsPath(): ?string
    {
        return $this->extendsPath;
    }

    public function data(...$params): self
    {
        $this->rawData = [...$this->rawData, ...$params];
        $this->data = [...$this->data, ...$this->escape($params)];

        return $this;
    }

    public function extends(string $path, ...$params): self
    {
        $this->extendsPath = $path;
        $this->extendsData = $params;

        return $this;
    }

    public function include(string $path, ...$params): string
    {
        return view($path)->data(...$this->rawData, ...$params)->render();
    }

    public function raw(string $name): ?string
    {
        return $this->rawData[$name] ?? null;
    }

    public function slot(string $name = 'slot'): ?string
    {
        return $this->rawData[$name] ?? null;
    }

    public function render(): string
    {
        return get(ViewRenderer::class)->render($this);
    }

    private function escape(array $items): array
    {
        foreach ($items as $key => $value) {
            $items[$key] = htmlentities($value);
        }

        return $items;
    }

    public function getExtendsData(): array
    {
        return $this->extendsData;
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

    /** @return \Tempest\Validation\Rule[][] */
    private function resolveValidationErrors(): ?array
    {
        $this->errors ??= $this->getSession()->get(Session::VALIDATION_ERRORS);

        return $this->errors;
    }

    private function resolveOriginalValues(): ?array
    {
        $this->originalValues ??= $this->getSession()->get(Session::ORIGINAL_VALUES);

        return $this->originalValues;
    }

    private function getSession(): Session
    {
        return get(Session::class);
    }
}
