<?php

namespace Tempest\View;

use Tempest\AppConfig;

trait BaseView
{
    public string $path;

    public array $params = [];

    public ?string $extends = null;

    public static function new(
        string $templatePath,
        ...$params,
    ): self {
        /** @phpstan-ignore-next-line */
        $template = new self();

        $template->path = $templatePath;
        $template->params = $params;

        return $template;
    }

    public function __get(string $name): mixed
    {
        return $this->params[$name] ?? null;
    }

    public function data(...$params): self
    {
        $this->params = [...$this->params, ...$params];

        return $this;
    }

    public function render(AppConfig $appConfig): RenderedView
    {
        $path = path($appConfig->rootPath, $this->path);

        ob_start();
        include $path;
        $contents = ob_get_clean();

        if ($this->extends) {
            return GenericView::new(
                templatePath: $this->extends,
                slot: $contents,
            )->render($appConfig);
        }

        return new RenderedView($contents);
    }
}
