<?php

declare(strict_types=1);

namespace Tempest\Framework\Exceptions;

use Tempest\Container\Tag;
use Tempest\Framework\Application\ExceptionHandler;
use Tempest\Highlight\Highlighter;
use Throwable;

final class HttpExceptionHandler implements ExceptionHandler
{
    private ?Throwable $throwable = null;

    public function __construct(
        #[Tag('web')]
        private readonly Highlighter $highlighter,
    ) {
    }

    public function handle(Throwable $throwable): void
    {
        $this->throwable = $throwable;

        ob_start();

        include __DIR__ . '/exception.php';

        $contents = ob_get_clean();

        ob_start();

        if (! headers_sent()) {
            http_response_code(500);
        }

        echo $contents;

        ob_end_flush();
    }

    private function getCodeSample(): string
    {
        $code = $this->highlighter->parse(file_get_contents($this->throwable->getFile()), 'php');
        $lines = explode(PHP_EOL, $code);
        $excerptSize = 5;

        foreach ($lines as $i => $line) {
            $class = 'gutter';

            if ($i + 1 === $this->throwable->getLine()) {
                $class .= ' selected';
                $line = '<span class="error-line">' . $line . '</span>';
            }

            $lines[$i] = '<span class="' . $class . '">' . str_pad(
                string: '' . $i + 1,
                length: 3,
                pad_type: STR_PAD_LEFT,
            ) . '</span>' . $line;
        }

        $start = max(0, $this->throwable->getLine() - $excerptSize);
        $lines = array_slice($lines, $start, $excerptSize * 2);


        return implode(PHP_EOL, $lines);
    }
}
