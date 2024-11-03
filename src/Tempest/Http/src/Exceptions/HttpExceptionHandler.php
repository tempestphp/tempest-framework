<?php

declare(strict_types=1);

namespace Tempest\Http\Exceptions;

use Tempest\Core\AppConfig;
use Tempest\Core\ExceptionHandler;
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Themes\CssTheme;
use Throwable;

final class HttpExceptionHandler implements ExceptionHandler
{
    private Throwable $throwable;

    private Highlighter $highlighter;

    public function __construct(private AppConfig $appConfig)
    {
        // TODO check for production or not
        $this->highlighter = new Highlighter(new CssTheme());

        //        // Production web
        //        if ($appConfig->environment->isProduction()) {
        //            set_exception_handler($this->renderExceptionPage(...));
        //            /** @phpstan-ignore-next-line */
        //            set_error_handler($this->renderErrorPage(...));
        //
        //            return;
        //        }
        //
        //        // Local web
        //        $whoops = new Run();
        //        $whoops->pushHandler(new PrettyPageHandler());
        //        $whoops->register();
    }

    public function handleException(Throwable $throwable): void
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

    public function getCodeSample(): string
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
                string: (string)($i + 1),
                length: 3,
                pad_type: STR_PAD_LEFT,
            ) . '</span>' . $line;
        }

        $start = max(0, $this->throwable->getLine() - $excerptSize);
        $lines = array_slice($lines, $start, $excerptSize * 2);


        return implode(PHP_EOL, $lines);
    }

    public function handleError(int $errNo, string $errstr, string $errFile, int $errLine): void
    {
        ll("{$errFile}:{$errLine} {$errstr} ({$errNo})");

        if (
            $errNo === E_USER_WARNING
            || $errNo === E_DEPRECATED
        ) {
            return;
        }

        ob_start();

        if (! headers_sent()) {
            http_response_code(500);
        }

        echo file_get_contents(__DIR__ . '/500.html');

        ob_end_flush();

        exit;
    }


    //ll($throwable);
    //
    //ob_start();
    //
    //if (! headers_sent()) {
    //http_response_code(500);
    //}
    //
    //echo file_get_contents(__DIR__ . '/500.html');
    //
    //ob_end_flush();
    //
    //exit;
}
