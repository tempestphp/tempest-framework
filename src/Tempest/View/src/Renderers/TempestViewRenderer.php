<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

use Stringable;
use function Tempest\Support\arr;
use function Tempest\Support\str;
use Tempest\View\Exceptions\CompileError;
use Tempest\View\GenericView;
use Tempest\View\View;
use Tempest\View\ViewCache;
use Tempest\View\ViewRenderer;
use Throwable;

final class TempestViewRenderer implements ViewRenderer
{
    private ?View $currentView = null;

    public function __construct(
        private readonly TempestViewCompiler $compiler,
        private readonly ViewCache $viewCache,
    ) {
    }

    public function __get(string $name): mixed
    {
        return $this->currentView?->get($name);
    }

    public function __call(string $name, array $arguments)
    {
        return $this->currentView?->{$name}(...$arguments);
    }

    public function render(string|View $view): string
    {
        $view = is_string($view) ? new GenericView($view) : $view;

        $path = $view->getPath();

        $compiled = $this->viewCache->resolve(
            key: (string)crc32($path),
            cache: fn () => $this->cleanupCompiled($this->compiler->compile($path)),
        );

        return $this->renderCompiled($view, $compiled);
    }

    private function cleanupCompiled(string $compiled): string
    {
        // Remove strict type declarations
        $compiled = str($compiled)->replace('declare(strict_types=1);', '');

        // Cleanup and bundle imports
        $imports = arr();
        $compiled = $compiled
            ->replaceRegex('/use .*;/', function (array $matches) use (&$imports) {
                $imports[$matches[0]] = $matches[0];

                return '';
            })
            ->prepend(
                sprintf(
                    '<?php
%s
?>',
                    $imports->implode(PHP_EOL)
                ),
            );

        // Remove empty PHP blocks
        $compiled = $compiled->replaceRegex('/<\?php\s*\?>/', '');

        return $compiled->toString();
    }

    private function renderCompiled(View $_view, string $_content): string
    {
        $this->currentView = $_view;

        ob_start();

        // Extract data from view into local variables so that they can be accessed directly
        $_data = $_view->getData();

        extract($_data, flags: EXTR_SKIP);

        try {
            /** @phpstan-ignore-next-line */
            eval('?>' . $_content . '<?php');
        } catch (Throwable $throwable) {
            throw new CompileError(content: $_content, previous: $throwable);
        }

        // If the view defines local variables, we add them here to the view object as well
        foreach (get_defined_vars() as $key => $value) {
            if (! $_view->has($key)) {
                $_view->data(...[$key => $value]);
            }
        }

        $this->currentView = null;

        return trim(ob_get_clean());
    }

    public function escape(string|Stringable $value): string
    {
        return htmlentities((string) $value);
    }
}
