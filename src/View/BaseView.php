<?php

namespace Tempest\View;

use Tempest\AppConfig;

trait BaseView
{
    public string $path;
    public array $params = [];
    private array $rawParams = [];
    public ?string $extendsPath = null;
    public array $extendsParams = [];

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

    public function raw(string $name): ?string
    {
        return $this->rawParams[$name] ?? null;
    }

    public function render(AppConfig $appConfig): string
    {
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
        return array_map(
            fn ($item) => htmlentities($item),
            $items,
        );
    }
}
