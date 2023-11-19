<?php

declare(strict_types=1);

namespace Tempest\View;

use Tempest\AppConfig;

use function Tempest\path;
use function Tempest\view;

trait BaseView
{
    public string $path;
    public array $params = [];
    private array $rawParams = [];
    public ?string $extendsPath = null;
    public array $extendsParams = [];
    private AppConfig $appConfig;

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

    public function include(string $path): string
    {
        return view($path)->data(...$this->rawParams)->render($this->appConfig);
    }

    public function raw(string $name): ?string
    {
        return $this->rawParams[$name] ?? null;
    }

    public function render(AppConfig $appConfig): string
    {
        $this->appConfig = $appConfig;

        $path = path($appConfig->rootPath, $this->path);

        ob_start();
        include $path;
        $contents = ob_get_clean();

        if ($this->extendsPath) {
            $extendsData = ['slot' => $contents, ...$this->extendsParams];

            return view($this->extendsPath)
                ->data(...$extendsData)
                ->render($appConfig);
        }

        return $contents;
    }

    private function escape(array $items): array
    {
        foreach ($items as $key => $value) {
            if ($key === 'slot') {
                continue;
            }

            $items[$key] = htmlentities($value);
        }

        return $items;
    }
}
